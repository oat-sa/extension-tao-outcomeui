<?php

namespace oat\taoOutcomeUi\model\table;

use common_Logger;
use core_kernel_classes_Resource;
use oat\taoOutcomeUi\model\ResultsService;
use tao_helpers_Date;
use tao_models_classes_table_Column;
use tao_models_classes_table_DataProvider;

class TraceVariableDataProvider implements tao_models_classes_table_DataProvider
{
    public const PROP_TRACE_VARIABLE = 'trace_variable';

    /** @var array */
    public $cache;

    public function prepare($resources, $columns)
    {
        $this->cache = [];
        $resultsService = ResultsService::singleton();

        foreach ($resources as $result) {
            $itemResults = $resultsService->getVariables($result, false);

            foreach ($itemResults as $itemResultUri => $vars) {
                foreach ($vars as $var) {
                    // cache the variable data
                    /** @var \taoResultServer_models_classes_Variable $varData */
                    $varData = $var[0]->variable;

                    if (
                        !$varData instanceof \taoResultServer_models_classes_TraceVariable
                        || $var[0]->callIdItem !== null
                    ) {
                        continue;
                    }

                    $epoch = $varData->getEpoch();
                    $trace = $varData->getTrace();
                    $identifier = $varData->getIdentifier();

                    $readableTime = "";
                    if ($epoch != "") {
                        $displayDate = tao_helpers_Date::displayeDate(
                            tao_helpers_Date::getTimeStamp($epoch),
                            tao_helpers_Date::FORMAT_VERBOSE
                        );
                        $readableTime = "@" . $displayDate;
                    }

                    $this->cache[$result][(string) $epoch] = [
                        $trace,
                        $identifier,
                        $readableTime
                    ];
                }
            }
        }
    }

    public function getValue(core_kernel_classes_Resource $resource, tao_models_classes_table_Column $column)
    {
        $returnValue = [];

        if (isset($this->cache[$resource->getUri()])) {
            $returnValue = $this->cache[$resource->getUri()];
        } else {
            common_Logger::d('no data for resource: ' . $resource->getUri() . ' column: ' . $column->getIdentifier());
        }

        return $returnValue;
    }

    public function getCache()
    {
        return $this->cache;
    }
}
