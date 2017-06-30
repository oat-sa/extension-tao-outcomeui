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
 * Copyright (c) 2013 Open Assessment Technologies S.A.
 *
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package taoOutcomeUi
 */

namespace oat\taoOutcomeUi\model;

use oat\taoOutcomeUi\helper\ResponseVariableFormatter;
use oat\taoOutcomeUi\model\table\GradeColumn;
use oat\taoOutcomeUi\model\table\ResponseColumn;
use \common_Exception;
use \common_Logger;
use \common_cache_FileCache;
use \common_exception_Error;
use \core_kernel_classes_Class;
use \core_kernel_classes_DbWrapper;
use \core_kernel_classes_Property;
use \core_kernel_classes_Resource;
use oat\taoResultServer\models\classes\ResultManagement;
use \tao_helpers_Date;
use \tao_models_classes_ClassService;
use oat\taoOutcomeUi\helper\Datatypes;
use oat\taoDelivery\model\execution\DeliveryExecution;
use oat\taoResultServer\models\classes\ResultServerService;

class ResultsService extends tao_models_classes_ClassService {

    /**
     *
     * @var \taoResultServer_models_classes_ReadableResultStorage
     */
    private $implementation = null;

    /**
     * (non-PHPdoc)
     * @see tao_models_classes_ClassService::getRootClass()
     */
    public function getRootClass() {
        return new core_kernel_classes_Class(TAO_DELIVERY_RESULT);
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
            foreach ($this->getItemResultsFromDeliveryResult($resultIdentifier) as $itemResult) {
                $itemResultVariables = $this->getVariablesFromObjectResult($itemResult);
                $variables[$itemResult] = $itemResultVariables;
            }
            foreach ($this->getTestsFromDeliveryResult($resultIdentifier) as $testResult) {
                $testResultVariables = $this->getVariablesFromObjectResult($testResult);
                $variables[$testResult] = $testResultVariables;
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
                $returnValue = array_merge($itemResultVariables, $returnValue);
            }
        } else {
            $returnValue = $variables;
        }
        

