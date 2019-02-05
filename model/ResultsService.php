<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2013-2017 Open Assessment Technologies S.A.
 *
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package taoOutcomeUi
 */

namespace oat\taoOutcomeUi\model;

use common_Utils;
use League\Flysystem\FileNotFoundException;
use oat\generis\model\GenerisRdf;
use oat\generis\model\OntologyRdfs;
use oat\oatbox\service\ServiceManager;
use oat\oatbox\user\User;
use oat\tao\helpers\metadata\ResourceCompiledMetadataHelper;
use oat\tao\model\metadata\compiler\ResourceJsonMetadataCompiler;
use oat\tao\model\metadata\compiler\ResourceMetadataCompilerInterface;
use oat\taoDelivery\model\AssignmentService;
use oat\taoDelivery\model\execution\ServiceProxy;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use oat\taoItems\model\ItemCompilerIndex;
use oat\taoOutcomeUi\model\table\ContextTypePropertyColumn;
use oat\taoOutcomeUi\model\table\GradeColumn;
use oat\taoOutcomeUi\model\table\ResponseColumn;
use \common_Exception;
use \common_Logger;
use \common_exception_Error;
use \core_kernel_classes_Class;
use \core_kernel_classes_DbWrapper;
use \core_kernel_classes_Resource;
use oat\taoOutcomeUi\model\table\VariableColumn;
use oat\taoOutcomeUi\model\Wrapper\ResultServiceWrapper;
use oat\taoResultServer\models\classes\NoResultStorage;
use oat\taoResultServer\models\classes\NoResultStorageException;
use oat\taoResultServer\models\classes\ResultManagement;
use oat\taoResultServer\models\classes\ResultService;
use \tao_models_classes_ClassService;
use oat\taoOutcomeUi\helper\Datatypes;
use oat\taoDelivery\model\execution\DeliveryExecution;
use oat\taoResultServer\models\classes\ResultServerService;
use tao_models_classes_service_StorageDirectory;
use taoQtiTest_models_classes_QtiTestService;
use oat\taoQtiTest\models\QtiTestCompilerIndex;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class ResultsService extends tao_models_classes_ClassService implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    const VARIABLES_FILTER_LAST_SUBMITTED = 'lastSubmitted';
    const VARIABLES_FILTER_FIRST_SUBMITTED = 'firstSubmitted';
    const VARIABLES_FILTER_ALL = 'all';

    const PERSISTENCE_CACHE_KEY = 'resultCache';

    /**
     *
     * @var \taoResultServer_models_classes_ReadableResultStorage
     */
    private $implementation = null;

    /**
     * Internal cache for item info.
     *
     * @var array
     */
    private $itemInfoCache = [];

    /**
     * External cache.
     *
     * @var \common_persistence_KvDriver
     */
    private $resultCache;

    /** @var array  */
    private $indexerCache = [];
    /** @var array  */
    private $executionCache = [];

    /** @var array */
    private $testMetadataCache = [];

    /**
     * @return \common_persistence_KvDriver|null
     */
    public function getCache()
    {
        if (is_null($this->resultCache)) {
            /** @var \common_persistence_Manager $persistenceManager */
            $persistenceManager = $this->getServiceLocator()->get(\common_persistence_Manager::SERVICE_ID);
            if ($persistenceManager->hasPersistence(self::PERSISTENCE_CACHE_KEY)) {
                $this->resultCache = $persistenceManager->getPersistenceById(self::PERSISTENCE_CACHE_KEY);
            }
        }

        return $this->resultCache;
    }

    public function getCacheKey($resultIdentifier, $suffix = '')
    {
        return 'resultPageCache:'. $resultIdentifier .':'. $suffix;
    }

    protected function getContainerCacheKey($resultIdentifier)
    {
        return $this->getCacheKey($resultIdentifier, 'keys');
    }

    public function setCacheValue($resultIdentifier, $fullKey, $value)
    {
        if (is_null($this->getCache())) {
            return false;
        }

        $fullKeys = [];

        $containerKey = $this->getContainerCacheKey($resultIdentifier);
        if ($this->getCache()->exists($containerKey)) {
            $fullKeys = $this->getContainerCacheValue($containerKey);
        }

        $fullKeys[] = $fullKey;

        if ($this->getCache()->set($fullKey, $value)) {
            // let's save the container of the keys as well
            return $this->setContainerCacheValue($containerKey, $fullKeys);
        }

        return false;
    }

    public function deleteCacheFor($resultIdentifier)
    {
        if (is_null($this->getCache())) {
            return false;
        }

        $containerKey = $this->getContainerCacheKey($resultIdentifier);
        if (!$this->getCache()->exists($containerKey)) {
            return false;
        }

        $fullKeys = $this->getContainerCacheValue($containerKey);
        $initialCount = count($fullKeys);

        foreach ($fullKeys as $i => $key) {
            if ($this->getCache()->del($key)) {
                unset($fullKeys[$i]);
            }
        }

        if (empty($fullKeys)) {
            // delete the whole container
            return $this->getCache()->del($containerKey);
        } else if (count($fullKeys) < $initialCount) {
            // update the container
            return $this->setContainerCacheValue($containerKey, $fullKeys);
        }

        // no cache has been deleted
        return false;
    }

    protected function setContainerCacheValue($containerKey, array $fullKeys)
    {
        return $this->getCache()->set($containerKey, gzencode(json_encode(array_unique($fullKeys)), 9));
    }

    protected function getContainerCacheValue($containerKey)
    {
        return json_decode(gzdecode($this->getCache()->get($containerKey)), true);
    }

    /**
     * (non-PHPdoc)
     * @see tao_models_classes_ClassService::getRootClass()
     */
    public function getRootClass() {
        return new core_kernel_classes_Class(ResultService::DELIVERY_RESULT_CLASS_URI);
    }

    public function setImplementation(ResultManagement $implementation){
        $this->implementation = $implementation;
    }

    /**
     * @return ResultManagement
     * @throws common_exception_Error
     */
    public function getImplementation(){
        if($this->implementation == null){
            throw new \common_exception_Error('No result storage defined');
        }
        return $this->implementation;
    }

    /**
     * return all variable for that deliveryResults (uri identifiers)
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  string $resultIdentifier
     * @param boolean $flat a flat array is returned or a structured delvieryResult-ItemResult-Variable
     * @return array
     */
    public function getVariables($resultIdentifier, $flat = true) {
        $variables = array();
        //this service is slow due to the way the data model design
        //if the delvieryResult related execution is finished, the data is stored in cache.

        $serial = 'deliveryResultVariables:'.$resultIdentifier;
        //if (common_cache_FileCache::singleton()->has($serial)) {
        //    $variables = common_cache_FileCache::singleton()->get($serial);
        //} else {

        $itemResultVariablesArray = array_values($this->getItemResultsFromDeliveryResult($resultIdentifier));
        $testResultVariablesArray = array_values($this->getTestsFromDeliveryResult($resultIdentifier));

        $finalResultVariables = array_merge($itemResultVariablesArray, $testResultVariablesArray);

        $resultVariables = $this->getVariablesFromObjectResult($finalResultVariables);
        foreach ($resultVariables as $resultVariable) {
            $currentItem = current($resultVariable);
            $key = isset($currentItem->callIdItem) ? $currentItem->callIdItem : $currentItem->callIdTest;
            $variables[$key][] = $resultVariable;
        }

        // impossible to determine state DeliveryExecution::STATE_FINISHIED
        //    if (false) {
        //        common_cache_FileCache::singleton()->put($variables, $serial);
        //    }
        //}
        if ($flat) {
            $returnValue = array();
            foreach ($variables as $key => $itemResultVariables) {
                $newKeys = array();
                $oldKeys = array_keys($itemResultVariables);
                foreach($oldKeys as $oldKey){
                    $newKeys[] = $key.'_'.$oldKey;
                }
                $itemResultVariables = array_combine($newKeys, array_values($itemResultVariables));
                $returnValue = array_merge($returnValue, $itemResultVariables);
            }
        } else {
            $returnValue = $variables;
        }


        return (array) $returnValue;
    }

    /**
     * @param string|array $itemResult
     * @param array        $wantedTypes
     * @return array
     * @throws common_exception_Error
     */
    public function getVariablesFromObjectResult($itemResult, $wantedTypes = [\taoResultServer_models_classes_ResponseVariable::class, \taoResultServer_models_classes_OutcomeVariable::class, \taoResultServer_models_classes_TraceVariable::class])
    {
        $returnedVariables = [];
        $variables = $this->getImplementation()->getVariables($itemResult);

        foreach ($variables as $itemVariables) {
            foreach ($itemVariables as $variable) {
                if (in_array(get_class($variable->variable), $wantedTypes)) {
                    $returnedVariables[] = [$variable];
                }
            }
        }

        unset($variables);

        return $returnedVariables;
    }

    /**
     * Return the corresponding delivery
     * @param string $resultIdentifier
     * @return core_kernel_classes_Resource delviery
     * @author Patrick Plichart, <patrick@taotesting.com>
     */
    public function getDelivery($resultIdentifier) {
        return new core_kernel_classes_Resource($this->getImplementation()->getDelivery($resultIdentifier));
    }

    /**
     * Ges the type of items contained by the delivery
     * @param string $resultIdentifier
     * @return string
     */
    public function getDeliveryItemType($resultIdentifier)
    {
        $resultsViewerService = $this->getServiceLocator()->get(ResultsViewerService::SERVICE_ID);
        return $resultsViewerService->getDeliveryItemType($resultIdentifier);
    }

    /**
     * Returns all label of itemResults related to the delvieryResults
     * @param string $resultIdentifier
     * @return array string uri
     * */
    public function getItemResultsFromDeliveryResult($resultIdentifier) {
        return $this->getImplementation()->getRelatedItemCallIds($resultIdentifier);
    }

    /**
     * Returns all label of itemResults related to the delvieryResults
     * @param string $resultIdentifier
     * @return array string uri
     * */
    public function getTestsFromDeliveryResult($resultIdentifier) {
        return $this->getImplementation()->getRelatedTestCallIds($resultIdentifier);
    }

    /**
     *
     * @param string $itemCallId
     * @param array $itemVariables already retrieved variables
     * @return array|null
     * @throws \common_exception_NotFound
     * @throws common_exception_Error
     */
    public function getItemFromItemResult($itemCallId, $itemVariables = array())
    {
        $item = null;

        if(empty($itemVariables)){
            $itemVariables = $this->getImplementation()->getVariables($itemCallId);
        }

        //get the first variable (item are the same in all)
        $tmpItems = array_shift($itemVariables);

        //get the first object
        $itemUri = $tmpItems[0]->item;

        $delivery = $this->getDeliveryByResultId($tmpItems[0]->deliveryResultIdentifier);

        $itemIndexer = $this->getItemIndexer($delivery);

        if(!is_null($itemUri)){
            $langItem = $itemIndexer->getItem($itemUri, $this->getResultLanguage() );
            $item = array_merge(is_array($langItem) ? $langItem : [],['uriResource'=>$itemUri]);
        }
        return $item;
    }

    /**
     *
     * @param string $test
     * @return \core_kernel_classes_Resource
     */
    public function getVariableFromTest($test) {
        $returnTest = null;
        $tests = $this->getImplementation()->getVariables($test);

        //get the first variable (item are the same in all)
        $tmpTests = array_shift($tests);

        //get the first object
        if(!is_null($tmpTests[0]->test)){
            $returnTest = new core_kernel_classes_Resource($tmpTests[0]->test);
        }
        return $returnTest;
    }

    /**
     *
     * @param string $variableUri
     * @return string
     *
     */
    public function getVariableCandidateResponse($variableUri) {
        return $this->getImplementation()->getVariableProperty($variableUri, 'candidateResponse');
    }

    /**
     *
     * @param string $variableUri
     * @return string
     */
    public function getVariableBaseType($variableUri) {
        return $this->getImplementation()->getVariableProperty($variableUri, 'baseType');
    }


    /**
     *
     * @param array $variablesData
     * @return array ["nbResponses" => x,"nbCorrectResponses" => y,"nbIncorrectResponses" => z,"nbUnscoredResponses" => a,"data" => $variableData]
     */
    public function calculateResponseStatistics($variablesData) {
        $numberOfResponseVariables = 0;
        $numberOfCorrectResponseVariables = 0;
        $numberOfInCorrectResponseVariables = 0;
        $numberOfUnscoredResponseVariables = 0;
        foreach ($variablesData as $epoch => $itemVariables) {
            foreach($itemVariables as $key => $value){
                if($key == \taoResultServer_models_classes_ResponseVariable::class){
                    foreach($value as $variable){
                        $numberOfResponseVariables++;
                        switch($variable['isCorrect']){
                            case 'correct':
                                $numberOfCorrectResponseVariables++;
                                break;
                            case 'incorrect':
                                $numberOfInCorrectResponseVariables++;
                                break;
                            case 'unscored':
                                $numberOfUnscoredResponseVariables++;
                                break;
                            default:
                                common_Logger::w('The value '.$variable['isCorrect'].' is not a valid value');
                                break;
                        }
                    }
                }
            }
        }
        $stats = array(
            "nbResponses" => $numberOfResponseVariables,
            "nbCorrectResponses" => $numberOfCorrectResponseVariables,
            "nbIncorrectResponses" => $numberOfInCorrectResponseVariables,
            "nbUnscoredResponses" => $numberOfUnscoredResponseVariables,
        );
        return $stats;
    }

    /**
     * @param $itemCallId
     * @param $itemVariables
     * @return array item information ['uri' => xxx, 'label' => yyy]
     */
    private function getItemInfos($itemCallId, $itemVariables)
    {
        $undefinedStr = __('unknown'); //some data may have not been submitted

        try {
            common_Logger::d("Retrieving related Item for item call " . $itemCallId . "");
            $relatedItem = $this->getItemFromItemResult($itemCallId, $itemVariables);
        } catch (common_Exception $e) {
            common_Logger::w("The item call '" . $itemCallId . "' is not linked to a valid item. (deleted item ?)");
            $relatedItem = null;
        }

        $itemIdentifier = $undefinedStr;
        $itemLabel = $undefinedStr;

        if ($relatedItem) {
            $itemIdentifier = $relatedItem['uriResource'];

            // check item info in internal cache
            if (isset($this->itemInfoCache[$itemIdentifier])) {
                common_Logger::t("Item info found in internal cache for item " . $itemIdentifier . "");
                return $this->itemInfoCache[$itemIdentifier];
            }
            $itemLabel = $relatedItem['label'];
        }

        $item['itemModel'] = '---';
        $item['label'] = $itemLabel;
        $item['uri'] = $itemIdentifier;

        // storing item info in memory to not hit the db for the same item again and again
        // when method "getStructuredVariables" are called multiple times in the same request
        if ($relatedItem) {
            $this->itemInfoCache[$itemIdentifier] = $item;
        }

        return $item;
    }


    /**
     *  prepare a data set as an associative array, service intended to populate gui controller
     *
     * @param string $resultIdentifier
     * @param string $filter 'lastSubmitted', 'firstSubmitted', 'all'
     * @param array $wantedTypes ['taoResultServer_models_classes_ResponseVariable', 'taoResultServer_models_classes_OutcomeVariable', 'taoResultServer_models_classes_TraceVariable']
     * @return array
        [
            'epoch1' => [
                'label' => Example_0_Introduction,
                'uri' => http://tao.local/mytao.rdf#i1462952280695832,
                'internalIdentifier' => item-1,
                'taoResultServer_models_classes_Variable class name' => [
                    'Variable identifier 1' => [
                        'uri' => 1,
                        'var' => taoResultServer_models_classes_Variable object,
                        'isCorrect' => correct
                    ],
                    'Variable identifier 2' => [
                        'uri' => 2,
                        'var' => taoResultServer_models_classes_Variable object,
                        'isCorrect' => unscored
                    ]
                ]
            ]
        ]
     *
     * @throws common_exception_Error
     */
    public function getStructuredVariables($resultIdentifier, $filter, $wantedTypes = array())
    {
        $itemCallIds = $this->getItemResultsFromDeliveryResult($resultIdentifier);

        // splitting call ids into chunks to perform bulk queries
        $itemCallIdChunks = array_chunk($itemCallIds, 50);

        $itemVariables = [];
        foreach ($itemCallIdChunks as $ids) {
            $itemVariables = array_merge($itemVariables, $this->getVariablesFromObjectResult($ids, $wantedTypes));
        }

        unset($itemCallIds, $itemCallIdChunks);

        usort($itemVariables, function($a, $b){
            $variableA = $a[0]->variable;
            $variableB = $b[0]->variable;
            list($usec, $sec) = explode(" ", $variableA->getEpoch());
            $floata = ((float) $usec + (float) $sec);
            list($usec, $sec) = explode(" ", $variableB->getEpoch());
            $floatb = ((float) $usec + (float) $sec);

            if ((floatval($floata) - floatval($floatb)) > 0) {
                return 1;
            } elseif ((floatval($floata) - floatval($floatb)) < 0) {
                return -1;
            } else {
                return 0;
            }
        });

        $savedItems = $variablesByItem = [];
        $firstEpoch = null;

        foreach ($itemVariables as $variables) {

            $itemVariable = $variables[0];

            /** @var \taoResultServer_models_classes_Variable $variable */
            $variable = $itemVariable->variable;
            $itemCallId = $itemVariable->callIdItem;

            if ($variable->getIdentifier() == 'numAttempts') {
                $firstEpoch = $variable->getEpoch();
                if ($filter != self::VARIABLES_FILTER_ALL) {
                    if (array_key_exists($itemCallId, $savedItems)) {
                        if ($filter == self::VARIABLES_FILTER_FIRST_SUBMITTED) {
                            continue;
                        }
                        if ($filter == self::VARIABLES_FILTER_LAST_SUBMITTED) {
                            unset($variablesByItem[$savedItems[$itemCallId]]);
                            unset($savedItems[$itemCallId]);
                        }
                    }
                }
                $variablesByItem[$firstEpoch] = $this->getItemInfos($itemCallId, [[$itemVariable]]);
                $variablesByItem[$firstEpoch]['attempt'] = $variable->getValue();
                $savedItems[$itemCallId] = $firstEpoch;
            }

            if (!isset($variablesByItem[$firstEpoch])) {
                continue;
            }

            $variableDescription = [
                'uri' => $itemVariable->uri,
                'var' => $variable,
            ];

            if ($variable instanceof \taoResultServer_models_classes_ResponseVariable && !is_null($variable->getCorrectResponse())) {
                $variableDescription['isCorrect'] = $variable->getCorrectResponse() >= 1 ? 'correct' : 'incorrect';
            } else {
                $variableDescription['isCorrect'] = 'unscored';
            }

            $variablesByItem[$firstEpoch]['internalIdentifier'] = explode('.', str_replace($resultIdentifier, '', $itemCallId), 3)[1];
            $variablesByItem[$firstEpoch][get_class($variable)][$variable->getIdentifier()] = $variableDescription;
        }

        return $variablesByItem;
    }

    /**
     * Filters the complex array structure for variable classes
     * @param array $structure as defined by getStructuredVariables()
     * @param array $filter classes to keep
     * @return array as defined by getStructuredVariables()
     */
    public function filterStructuredVariables(array $structure, array $filter)
    {
        $all = [
            \taoResultServer_models_classes_ResponseVariable::class,
            \taoResultServer_models_classes_OutcomeVariable::class,
            \taoResultServer_models_classes_TraceVariable::class
        ];
        $toRemove = array_diff($all, $filter);
        $filtered = $structure;
        foreach ($filtered as $timestamp => $entry) {
            foreach ($entry as $key => $value) {
                if (in_array($key, $toRemove)) {
                    unset($filtered[$timestamp][$key]);
                }
            }
        }
        return $filtered;
    }

    /**
     *
     * @param $resultIdentifier
     * @param string $filter 'lastSubmitted', 'firstSubmitted'
     * @return array ["nbResponses" => x,"nbCorrectResponses" => y,"nbIncorrectResponses" => z,"nbUnscoredResponses" => a,"data" => $variableData]
     * @deprecated
     */
    public function getItemVariableDataStatsFromDeliveryResult($resultIdentifier, $filter = null) {
        $numberOfResponseVariables = 0;
        $numberOfCorrectResponseVariables = 0;
        $numberOfInCorrectResponseVariables = 0;
        $numberOfUnscoredResponseVariables = 0;
        $numberOfOutcomeVariables = 0;
        $variablesData = $this->getItemVariableDataFromDeliveryResult($resultIdentifier, $filter);
        foreach ($variablesData as $itemVariables) {
            foreach($itemVariables['sortedVars'] as $key => $value){
                if($key == \taoResultServer_models_classes_ResponseVariable::class){
                    foreach($value as $variable){
                        $variable = array_shift($variable);
                        $numberOfResponseVariables++;
                        switch($variable['isCorrect']){
                            case 'correct':
                                $numberOfCorrectResponseVariables++;
                                break;
                            case 'incorrect':
                                $numberOfInCorrectResponseVariables++;
                                break;
                            case 'unscored':
                                $numberOfUnscoredResponseVariables++;
                                break;
                            default:
                                common_Logger::w('The value '.$variable['isCorrect'].' is not a valid value');
                                break;
                        }
                    }
                }
                else{
                    $numberOfOutcomeVariables++;
                }

            }
        }
        $stats = array(
            "nbResponses" => $numberOfResponseVariables,
            "nbCorrectResponses" => $numberOfCorrectResponseVariables,
            "nbIncorrectResponses" => $numberOfInCorrectResponseVariables,
            "nbUnscoredResponses" => $numberOfUnscoredResponseVariables,
            "data" => $variablesData
        );
        return $stats;
    }
    /**
     *  prepare a data set as an associative array, service intended to populate gui controller
     *
     * @param string $resultIdentifier
     * @param string $filter 'lastSubmitted', 'firstSubmitted'
     *
     * @return array
     * @deprecated
     */
    public function getItemVariableDataFromDeliveryResult($resultIdentifier, $filter)
    {
        $itemCallIds = $this->getItemResultsFromDeliveryResult($resultIdentifier);
        $variablesByItem = array();
        foreach ($itemCallIds as $itemCallId) {
            $itemVariables = $this->getVariablesFromObjectResult($itemCallId);

            $item = $this->getItemInfos($itemCallId, $itemVariables);
            $itemIdentifier = $item['uri'];
            $itemLabel = $item['label'];
            $variablesByItem[$itemIdentifier]['itemModel'] = $item['itemModel'];
            foreach ($itemVariables as $variable) {
                //retrieve the type of the variable
                $variableTemp = $variable[0]->variable;
                $variableDescription = array();
                $type = get_class($variableTemp);


                $variableIdentifier = $variableTemp->getIdentifier();

                $variableDescription["uri"] = $variable[0]->uri;
                $variableDescription["var"] = $variableTemp;

                if (method_exists($variableTemp, 'getCorrectResponse') && !is_null($variableTemp->getCorrectResponse())) {
                    if($variableTemp->getCorrectResponse() >= 1){
                        $variableDescription["isCorrect"] = "correct";
                    }
                    else{
                        $variableDescription["isCorrect"] = "incorrect";
                    }
                }
                else{
                    $variableDescription["isCorrect"] = "unscored";
                }

                $variablesByItem[$itemIdentifier]['sortedVars'][$type][$variableIdentifier][$variableTemp->getEpoch()] = $variableDescription;
                $variablesByItem[$itemIdentifier]['label'] = $itemLabel;
            }
        }
        //sort by epoch and filter
        foreach ($variablesByItem as $itemIdentifier => $itemVariables) {

            foreach ($itemVariables['sortedVars'] as $variableType => $variables) {
                foreach ($variables as $variableIdentifier => $observation) {

                    uksort($variablesByItem[$itemIdentifier]['sortedVars'][$variableType][$variableIdentifier], "self::sortTimeStamps");

                    switch ($filter) {
                        case self::VARIABLES_FILTER_LAST_SUBMITTED: {
                                $variablesByItem[$itemIdentifier]['sortedVars'][$variableType][$variableIdentifier] = array(array_pop($variablesByItem[$itemIdentifier]['sortedVars'][$variableType][$variableIdentifier]));
                                break;
                            }
                        case self::VARIABLES_FILTER_FIRST_SUBMITTED: {
                                $variablesByItem[$itemIdentifier]['sortedVars'][$variableType][$variableIdentifier] = array(array_shift($variablesByItem[$itemIdentifier]['sortedVars'][$variableType][$variableIdentifier]));
                                break;
                            }
                    }
                }
            }
        }

        return $variablesByItem;
    }
    /**
     *
     * @param string $a epoch
     * @param string $b epoch
     * @return number
     */
    public static function sortTimeStamps($a, $b) {
        list($usec, $sec) = explode(" ", $a);
        $floata = ((float) $usec + (float) $sec);
        list($usec, $sec) = explode(" ", $b);
        $floatb = ((float) $usec + (float) $sec);

        //the callback is expecting an int returned, for the case where the difference is of less than a second
        //intval(round(floatval($b) - floatval($a),1, PHP_ROUND_HALF_EVEN));
        if ((floatval($floata) - floatval($floatb)) > 0) {
            return 1;
        } elseif ((floatval($floata) - floatval($floatb)) < 0) {
            return -1;
        } else {
            return 0;
        }
    }

    /**
     * return all variables linked to the delviery result and that are not linked to a particular itemResult
     *
     * @param string $resultIdentifier
     * @param array $wantedTypes
     * @return array
     */
    public function getVariableDataFromDeliveryResult($resultIdentifier, $wantedTypes = array(\taoResultServer_models_classes_ResponseVariable::class,\taoResultServer_models_classes_OutcomeVariable::class, \taoResultServer_models_classes_TraceVariable::class)) {
        $variablesData = array();
        foreach ($this->getTestsFromDeliveryResult($resultIdentifier) as $testResult) {
            foreach ($this->getVariablesFromObjectResult($testResult) as $variable) {
                if($variable[0]->callIdTest != "" && in_array(get_class($variable[0]->variable), $wantedTypes)){
                    $variablesData[] = $variable[0]->variable;
                }
            }
        }
        usort($variablesData, function($a, $b){
            list($usec, $sec) = explode(" ", $a->getEpoch());
            $floata = ((float) $usec + (float) $sec);
            list($usec, $sec) = explode(" ", $b->getEpoch());
            $floatb = ((float) $usec + (float) $sec);

            if ((floatval($floata) - floatval($floatb)) > 0) {
                return 1;
            } elseif ((floatval($floata) - floatval($floatb)) < 0) {
                return -1;
            } else {
                return 0;
            }
        });
        return $variablesData;
    }

    /**
     * returns the test taker related to the delivery
     *
     * @param string $resultIdentifier
     * @return User
     */
    public function getTestTaker($resultIdentifier) {
        $testTaker = $this->getImplementation()->getTestTaker($resultIdentifier);
        /** @var \tao_models_classes_UserService $userService */
        $userService = $this->getServiceLocator()->get(\tao_models_classes_UserService::SERVICE_ID);
        $user = $userService->getUserById($testTaker);
        return $user;
    }

    /**
     * Delete a delivery result
     *
     * @param string $resultIdentifier
     * @return boolean
     */
     public function deleteResult($resultIdentifier) {
        return $this->getImplementation()->deleteResult($resultIdentifier);
    }


    /**
     * Return the file data associate to a variable
     * @param $variableUri
     * @return array file data
     * @throws \core_kernel_persistence_Exception
     */
    public function getVariableFile($variableUri) {
        //distinguish QTI file from other "file" base type
        $baseType = $this->getVariableBaseType($variableUri);

        // https://bugs.php.net/bug.php?id=52623 ;
        // if the constant for max buffering, mysqlnd or similar driver
        // is being used without need to adapt buffer size as it is atutomatically adapted for all the data.
        if (core_kernel_classes_DbWrapper::singleton()->getPlatForm()->getName() == 'mysql') {
            if (defined("PDO::MYSQL_ATTR_MAX_BUFFER_SIZE")) {
                $maxBuffer = (is_int(ini_get('upload_max_filesize'))) ? (ini_get('upload_max_filesize')* 1.5) : 10485760 ;
                core_kernel_classes_DbWrapper::singleton()->getSchemaManager()->setAttribute(\PDO::MYSQL_ATTR_MAX_BUFFER_SIZE,$maxBuffer);
            }
        }

        switch ($baseType) {
            case "file": {
                    $value = $this->getVariableCandidateResponse($variableUri);
                    common_Logger::i(var_export(strlen($value), true));
                    $decodedFile = Datatypes::decodeFile($value);
                    common_Logger::i("FileName:");
                    common_Logger::i(var_export($decodedFile["name"], true));
                    common_Logger::i("Mime Type:");
                    common_Logger::i(var_export($decodedFile["mime"], true));
                    $file = array(
                        "data" => $decodedFile["data"],
                        "mimetype" => "Content-type: " . $decodedFile["mime"],
                        "filename" => $decodedFile["name"]);
                    break;
                }
            default: { //legacy files
                    $file = array(
                        "data" => $this->getVariableCandidateResponse($variableUri),
                        "mimetype" => "Content-type: text/xml",
                        "filename" => "trace.xml");
                }
        }
        return $file;
    }

    /**
     * To be reviewed as it implies a dependency towards taoSubjects
     * @param string $resultIdentifier
     * @return array test taker properties values
     */
    public function getTestTakerData($resultIdentifier) {
        $testTaker = $this->gettestTaker($resultIdentifier);
        if (get_class($testTaker) == 'core_kernel_classes_Literal') {
            return $testTaker;
        } elseif (empty($testTaker)) {
            return null;
        } else {
            $arrayOfProperties = [
                OntologyRdfs::RDFS_LABEL,
                GenerisRdf::PROPERTY_USER_LOGIN,
                GenerisRdf::PROPERTY_USER_FIRSTNAME,
                GenerisRdf::PROPERTY_USER_LASTNAME,
                GenerisRdf::PROPERTY_USER_MAIL
            ];
            $propValues = [];
            foreach ($arrayOfProperties as $property) {
                $values = [];
                foreach ($testTaker->getPropertyValues($property) as $value) {
                    $values[] = new \core_kernel_classes_Literal($value);
                }
                $propValues[$property] = $values;
            }
        }
        return $propValues;
    }

    /**
     *
     * @param \core_kernel_classes_Resource $delivery
     * @return \taoResultServer_models_classes_ReadableResultStorage
     * @throws common_exception_Error
     */
    public function getReadableImplementation(\core_kernel_classes_Resource $delivery)
    {
        /** @var ResultServerService  $service */
        $service = $this->getServiceLocator()->get(ResultServerService::SERVICE_ID);
        $resultStorage = $service->getResultStorage($delivery);

        /** NoResultStorage it's not readable only writable */
        if ($resultStorage instanceof NoResultStorage) {
            throw NoResultStorageException::create();
        }

        if (!$resultStorage instanceof \taoResultServer_models_classes_ReadableResultStorage){
            throw new \common_exception_Error('The results storage it is not readable');
        }

        return $resultStorage;
    }

    /**
     * Get the array of column names indexed by their unique column id.
     *
     * @param \tao_models_classes_table_Column[] $columns
     * @return array
     */
    public function getColumnNames(array $columns)
    {
        return array_reduce($columns, function ($carry, \tao_models_classes_table_Column $column) {
            /** @var ContextTypePropertyColumn|VariableColumn $column */
            $carry[$this->getColumnId($column)] = $column->getLabel();
            return $carry;
        });
    }

    /**
     * @param \tao_models_classes_table_Column|ContextTypePropertyColumn|VariableColumn $column
     * @return string
     */
    private function getColumnId(\tao_models_classes_table_Column $column)
    {
        return $column instanceof ContextTypePropertyColumn
            ? $column->getProperty()->getUri() .'_'. $column->getContextType()
            : $column->getContextIdentifier() .'_'. $column->getIdentifier();
    }

    /**
     * @param core_kernel_classes_Resource $delivery
     * @param                              $columns - columns to be exported
     * @param                              $filter  'lastSubmitted' or 'firstSubmitted'
     * @param array                        $storageOptions
     * @return array
     */
    public function getResultsByDelivery(\core_kernel_classes_Resource $delivery, $columns, $filter, array $storageOptions = [])
    {
        $rows = array();

        //The list of delivery Results matching the current selection filters
        $results = array();

        $this->setImplementation($this->getReadableImplementation($delivery));
        foreach($this->getImplementation()->getResultByDelivery([$delivery->getUri()], $storageOptions) as $result){
            $results[] = $result['deliveryResultIdentifier'];
        }
        $dpmap = array();
        foreach ($columns as $column) {
            $dataprovider = $column->getDataProvider();
            $found = false;
            foreach ($dpmap as $k => $dp) {
                if ($dp['instance'] == $dataprovider) {
                    $found = true;
                    $dpmap[$k]['columns'][] = $column;
                }
            }
            if (!$found) {
                $dpmap[] = array(
                    'instance'	=> $dataprovider,
                    'columns'	=> array(
                        $column
                    )
                );
            }
        }

        foreach ($dpmap as $arr) {
            $arr['instance']->prepare($results, $arr['columns']);
        }

        /** @var DeliveryExecution $result */
        foreach($results as $result) {
            $cellData = array();

            /** @var ContextTypePropertyColumn|VariableColumn $column */
            foreach ($columns as $column) {
                $cellKey = $this->getColumnId($column);

                if (count($column->getDataProvider()->cache) > 0) {
                    // grade or response column values
                    $cellData[$cellKey] = self::filterCellData($column->getDataProvider()->getValue(new core_kernel_classes_Resource($result), $column), $filter);
                } else {
                    // test taker or delivery property values
                    $resource = $column->isTestTakerType()
                        ? $this->getTestTaker($result)
                        : $this->getDelivery($result);

                    $values = $resource->getPropertyValues($column->getProperty());

                    $values = array_map(function ($value) use ($column) {
                        if (\common_Utils::isUri($value)) {
                            $value = (new core_kernel_classes_Resource($value))->getLabel();
                        } else {
                            $value = (string) $value;
                        }

                        if (in_array($column->getProperty()->getUri(), [DeliveryAssemblyService::PROPERTY_START, DeliveryAssemblyService::PROPERTY_END])) {
                            $value = \tao_helpers_Date::displayeDate($value, \tao_helpers_Date::FORMAT_VERBOSE);
                        }

                        return $value;
                    }, $values);

                    // if it's a guest test taker (it has no property values at all), let's display the uri as label
                    if ($column->isTestTakerType() && empty($values) && $column->getProperty()->getUri() == OntologyRdfs::RDFS_LABEL) {
                        switch(true) {
                            case $resource instanceof core_kernel_classes_Resource:
                                $values[] = $resource->getUri();
                                break;
                            case $resource instanceof User:
                                $values[] = $resource->getIdentifier();
                                break;
                            default:
                                throw new \Exception('Invalid type of resource property values.');
                        }
                    }

                    $cellData[$cellKey] = [self::filterCellData(implode(' ', $values), $filter)];
                }
            }
            $rows[] = array(
                'id' => $result,
                'cell' => $cellData
            );
        }
        return $rows;
    }

    /**
     * @param array $deliveryUris
     * @return int
     */
    public function countResultByDelivery(array $deliveryUris)
    {
        return $this->getImplementation()->countResultByDelivery($deliveryUris);
    }

    /**
     * @param array|string $resultsIds
     * @return mixed
     * @throws common_exception_Error
     */
    protected function getResultsVariables($resultsIds)
    {
        return $this->getImplementation()->getDeliveryVariables($resultsIds);
    }

    /**
     * Retrieve the different variables columns pertainign to the current selection of results
     * Implementation note : it nalyses all the data collected to identify the different response variables submitted by the items in the context of activities
     */
    public function getVariableColumns($delivery, $variableClassUri)
    {
        $columns = array();
        /** @var ResultServiceWrapper $resultServiceWrapper */
        $resultServiceWrapper = $this->getServiceLocator()->get(ResultServiceWrapper::SERVICE_ID);

        $this->setImplementation($this->getReadableImplementation($delivery));
        //The list of delivery Results matching the current selection filters
        $results = $this->getImplementation()->getResultByDelivery([$delivery->getUri()]);

        //retrieveing all individual response variables referring to the  selected delivery results
        $itemIndex = $this->getItemIndexer($delivery);

        $resultsIds  = array_column($results, 'deliveryResultIdentifier');

        //retrieving The list of the variables identifiers per activities defintions as observed
        $variableTypes = array();

        $resultLanguage = $this->getResultLanguage();

        foreach (array_chunk($resultsIds, $resultServiceWrapper->getOption(ResultServiceWrapper::RESULT_COLUMNS_CHUNK_SIZE_OPTION)) as $resultsIdsItem) {
            $selectedVariables = $this->getResultsVariables($resultsIdsItem);
            foreach ($selectedVariables as $variable) {
                $variable = $variable[0];
                if($this->isResultVariable($variable, $variableClassUri)) {
                    //variableIdentifier
                    $variableIdentifier = $variable->variable->identifier;
                    if (!is_null($variable->item)) {
                        $uri = $variable->item;
                        $contextIdentifierLabel = $itemIndex->getItemValue($uri, $resultLanguage, 'label');
                    } else {
                        $uri = $variable->test;
                        $testData = $this->getTestMetadata($delivery, $variable->test);
                        $contextIdentifierLabel = $testData->getLabel();
                    }

                    $variableTypes[$uri.$variableIdentifier] = array("contextLabel" => $contextIdentifierLabel, "contextId" => $uri, "variableIdentifier" => $variableIdentifier);
                }
            }
        }

        foreach ($variableTypes as $variableType){

            switch ($variableClassUri){
                case \taoResultServer_models_classes_OutcomeVariable::class :
                    $columns[] = new GradeColumn($variableType["contextId"], $variableType["contextLabel"], $variableType["variableIdentifier"]);
                    break;
                case \taoResultServer_models_classes_ResponseVariable::class :
                    $columns[] = new ResponseColumn($variableType["contextId"], $variableType["contextLabel"], $variableType["variableIdentifier"]);
                    break;
                default:
                    $columns[] = new ResponseColumn($variableType["contextId"], $variableType["contextLabel"], $variableType["variableIdentifier"]);
            }
        }
        $arr = array();
        foreach ($columns as $column) {
            $arr[] = $column->toArray();
        }
        return $arr;
    }

    /**
     * Check if provided variable is a result variable.
     *
     * @param $variable
     * @param $variableClassUri
     * @return bool
     */
    private function isResultVariable($variable, $variableClassUri)
    {
        $responseVariableClass = \taoResultServer_models_classes_ResponseVariable::class;
        $outcomeVariableClass = \taoResultServer_models_classes_OutcomeVariable::class;
        $class = isset($variable->class) ? $variable->class : get_class($variable->variable);

        return (null != $variable->item ||  null != $variable->test)
            && (
                $class == $outcomeVariableClass
                && $variableClassUri == $outcomeVariableClass
            ) || (
                $class == $responseVariableClass
                && $variableClassUri == $responseVariableClass
            );
    }

    /**
     * Sort the list of variables by filters
     *
     * List of variables contains the response for an interaction.
     * Each attempts is an entry in $observationList
     *
     * 3 allowed filters: firstSubmitted, lastSubmitted, all
     *
     * @param array $observationsList The list of variable values
     * @param string $filterData The filter
     * @param string $allDelimiter $delimiter to separate values in "all" filter context
     * @return array
     */
    public static function filterCellData($observationsList, $filterData, $allDelimiter = '|')
    {
        //if the cell content is not an array with multiple entries, do not filter
        if (!is_array($observationsList)) {
            return $observationsList;
        }

        // Sort by TimeStamps
        uksort($observationsList, "oat\\taoOutcomeUi\\model\\ResultsService::sortTimeStamps");

        // Extract the value to make this array flat
        $observationsList = array_map(function($obs) {
            return $obs[0];
        }, $observationsList);

        switch ($filterData) {

            case self::VARIABLES_FILTER_LAST_SUBMITTED:
                $value = array_pop($observationsList);
            break;

            case self::VARIABLES_FILTER_FIRST_SUBMITTED:
                $value = array_shift($observationsList);
            break;

            case self::VARIABLES_FILTER_ALL:
            default:
                $value = implode($allDelimiter, $observationsList);
            break;
        }

        return [$value];
    }

    /**
     * @param $delivery
     * @return ItemCompilerIndex
     * @throws common_exception_Error
     */
    private function getItemIndexer($delivery)
    {
        $deliveryUri = $delivery->getUri();
        if (!array_key_exists($deliveryUri, $this->indexerCache)) {
            $directory = $this->getPrivateDirectory($delivery);
            $indexer = $this->getDecompiledIndexer($directory);
            $this->indexerCache[$deliveryUri] = $indexer;
        }
        $indexer = $this->indexerCache[$deliveryUri] ;
        return $indexer;
    }

    /**
     * @param $delivery
     * @param $testUri
     * @return ResourceCompiledMetadataHelper
     */
    private function getTestMetadata(core_kernel_classes_Resource $delivery, $testUri)
    {
        if (isset($this->testMetadataCache[$testUri])) {
            return $this->testMetadataCache[$testUri];
        }

        $compiledMetadataHelper = new ResourceCompiledMetadataHelper();

        try {
            $directory = $this->getPrivateDirectory($delivery);
            $testMetadata = $this->loadTestMetadata($directory, $testUri);
            if (!empty($testMetadata)) {
                $compiledMetadataHelper->unserialize($testMetadata);
            }
        } catch (\Exception $e) {
            \common_Logger::d('Ignoring data not found exception for Test Metadata');
        }

        $this->testMetadataCache[$testUri] = $compiledMetadataHelper;

        return $this->testMetadataCache[$testUri];
    }

    /**
     * Load test metadata from file. For deliveries without compiled file try  to compile test metadata.
     *
     * @param tao_models_classes_service_StorageDirectory $directory
     * @param string $testUri
     * @return false|string
     * @throws \FileNotFoundException
     * @throws common_Exception
     */
    private function loadTestMetadata(tao_models_classes_service_StorageDirectory $directory, $testUri) {
        try {
            $testMetadata = $this->loadTestMetadataFromFile($directory);
        } catch (FileNotFoundException $e) {
            \common_Logger::d('Compiled test metadata file not found. Try to compile a new file.');

            $this->compileTestMetadata($directory, $testUri);
            $testMetadata = $this->loadTestMetadataFromFile($directory);
        }

        return $testMetadata;
    }

    /**
     * Get teast metadata from file.
     *
     * @param tao_models_classes_service_StorageDirectory $directory
     * @return false|string
     */
    private function loadTestMetadataFromFile(tao_models_classes_service_StorageDirectory $directory)
    {
        return $directory->read(taoQtiTest_models_classes_QtiTestService::TEST_COMPILED_METADATA_FILENAME);
    }

    /**
     * Compile test metadata and store into file.
     * Added for backward compatibility for deliveries without compiled test metadata.
     *
     * @param tao_models_classes_service_StorageDirectory $directory
     * @param $testUri
     * @throws \FileNotFoundException
     * @throws common_Exception
     */
    private function compileTestMetadata(tao_models_classes_service_StorageDirectory $directory, $testUri)
    {
        $resource = $this->getResource($testUri);

        /** @var ResourceMetadataCompilerInterface $resourceMetadataCompiler */
        $resourceMetadataCompiler = $this->getServiceLocator()->get(ResourceJsonMetadataCompiler::SERVICE_ID);
        $metadata = $resourceMetadataCompiler->compile($resource);

        $directory->write(taoQtiTest_models_classes_QtiTestService::TEST_COMPILED_METADATA_FILENAME, json_encode($metadata));
    }

    /**
     * @param string $directory
     * @return QtiTestCompilerIndex
     */
    private function getDecompiledIndexer(tao_models_classes_service_StorageDirectory $directory)
    {
        $itemIndex = new QtiTestCompilerIndex();
        try {
            $data = $directory->read(taoQtiTest_models_classes_QtiTestService::TEST_COMPILED_INDEX);
            if ($data) {
                $itemIndex->unserialize($data);
            }
        } catch (\Exception $e) {
            \common_Logger::d('Ignoring file not found exception for Items Index');
        }
        return $itemIndex;
    }

    /**
     * Should be changed if real result language would matter
     * @return string
     */
    private function getResultLanguage()
    {
        return DEFAULT_LANG;
    }

    /**
     * @param $executionUri
     * @return core_kernel_classes_Resource
     * @throws \common_exception_NotFound
     */
    private function getDeliveryByResultId($executionUri)
    {
        if (!array_key_exists($executionUri, $this->executionCache)) {
            /** @var DeliveryExecution $execution */
            $execution = $this->getServiceManager()->get(ServiceProxy::class)->getDeliveryExecution($executionUri);
            $delivery = $execution->getDelivery();
            $this->executionCache[$executionUri] = $delivery;
        }
        $delivery = $this->executionCache[$executionUri];
        return $delivery;
    }

    /**
     * @param $delivery
     * @return array
     * @throws common_exception_Error
     */
    private function getDirectoryIds($delivery)
    {
        $runtime = $this->getServiceLocator()->get(AssignmentService::SERVICE_ID)->getRuntime($delivery);
        $inputParameters = \tao_models_classes_service_ServiceCallHelper::getInputValues($runtime, []);
        $directoryIds = explode('|', $inputParameters['QtiTestCompilation']);

        return $directoryIds;
    }

    /**
     * @param $delivery
     * @return tao_models_classes_service_StorageDirectory
     * @throws common_exception_Error
     */
    private function getPrivateDirectory($delivery)
    {
        $directoryIds = $this->getDirectoryIds($delivery);
        $fileStorage = \tao_models_classes_service_FileStorage::singleton();

        return $fileStorage->getDirectoryById($directoryIds[0]);
    }

    /**
     * @return mixed
     */
    public function getServiceLocator()
    {
        if (!$this->serviceLocator) {
            $this->setServiceLocator(ServiceManager::getServiceManager());
        }
        return $this->serviceLocator;
    }
}
