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
use \core_kernel_classes_Resource;
use oat\taoOutcomeUi\model\table\VariableColumn;
use oat\taoOutcomeUi\model\Wrapper\ResultServiceWrapper;
use oat\taoResultServer\models\classes\NoResultStorage;
use oat\taoResultServer\models\classes\NoResultStorageException;
use oat\taoResultServer\models\classes\ResultManagement;
use oat\taoResultServer\models\classes\ResultService;
use tao_helpers_Date;
use \tao_models_classes_ClassService;
use oat\taoOutcomeUi\helper\Datatypes;
use oat\taoDelivery\model\execution\DeliveryExecution;
use oat\taoResultServer\models\classes\ResultServerService;
use tao_models_classes_service_StorageDirectory;
use taoQtiTest_models_classes_QtiTestService;
use oat\taoQtiTest\models\QtiTestCompilerIndex;
use taoResultServer_models_classes_Variable;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use oat\tao\model\OntologyClassService;
use oat\taoDelivery\model\RuntimeService;

class ResultsService extends OntologyClassService implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    const VARIABLES_FILTER_LAST_SUBMITTED = 'lastSubmitted';
    const VARIABLES_FILTER_FIRST_SUBMITTED = 'firstSubmitted';
    const VARIABLES_FILTER_ALL = 'all';

    const PERSISTENCE_CACHE_KEY = 'resultCache';

    const PERIODS = [self::FILTER_START_FROM, self::FILTER_START_TO, self::FILTER_END_FROM, self::FILTER_END_TO];
    const DELIVERY_EXECUTION_STARTED_AT = 'delivery_execution_started_at';
    const DELIVERY_EXECUTION_FINISHED_AT = 'delivery_execution_finished_at';
    const FILTER_START_FROM = 'startfrom';
    const FILTER_START_TO = 'startto';
    const FILTER_END_FROM = 'endfrom';
    const FILTER_END_TO = 'endto';

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
        return 'resultPageCache:' . $resultIdentifier . ':' . $suffix;
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
        } elseif (count($fullKeys) < $initialCount) {
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
    public function getRootClass()
    {
        return $this->getClass(ResultService::DELIVERY_RESULT_CLASS_URI);
    }

    public function setImplementation(ResultManagement $implementation)
    {
        $this->implementation = $implementation;
    }

    /**
     * @return ResultManagement
     * @throws common_exception_Error
     */
    public function getImplementation()
    {
        if ($this->implementation == null) {
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
    public function getVariables($resultIdentifier, $flat = true)
    {
        $variables = [];
        //this service is slow due to the way the data model design
        //if the delvieryResult related execution is finished, the data is stored in cache.

        $serial = 'deliveryResultVariables:' . $resultIdentifier;
        //if (common_cache_FileCache::singleton()->has($serial)) {
        //    $variables = common_cache_FileCache::singleton()->get($serial);
        //} else {
        $resultVariables = $this->getImplementation()->getDeliveryVariables($resultIdentifier);
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
            $returnValue = [];
            foreach ($variables as $key => $itemResultVariables) {
                $newKeys = [];
                $oldKeys = array_keys($itemResultVariables);
                foreach ($oldKeys as $oldKey) {
                    $newKeys[] = $key . '_' . $oldKey;
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
    public function getDelivery($resultIdentifier)
    {
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
    public function getItemResultsFromDeliveryResult($resultIdentifier)
    {
        return $this->getImplementation()->getRelatedItemCallIds($resultIdentifier);
    }

    /**
     * Returns all label of itemResults related to the delvieryResults
     * @param string $resultIdentifier
     * @return array string uri
     * */
    public function getTestsFromDeliveryResult($resultIdentifier)
    {
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
    public function getItemFromItemResult($itemCallId, $itemVariables = [])
    {
        $item = null;

        if (empty($itemVariables)) {
            $itemVariables = $this->getImplementation()->getVariables($itemCallId);
        }

        //get the first variable (item are the same in all)
        $tmpItems = array_shift($itemVariables);

        //get the first object
        $itemUri = $tmpItems[0]->item;

        $delivery = $this->getDeliveryByResultId($tmpItems[0]->deliveryResultIdentifier);

        $itemIndexer = $this->getItemIndexer($delivery);

        if (!is_null($itemUri)) {
            $langItem = $itemIndexer->getItem($itemUri, $this->getResultLanguage());
            $item = array_merge(is_array($langItem) ? $langItem : [], ['uriResource' => $itemUri]);
        }
        return $item;
    }

    /**
     *
     * @param string $test
     * @return \core_kernel_classes_Resource
     */
    public function getVariableFromTest($test)
    {
        $returnTest = null;
        $tests = $this->getImplementation()->getVariables($test);

        //get the first variable (item are the same in all)
        $tmpTests = array_shift($tests);

        //get the first object
        if (!is_null($tmpTests[0]->test)) {
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
    public function getVariableCandidateResponse($variableUri)
    {
        return $this->getImplementation()->getVariableProperty($variableUri, 'candidateResponse');
    }

    /**
     *
     * @param string $variableUri
     * @return string
     */
    public function getVariableBaseType($variableUri)
    {
        return $this->getImplementation()->getVariableProperty($variableUri, 'baseType');
    }


    /**
     *
     * @param array $variablesData
     * @return array ["nbResponses" => x,"nbCorrectResponses" => y,"nbIncorrectResponses" => z,"nbUnscoredResponses" => a,"data" => $variableData]
     */
    public function calculateResponseStatistics($variablesData)
    {
        $numberOfResponseVariables = 0;
        $numberOfCorrectResponseVariables = 0;
        $numberOfInCorrectResponseVariables = 0;
        $numberOfUnscoredResponseVariables = 0;
        foreach ($variablesData as $epoch => $itemVariables) {
            foreach ($itemVariables as $key => $value) {
                if ($key == \taoResultServer_models_classes_ResponseVariable::class) {
                    foreach ($value as $variable) {
                        $numberOfResponseVariables++;
                        switch ($variable['isCorrect']) {
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
                                common_Logger::w('The value ' . $variable['isCorrect'] . ' is not a valid value');
                                break;
                        }
                    }
                }
            }
        }
        $stats = [
            "nbResponses" => $numberOfResponseVariables,
            "nbCorrectResponses" => $numberOfCorrectResponseVariables,
            "nbIncorrectResponses" => $numberOfInCorrectResponseVariables,
            "nbUnscoredResponses" => $numberOfUnscoredResponseVariables,
        ];
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
    public function getStructuredVariables($resultIdentifier, $filter, $wantedTypes = [])
    {
        $itemCallIds = $this->getItemResultsFromDeliveryResult($resultIdentifier);

        // splitting call ids into chunks to perform bulk queries
        $itemCallIdChunks = array_chunk($itemCallIds, 50);

        $itemVariables = [];
        foreach ($itemCallIdChunks as $ids) {
            $itemVariables = array_merge($itemVariables, $this->getVariablesFromObjectResult($ids, $wantedTypes));
        }

        return $this->structureItemVariables($itemVariables, $filter);
    }

    public function structureItemVariables($itemVariables, $filter)
    {
        usort($itemVariables, function ($a, $b) {
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

        $attempts = $this->splitByItemAndAttempt($itemVariables, $filter);
        $variablesByItem = [];
        foreach ($attempts as $time => $variables) {
            foreach ($variables as $itemVariable) {
                $variable = $itemVariable->variable;
                $itemCallId = $itemVariable->callIdItem;
                if ($variable->getIdentifier() == 'numAttempts') {
                    $variablesByItem[$time] = $this->getItemInfos($itemCallId, [[$itemVariable]]);
                    $variablesByItem[$time]['attempt'] = $variable->getValue();
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

                // some dangerous assumptions about the call Id structure
                $callIdParts = explode('.', $itemCallId);
                $variablesByItem[$time]['internalIdentifier'] = $callIdParts[count($callIdParts) - 2];
                $variablesByItem[$time][get_class($variable)][$variable->getIdentifier()] = $variableDescription;
            }
        }

        return $variablesByItem;
    }

    public function splitByItemAndAttempt($itemVariables, $filter)
    {
        $sorted = [];
        foreach ($this->splitByItem($itemVariables) as $variables) {
            $itemCallId = current($variables)->callIdItem;
            $byAttempt = $this->splitByAttempt($variables);
            switch ($filter) {
                case self::VARIABLES_FILTER_ALL:
                    foreach ($byAttempt as $time => $attempt) {
                        $sorted[$time . $itemCallId] = $attempt;
                    }
                    break;
                case self::VARIABLES_FILTER_FIRST_SUBMITTED:
                    reset($byAttempt);
                    $sorted[key($byAttempt) . $itemCallId] = current($byAttempt);
                    break;
                case self::VARIABLES_FILTER_LAST_SUBMITTED:
                    end($byAttempt);
                    $sorted[key($byAttempt) . $itemCallId] = current($byAttempt);
                    break;
                default:
                    throw new \common_exception_InconsistentData('Unknown Filter ' . $filter);
            }
        }
        ksort($sorted);
        return $sorted;
    }

    /**
     * Split item variables by item
     */
    public function splitByItem($itemVariables)
    {
        $byItem = [];
        foreach ($itemVariables as $variable) {
            $itemVariable = $variable[0];
            if (!is_null($itemVariable->callIdItem)) {
                if (!isset($byItem[$itemVariable->callIdItem])) {
                    $byItem[$itemVariable->callIdItem] = [$itemVariable];
                } else {
                    $byItem[$itemVariable->callIdItem][] = $itemVariable;
                }
            }
        }
        return $byItem;
    }

    public function splitByAttempt($itemVariables)
    {
        $attempts = [];
        foreach ($itemVariables as $variable) {
            if ($variable->variable->getIdentifier() == 'numAttempts') {
                $attempts[(string)$variable->variable->getCreationTime()] = [];
            }
        }
        foreach ($itemVariables as $variable) {
            $cand = null;
            $bestDist = null;
            foreach (array_keys($attempts) as $time) {
                $dist = abs($time - $variable->variable->getCreationTime());
                if (is_null($bestDist) || $dist < $bestDist) {
                    $bestDist = $dist;
                    $cand = $time;
                }
            }
            $attempts[$cand][] = $variable;
        }
        return $attempts;
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
    public function getItemVariableDataStatsFromDeliveryResult($resultIdentifier, $filter = null)
    {
        $numberOfResponseVariables = 0;
        $numberOfCorrectResponseVariables = 0;
        $numberOfInCorrectResponseVariables = 0;
        $numberOfUnscoredResponseVariables = 0;
        $numberOfOutcomeVariables = 0;
        $variablesData = $this->getItemVariableDataFromDeliveryResult($resultIdentifier, $filter);
        foreach ($variablesData as $itemVariables) {
            foreach ($itemVariables['sortedVars'] as $key => $value) {
                if ($key == \taoResultServer_models_classes_ResponseVariable::class) {
                    foreach ($value as $variable) {
                        $variable = array_shift($variable);
                        $numberOfResponseVariables++;
                        switch ($variable['isCorrect']) {
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
                                common_Logger::w('The value ' . $variable['isCorrect'] . ' is not a valid value');
                                break;
                        }
                    }
                } else {
                    $numberOfOutcomeVariables++;
                }
            }
        }
        $stats = [
            "nbResponses" => $numberOfResponseVariables,
            "nbCorrectResponses" => $numberOfCorrectResponseVariables,
            "nbIncorrectResponses" => $numberOfInCorrectResponseVariables,
            "nbUnscoredResponses" => $numberOfUnscoredResponseVariables,
            "data" => $variablesData
        ];
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
        $variablesByItem = [];
        foreach ($itemCallIds as $itemCallId) {
            $itemVariables = $this->getVariablesFromObjectResult($itemCallId);

            $item = $this->getItemInfos($itemCallId, $itemVariables);
            $itemIdentifier = $item['uri'];
            $itemLabel = $item['label'];
            $variablesByItem[$itemIdentifier]['itemModel'] = $item['itemModel'];
            foreach ($itemVariables as $variable) {
                //retrieve the type of the variable
                $variableTemp = $variable[0]->variable;
                $variableDescription = [];
                $type = get_class($variableTemp);


                $variableIdentifier = $variableTemp->getIdentifier();

                $variableDescription["uri"] = $variable[0]->uri;
                $variableDescription["var"] = $variableTemp;

                if (method_exists($variableTemp, 'getCorrectResponse') && !is_null($variableTemp->getCorrectResponse())) {
                    if ($variableTemp->getCorrectResponse() >= 1) {
                        $variableDescription["isCorrect"] = "correct";
                    } else {
                        $variableDescription["isCorrect"] = "incorrect";
                    }
                } else {
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
                                $variablesByItem[$itemIdentifier]['sortedVars'][$variableType][$variableIdentifier] = [array_pop($variablesByItem[$itemIdentifier]['sortedVars'][$variableType][$variableIdentifier])];
                                break;
                        }
                        case self::VARIABLES_FILTER_FIRST_SUBMITTED: {
                                $variablesByItem[$itemIdentifier]['sortedVars'][$variableType][$variableIdentifier] = [array_shift($variablesByItem[$itemIdentifier]['sortedVars'][$variableType][$variableIdentifier])];
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
    public static function sortTimeStamps($a, $b)
    {
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
    public function getVariableDataFromDeliveryResult($resultIdentifier, $wantedTypes = [\taoResultServer_models_classes_ResponseVariable::class,\taoResultServer_models_classes_OutcomeVariable::class, \taoResultServer_models_classes_TraceVariable::class])
    {
        $testCallIds = $this->getTestsFromDeliveryResult($resultIdentifier);
        return $this->extractTestVariables($this->getVariablesFromObjectResult($testCallIds), $wantedTypes);
    }

    public function extractTestVariables(array $variableObjects, array $wantedTypes, string $filter = self::VARIABLES_FILTER_ALL)
    {
        $variableObjects = array_filter($variableObjects, static function (array $variableObject) use ($wantedTypes) {
            $variable = current($variableObject);

            return $variable->callIdItem === null && in_array(get_class($variable->variable), $wantedTypes, true);
        });

        $variableObjects = array_map(static function (array $variableObject) {
            return current($variableObject)->variable;
        }, $variableObjects);

        usort($variableObjects, static function (
            taoResultServer_models_classes_Variable $a,
            taoResultServer_models_classes_Variable $b
        ) use ($filter) {
            if ($filter === self::VARIABLES_FILTER_LAST_SUBMITTED) {
                return $b->getCreationTime() - $a->getCreationTime();
            }

            return $a->getCreationTime() - $b->getCreationTime();
        });

        if (in_array($filter, [self::VARIABLES_FILTER_FIRST_SUBMITTED, self::VARIABLES_FILTER_LAST_SUBMITTED], true)) {
            $uniqueVariableIdentifiers = [];

            $variableObjects = array_filter($variableObjects, static function (
                taoResultServer_models_classes_Variable $variable
            ) use (&$uniqueVariableIdentifiers) {
                if (in_array($variable->getIdentifier(), $uniqueVariableIdentifiers, true)) {
                    return false;
                }
                $uniqueVariableIdentifiers[] = $variable->getIdentifier();

                return true;
            });
        }

        return $variableObjects;
    }

    /**
     * returns the test taker related to the delivery
     *
     * @param string $resultIdentifier
     * @return User
     */
    public function getTestTaker($resultIdentifier)
    {
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
    public function deleteResult($resultIdentifier)
    {
        return $this->getImplementation()->deleteResult($resultIdentifier);
    }


    /**
     * Return the file data associate to a variable
     * @param $variableUri
     * @return array file data
     * @throws \core_kernel_persistence_Exception
     */
    public function getVariableFile($variableUri)
    {
        //distinguish QTI file from other "file" base type
        $baseType = $this->getVariableBaseType($variableUri);

        switch ($baseType) {
            case "file": {
                    $value = $this->getVariableCandidateResponse($variableUri);
                    common_Logger::i(var_export(strlen($value), true));
                    $decodedFile = Datatypes::decodeFile($value);
                    common_Logger::i("FileName:");
                    common_Logger::i(var_export($decodedFile["name"], true));
                    common_Logger::i("Mime Type:");
                    common_Logger::i(var_export($decodedFile["mime"], true));
                    $file = [
                        "data" => $decodedFile["data"],
                        "mimetype" => "Content-type: " . $decodedFile["mime"],
                        "filename" => $decodedFile["name"]];
                    break;
            }
            default: { //legacy files
                    $file = [
                        "data" => $this->getVariableCandidateResponse($variableUri),
                        "mimetype" => "Content-type: text/xml",
                        "filename" => "trace.xml"];
            }
        }
        return $file;
    }

    /**
     * To be reviewed as it implies a dependency towards taoSubjects
     * @param string $resultIdentifier
     * @return array test taker properties values
     */
    public function getTestTakerData($resultIdentifier)
    {
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

        if (!$resultStorage instanceof \taoResultServer_models_classes_ReadableResultStorage) {
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
        if ($column instanceof ContextTypePropertyColumn) {
            $id = $column->getProperty()->getUri() . '_' . $column->getContextType();
        } else {
            $id = $column->getContextIdentifier() . '_' . $column->getIdentifier();
        }

        return $id;
    }

    /**
     * @param core_kernel_classes_Resource $delivery
     * @param array $storageOptions
     * @param array $filters
     * @throws common_Exception
     * @throws common_exception_Error
     */
    public function getResultsByDelivery(\core_kernel_classes_Resource $delivery, array $storageOptions = [], array $filters = [])
    {
        //The list of delivery Results matching the current selection filters
        $this->setImplementation($this->getReadableImplementation($delivery));
        return $this->findResultsByDeliveryAndFilters($delivery, $filters, $storageOptions);
    }

    /**
     * @param string $result
     * @return bool
     */
    protected function shouldResultBeSkipped($result)
    {
        return false;
    }

    /**
     * @param array $results
     * @param                              $columns - columns to be exported
     * @param                              $filter  'lastSubmitted' or 'firstSubmitted'
     * @param array $filters
     * @param int $offset
     * @param int $limit
     * @return array
     * @throws common_Exception
     * @throws common_exception_Error
     */
    public function getCellsByResults(array $results, $columns, $filter, array $filters = [], $offset = 0, $limit = null)
    {
        $rows = [];
        $dataProviderMap = $this->collectColumnDataProviderMap($columns);

        if (!array_key_exists($offset, $results)) {
            return null;
        }

        /** @var DeliveryExecution $result */
        for ($i = $offset; $i < ($offset + $limit); $i++) {
            if (!array_key_exists($i, $results)) {
                break;
            }
            $result = $results[$i];
            if ($this->shouldResultBeSkipped($result)) {
                continue;
            }

            // initialize column data providers for single result
            foreach ($dataProviderMap as $element) {
                $element['instance']->prepare([$result], $element['columns']);
            }

            $cellData = [];

            /** @var ContextTypePropertyColumn|VariableColumn $column */
            foreach ($columns as $column) {
                $cellKey = $this->getColumnId($column);

                $cellData[$cellKey] = null;
                if (count($column->getDataProvider()->cache) > 0) {
                    // grade or response column values
                    $cellData[$cellKey] = self::filterCellData($column->getDataProvider()->getValue(new core_kernel_classes_Resource($result), $column), $filter);
                } elseif ($column instanceof ContextTypePropertyColumn) {
                    // test taker or delivery property values
                    $resource = $column->isTestTakerType()
                        ? $this->getTestTaker($result)
                        : $this->getDelivery($result);

                    $property = $column->getProperty();
                    if ($resource instanceof User) {
                        $property = $column->getProperty()->getUri();
                    }
                    $values = $resource->getPropertyValues($property);

                    $values = array_map(function ($value) use ($column) {
                        if (\common_Utils::isUri($value)) {
                            $value = (new core_kernel_classes_Resource($value))->getLabel();
                        } else {
                            $value = (string) $value;
                        }

                        if (in_array($column->getProperty()->getUri(), [DeliveryAssemblyService::PROPERTY_START, DeliveryAssemblyService::PROPERTY_END])) {
                            $value = tao_helpers_Date::displayeDate($value, tao_helpers_Date::FORMAT_VERBOSE);
                        }

                        return $value;
                    }, $values);

                    // if it's a guest test taker (it has no property values at all), let's display the uri as label
                    if ($column->isTestTakerType() && empty($values) && $column->getProperty()->getUri() == OntologyRdfs::RDFS_LABEL) {
                        switch (true) {
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
            if ($this->filterData($cellData, $filters)) {
                $this->convertDates($cellData);
                $rows[] = [
                    'id' => $result,
                    'cell' => $cellData
                ];
            }
        }

        return $rows;
    }

    /**
     * @param $columns
     * @return array
     */
    private function collectColumnDataProviderMap($columns)
    {
        $dataProviderMap = [];
        foreach ($columns as $column) {
            $dataProvider = $column->getDataProvider();
            $found = false;
            foreach ($dataProviderMap as $index => $element) {
                if ($element['instance'] == $dataProvider) {
                    $dataProviderMap[$index]['columns'][] = $column;
                    $found = true;
                }
            }
            if (!$found) {
                $dataProviderMap[] = [
                    'instance' => $dataProvider,
                    'columns' => [$column]
                ];
            }
        }

        return $dataProviderMap;
    }

    /**
     * @param $data
     * @throws common_Exception
     */
    private function convertDates(&$data)
    {
        $sd = current($data[self::DELIVERY_EXECUTION_STARTED_AT]);
        $data[self::DELIVERY_EXECUTION_STARTED_AT][0] = $sd ? tao_helpers_Date::displayeDate($sd) : '';
        $ed = current($data[self::DELIVERY_EXECUTION_FINISHED_AT]);
        $data[self::DELIVERY_EXECUTION_FINISHED_AT][0] = $ed ? tao_helpers_Date::displayeDate($ed) : '';
    }

    /**
     * Check that data is apply to these filter params
     * @param $row
     * @param array $filters
     * @return bool
     */
    private function filterData($row, array $filters)
    {
        $matched = true;
        if (count($filters) && count(array_intersect(self::PERIODS, array_keys($filters)))) {
            $startDate = current($row[self::DELIVERY_EXECUTION_STARTED_AT]);
            $startTime = $startDate ? tao_helpers_Date::getTimeStamp($startDate) : 0;

            $endDate = current($row[self::DELIVERY_EXECUTION_FINISHED_AT]);
            $endTime = $endDate ? tao_helpers_Date::getTimeStamp($endDate) : 0;

            if ($matched && array_key_exists(self::FILTER_START_FROM, $filters) && $filters[self::FILTER_START_FROM]) {
                $matched = $startTime >= $filters[self::FILTER_START_FROM];
            }
            if ($matched && array_key_exists(self::FILTER_START_TO, $filters) && $filters[self::FILTER_START_TO]) {
                $matched = $startTime <= $filters[self::FILTER_START_TO];
            }
            if ($matched && array_key_exists(self::FILTER_END_FROM, $filters) && $filters[self::FILTER_END_FROM]) {
                $matched = $endTime >= $filters[self::FILTER_END_FROM];
            }
            if ($matched && array_key_exists(self::FILTER_END_TO, $filters) && $filters[self::FILTER_END_TO]) {
                $matched = $endTime <= $filters[self::FILTER_END_TO];
            }
        }
        return $matched;
    }

    /**
     * @param array $deliveryUris
     * @return int
     * @throws common_exception_Error
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
    public function getVariableColumns($delivery, $variableClassUri, array $filters = [], array $storageOptions = [])
    {
        $columns = [];
        /** @var ResultServiceWrapper $resultServiceWrapper */
        $resultServiceWrapper = $this->getServiceLocator()->get(ResultServiceWrapper::SERVICE_ID);

        $this->setImplementation($this->getReadableImplementation($delivery));
        //The list of delivery Results matching the current selection filters
        $resultsIds = $this->findResultsByDeliveryAndFilters($delivery, $filters, $storageOptions);

        //retrieveing all individual response variables referring to the  selected delivery results
        $itemIndex = $this->getItemIndexer($delivery);

        //retrieving The list of the variables identifiers per activities defintions as observed
        $variableTypes = [];

        $resultLanguage = $this->getResultLanguage();

        foreach (array_chunk($resultsIds, $resultServiceWrapper->getOption(ResultServiceWrapper::RESULT_COLUMNS_CHUNK_SIZE_OPTION)) as $resultsIdsItem) {
            $selectedVariables = $this->getResultsVariables($resultsIdsItem);
            foreach ($selectedVariables as $variable) {
                $variable = $variable[0];
                if ($this->isResultVariable($variable, $variableClassUri)) {
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

                    $variableTypes[$uri . $variableIdentifier] = ["contextLabel" => $contextIdentifierLabel, "contextId" => $uri, "variableIdentifier" => $variableIdentifier];
                }
            }
        }

        foreach ($variableTypes as $variableType) {
            switch ($variableClassUri) {
                case \taoResultServer_models_classes_OutcomeVariable::class:
                    $columns[] = new GradeColumn($variableType["contextId"], $variableType["contextLabel"], $variableType["variableIdentifier"]);
                    break;
                case \taoResultServer_models_classes_ResponseVariable::class:
                    $columns[] = new ResponseColumn($variableType["contextId"], $variableType["contextLabel"], $variableType["variableIdentifier"]);
                    break;
                default:
                    $columns[] = new ResponseColumn($variableType["contextId"], $variableType["contextLabel"], $variableType["variableIdentifier"]);
            }
        }
        $arr = [];
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
        $observationsList = array_map(function ($obs) {
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
    private function loadTestMetadata(tao_models_classes_service_StorageDirectory $directory, $testUri)
    {
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
        $runtime = $this->getServiceLocator()->get(RuntimeService::SERVICE_ID)->getRuntime($delivery);
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

    /**
     * @param core_kernel_classes_Resource $delivery
     * @param array $filters
     * @param array $storageOptions
     * @return array
     * @throws common_exception_Error
     */
    protected function findResultsByDeliveryAndFilters($delivery, array $filters = [], array $storageOptions = [])
    {
        $results = [];
        foreach ($this->getImplementation()->getResultByDelivery([$delivery->getUri()], $storageOptions) as $result) {
            $results[] = $result['deliveryResultIdentifier'];
        }

        return $results;
    }
}