        return (array) $returnValue;
    }

    /**
     * @param  string $itemResult
     * @param array $wantedTypes
     * @return array
     */
    public function getVariablesFromObjectResult($itemResult, $wantedTypes = array(\taoResultServer_models_classes_ResponseVariable::class,\taoResultServer_models_classes_OutcomeVariable::class, \taoResultServer_models_classes_TraceVariable::class)) {
        $returnedVariables = array();
        $variables = $this->getImplementation()->getVariables($itemResult);
        if(!empty($wantedTypes)){
            foreach($variables as $variable){
                if(in_array(get_class($variable[0]->variable),$wantedTypes)){
                    $returnedVariables[] = $variable;
                }
            }
        }
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
     * @return \core_kernel_classes_Resource
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
        if(!is_null($tmpItems[0]->item)){
            $item = new core_kernel_classes_Resource($tmpItems[0]->item);
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
     * @param array $$variablesData
     * @param string $filter 'lastSubmitted', 'firstSubmitted'
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
     * @return array item information ['uri' => xxx, 'label' => yyy, 'itemModel' => zzz]
     */
    private function getItemInfos($itemCallId, $itemVariables){
        $undefinedStr = __('unknown'); //some data may have not been submitted

        try {
            common_Logger::d("Retrieving related Item for item call " . $itemCallId . "");
            $relatedItem = $this->getItemFromItemResult($itemCallId, $itemVariables);
        } catch (common_Exception $e) {
            common_Logger::w("The item call '" . $itemCallId . "' is not linked to a valid item. (deleted item ?)");
            $relatedItem = null;
        }
        if ($relatedItem instanceof \core_kernel_classes_Literal) {
            $itemIdentifier = $relatedItem->__toString();
            $itemLabel = $relatedItem->__toString();
            $itemModel = $undefinedStr;
        } elseif ($relatedItem instanceof core_kernel_classes_Resource) {
            $itemIdentifier = $relatedItem->getUri();
            $itemLabel = $relatedItem->getLabel();

            try {
                common_Logger::d("Retrieving related Item model for item " . $relatedItem->getUri() . "");
                $itemModel = $relatedItem->getUniquePropertyValue(new core_kernel_classes_Property(TAO_ITEM_MODEL_PROPERTY));
                $itemModel = $itemModel->getLabel();
            } catch (common_Exception $e) { //a resource but unknown
                $itemModel = $undefinedStr;
            }
        } else {
            $itemIdentifier = $undefinedStr;
            $itemLabel = $undefinedStr;
            $itemModel = $undefinedStr;
        }
        $item['itemModel'] = $itemModel;
        $item['label'] = $itemLabel;
        $item['uri'] = $itemIdentifier;

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
                'itemModel' => QTI,
                'label' => Example_0_Introduction,
                'uri' => http://tao.local/mytao.rdf#i1462952280695832,
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
     */
    public function getStructuredVariables($resultIdentifier, $filter, $wantedTypes = array())
    {
        $itemCallIds = $this->getItemResultsFromDeliveryResult($resultIdentifier);
        $variablesByItem = array();
        $savedItems = array();
        $itemVariables = array();
        $tmpitem = array();
        $item = array();

        foreach ($itemCallIds as $itemCallId) {
            $firstEpoch = null;
            $itemVariables = array_merge($itemVariables, $this->getVariablesFromObjectResult($itemCallId, $wantedTypes));
        }

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

        $lastItemCallId = null;

        foreach($itemVariables as $variable){
            $currentItemCallId = $variable[0]->callIdItem;

            /** @var \taoResultServer_models_classes_Variable $variableTemp */
            $variableTemp = $variable[0]->variable;
            $variableDescription = array();
            //retrieve the type of the variable
            $type = get_class($variableTemp);

            if(is_null($lastItemCallId)){
                $lastItemCallId = $currentItemCallId;
                $firstEpoch = $variableTemp->getEpoch();
                $item = $this->getItemInfos($currentItemCallId, array($variable));
            }

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


            if($currentItemCallId !== $lastItemCallId){
                //no yet saved
                //already saved and filter not first
                if(!isset($savedItems[$item['uri']]) || $filter !== "firstSubmitted"){
                    //last submitted and already something saved
                    if($filter === "lastSubmitted" && isset($savedItems[$item['uri']])){
                        //$tmpitem not empty and contains at least one wanted type
                        if(!empty($tmpitem)){
                            foreach($wantedTypes as $type){
                                if(isset($tmpitem[$type])){
                                    unset($variablesByItem[$savedItems[$item['uri']]]);
                                    $variablesByItem[$firstEpoch] = array_merge($item,$tmpitem);
                                    continue;
                                }
                            }
                        }
                    } else {
                        $variablesByItem[$firstEpoch] = array_merge($item,$tmpitem);
                    }
                    $savedItems[$item['uri']] = $firstEpoch;
                }
                $tmpitem = array();
                $firstEpoch = $variableTemp->getEpoch();
                $item = $this->getItemInfos($currentItemCallId, array($variable));
                $lastItemCallId = $currentItemCallId;
            }

            $tmpitem[$type][$variableIdentifier] = $variableDescription;
        }

        if(!empty($item) && (!isset($savedItems[$item['uri']]) || $filter !== "firstSubmitted")){
            //last submitted and already something saved
            if($filter === "lastSubmitted" && isset($savedItems[$item['uri']])){
                //$tmpitem not empty and contains at least one wanted type
                if(!empty($tmpitem)){
                    foreach($wantedTypes as $type){
                        if(isset($tmpitem[$type])){
                            unset($variablesByItem[$savedItems[$item['uri']]]);
                            $variablesByItem[$firstEpoch] = array_merge($item,$tmpitem);
                            break;
                        }
                    }
                }
            } else {
                $variablesByItem[$firstEpoch] = array_merge($item,$tmpitem);
            }
        }

        return $variablesByItem;
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

        $undefinedStr = __('unknown'); //some data may have not been submitted           

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
                        case "lastSubmitted": {
                                $variablesByItem[$itemIdentifier]['sortedVars'][$variableType][$variableIdentifier] = array(array_pop($variablesByItem[$itemIdentifier]['sortedVars'][$variableType][$variableIdentifier]));
                                break;
                            }
                        case "firstSubmitted": {
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
     * @return \core_kernel_classes_Resource
     */
    public function getTestTaker($resultIdentifier) {
        $testTaker = $this->getImplementation()->getTestTaker($resultIdentifier);
        return new core_kernel_classes_Resource($testTaker);
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
        } else {
            $propValues = $testTaker->getPropertiesValues(array(
                RDFS_LABEL,
                PROPERTY_USER_LOGIN,
                PROPERTY_USER_FIRSTNAME,
                PROPERTY_USER_LASTNAME,
                PROPERTY_USER_MAIL,
            ));
        }
        return $propValues;
    }

    /**
     *
     * @param \core_kernel_classes_Resource $delivery
     * @return \taoResultServer_models_classes_ReadableResultStorage
     * @throws \core_kernel_persistence_Exception
     * @throws common_exception_Error
     */
    public function getReadableImplementation(\core_kernel_classes_Resource $delivery) {
        return $this->getServiceManager()->get(ResultServerService::SERVICE_ID)->getResultStorage($delivery);
    }

    /**
     * @param core_kernel_classes_Resource $delivery
     * @param $columns - columns to be exported
     * @param $filter 'lastSubmitted' or 'firstSubmitted'
     * @return array
     * @throws \common_exception_Error
     * @throws \core_kernel_persistence_Exception
     */
    public function getResultsByDelivery(\core_kernel_classes_Resource $delivery, $columns, $filter)
    {
        $rows = array();

        //The list of delivery Results matching the current selection filters
        $results = array();
        $this->setImplementation($this->getReadableImplementation($delivery));
        foreach($this->getImplementation()->getResultByDelivery([$delivery->getUri()]) as $result){
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
            foreach ($columns as $column) {
                if (count($column->getDataProvider()->cache) > 0) {
                    $cellData[]=self::filterCellData($column->getDataProvider()->getValue(new core_kernel_classes_Resource($result), $column), $filter);
                } else {
                    $cellData[]=[self::filterCellData(
                        (string)$this->getTestTaker($result)->getOnePropertyValue(new \core_kernel_classes_Property(PROPERTY_USER_LOGIN)),
                        $filter)];
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
     * Retrieve the different variables columns pertainign to the current selection of results
     * Implementation note : it nalyses all the data collected to identify the different response variables submitted by the items in the context of activities
     */
    public function getVariableColumns($delivery, $variableClassUri, $filter)
    {
        $columns = array();

        $this->setImplementation($this->getReadableImplementation($delivery));
        //The list of delivery Results matching the current selection filters
        $results = $this->getImplementation()->getResultByDelivery([$delivery->getUri()]);

        //retrieveing all individual response variables referring to the  selected delivery results
        $selectedVariables = array ();
        foreach ($results as $result){
            $variables = $this->getVariables($result["deliveryResultIdentifier"]);
            $selectedVariables = array_merge($selectedVariables, $variables);
        }
        //retrieving The list of the variables identifiers per activities defintions as observed
        $variableTypes = array();
        foreach ($selectedVariables as $variable) {
            if((!is_null($variable[0]->item) ||  !is_null($variable[0]->test))&& (get_class($variable[0]->variable) == 'taoResultServer_models_classes_OutcomeVariable' && $variableClassUri == CLASS_OUTCOME_VARIABLE)
                || (get_class($variable[0]->variable) == 'taoResultServer_models_classes_ResponseVariable' && $variableClassUri == CLASS_RESPONSE_VARIABLE)){
                //variableIdentifier
                $variableIdentifier = $variable[0]->variable->identifier;
                $uri = (!is_null($variable[0]->item))? $variable[0]->item : $variable[0]->test;
                $object = new core_kernel_classes_Resource($uri);
                if (get_class($object) == "core_kernel_classes_Resource") {
                    $contextIdentifierLabel = $object->getLabel();
                    $contextIdentifier = $object->getUri(); // use the callId/itemResult identifier
                }
                else {
                    $contextIdentifierLabel = $object->__toString();
                    $contextIdentifier = $object->__toString();
                }
                $variableTypes[$contextIdentifier.$variableIdentifier] = array("contextLabel" => $contextIdentifierLabel, "contextId" => $contextIdentifier, "variableIdentifier" => $variableIdentifier);
            }
        }
        foreach ($variableTypes as $variable){

            switch ($variableClassUri){
                case \taoResultServer_models_classes_OutcomeVariable::class :
                    $columns[] = new GradeColumn($variable["contextId"], $variable["contextLabel"], $variable["variableIdentifier"]);
                    break;
                case \taoResultServer_models_classes_ResponseVariable::class :
                    $columns[] = new ResponseColumn($variable["contextId"], $variable["contextLabel"], $variable["variableIdentifier"]);
                    break;
                default:
                    $columns[] = new ResponseColumn($variable["contextId"], $variable["contextLabel"], $variable["variableIdentifier"]);
            }
        }
        $arr = array();
        foreach ($columns as $column) {
            $arr[] = $column->toArray();
        }
        return $arr;
    }

    /**
     * @param $observationsList
     * @param $filterData
     * @return array|string
     */
    public static function filterCellData($observationsList, $filterData){
        //if the cell content is not an array with multiple entries, do not filter
        if (!(is_array($observationsList))){
            return $observationsList;
        }
        //takes only the alst or the first observation
        if (
            ($filterData=="lastSubmitted" or $filterData=="firstSubmitted")
            and
            (is_array($observationsList))
        ){
            $returnValue = array();

            //sort by timestamp observation
            uksort($observationsList, "oat\\taoOutcomeUi\\model\\ResultsService::sortTimeStamps");
            $filteredObservation = ($filterData=='lastSubmitted') ? array_pop($observationsList) : array_shift($observationsList);
            $returnValue[]= $filteredObservation[0];

        } else {
            $cellData = '';
            foreach ($observationsList as $observation) {
                $cellData.= $observation[0].$observation[1].PHP_EOL;
            }
            $returnValue = [$cellData];
        }
        return $returnValue;
    }

}
