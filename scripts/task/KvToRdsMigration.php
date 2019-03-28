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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 *
 */


namespace oat\taoOutcomeUi\scripts\task;

use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\extension\script\ScriptAction;
use oat\taoDelivery\model\execution\ServiceProxy;
use oat\taoOutcomeRds\model\RdsResultStorage;
use oat\taoResultServer\models\classes\implementation\ResultServerService;

class KvToRdsMigration extends ScriptAction
{
    use OntologyAwareTrait;

    /** @var RdsResultStorage $kvStorageService */
    protected $rdsStorage;

    /** @var \taoResultServer_models_classes_ReadableResultStorage $kvStorageService */
    protected $kvStorage;

    /** @var array  */
    protected $deliveryExecutions = [];

    /** @var \common_report_Report */
    protected $report;

    /**
     * Provide a "force" flag to apply the migration
     *
     * @return array
     */
    protected function provideOptions()
    {
        return [
            'force' => [
                'prefix' => 'f',
                'longPrefix' => 'force',
                'required' => false,
                'default' => true,
                'flag' => true,
                'description' => 'Apply the migration to Result storage.',
            ]
        ];
    }

    /**
     * Run the command.
     *
     * Get current result storage as key value storage
     * Retrieve result $callIds and migrate deliveryExecution
     * Migrate the result storage to rdsStorage
     *
     * @return \common_report_Report The report of the migration
     * @throws \common_Exception
     */
    protected function run()
    {
        $this->kvStorage = $this->getCurrentKvResultStorage();

        $this->rdsStorage = $this->getServiceLocator()->get(RdsResultStorage::SERVICE_ID);

        $this->report = \common_report_Report::createInfo('Starting migration...');

        foreach ($this->kvStorage->getAllCallIds() as $callId) {
            foreach ($this->kvStorage->getVariables($callId) as $variables) {
                $this->migrateDeliveryExecution($callId, $variables);
            }
        }

        $this->setCurrentResultStorageToRdsStorage();

        $this->report->add(\common_report_Report::createSuccess(count($this->deliveryExecutions) . ' delivery executions migrated.'));
        if ($this->isDryrun()) {
            $this->report->add(\common_report_Report::createFailure(
                'The migration has not been applied because of dryrun mode. Use --force to really run the migration'
            ));
        }
        return $this->report;
    }

    /**
     * Migrate delivery execution variables
     *
     * Register the delivery execution if does not exist
     * - If not then register related delivery & testtaker
     * Migrate delivery execution variables
     *
     * @param $callId
     * @param array $variables
     * @throws \common_exception_Error
     */
    protected function migrateDeliveryExecution($callId, array $variables)
    {
        $deliveryExecutionIdentifier = $variables[0]->deliveryResultIdentifier;

        if (!in_array($deliveryExecutionIdentifier, $this->deliveryExecutions)) {
            if (!$this->isDryrun()) {
                $this->rdsStorage->storeRelatedDelivery(
                    $deliveryExecutionIdentifier,
                    $this->kvStorage->getDelivery($deliveryExecutionIdentifier)
                );
                $this->rdsStorage->storeRelatedTestTaker(
                    $deliveryExecutionIdentifier,
                    $this->kvStorage->getTestTaker($deliveryExecutionIdentifier)
                );
            }
            $this->deliveryExecutions[] = $deliveryExecutionIdentifier;
        }

        $this->report->add($this->migrateDeliveryExecutionVariables($callId, $deliveryExecutionIdentifier, $variables));
    }

    /**
     * Migrate delivery execution variables
     *
     * If a variable already exists with same epoch then skip
     * Otherwise register variable (item or test variable)
     *
     * @param $callId
     * @param $deliveryExecutionIdentifier
     * @param array $variables
     * @return \common_report_Report
     */
    protected function migrateDeliveryExecutionVariables($callId, $deliveryExecutionIdentifier, array $variables)
    {
        $count = 0;
        $identifier = null;

        foreach ($variables as $variable) {
            $identifier = $variable->variable->identifier;

            foreach ($this->rdsStorage->getVariable($callId, $variable->variable->identifier) as $existingVariable) {
                if ($variable->variable->epoch == $existingVariable->variable->epoch) {
                    continue 2;
                }
            }
            if (!$this->isDryrun()) {
                if (isset($variable->callIdItem)) {
                    $this->rdsStorage->storeItemVariable(
                        $deliveryExecutionIdentifier,
                        $variable->test,
                        $variable->item,
                        $variable->variable,
                        $variable->callIdItem
                    );
                } else {
                    $this->rdsStorage->storeTestVariable(
                        $deliveryExecutionIdentifier,
                        $variable->test,
                        $variable->variable,
                        $variable->callIdTest
                    );
                }
            }
            $count++;
        }

        if ($count == 0) {
            $message = 'Already migrated.';
        } else {
            $message =  $count . ' variables migrated';
        }

        return \common_report_Report::createInfo('Migrating ' . $callId . ' : ' . $identifier . ' : ' . $message);
    }

    /**
     * Get the configured service to deal with result storage
     *
     * @return \taoResultServer_models_classes_WritableResultStorage
     * @throws \common_exception If the service is not a key value interface
     */
    protected function getCurrentKvResultStorage()
    {
        /** @var ResultServerService $resultService */
        $resultService = $this->getServiceLocator()->get(ResultServerService::SERVICE_ID);
        $resultStorageKey = $resultService->getOption(ResultServerService::OPTION_RESULT_STORAGE);
        if ($resultStorageKey != 'taoAltResultStorage/KeyValueResultStorage') {
            throw new \common_Exception('Result storage is not on KeyValue storage mode.');
        }
        return $resultService->instantiateResultStorage($resultStorageKey);
    }

    /**
     * Register rdsStorage as default result storage
     *
     * @throws \common_Exception
     */
    protected function setCurrentResultStorageToRdsStorage()
    {
        if ($this->isDryrun()) {
            return;
        }
        /** @var ResultServerService $resultService */
        $resultService = $this->getServiceLocator()->get(ResultServerService::SERVICE_ID);
        $resultService->setOption(ResultServerService::OPTION_RESULT_STORAGE, RdsResultStorage::SERVICE_ID);
        $this->registerService(ResultServerService::SERVICE_ID, $resultService);
    }

    /**
     * Get the delivery execution service
     *
     * @return ServiceProxy
     */
    protected function getDeliveryExecutionService()
    {
        return $this->getServiceLocator()->get(ServiceProxy::SERVICE_ID);
    }

    /**
     * Display help
     *
     * @return array
     */
    protected function provideUsage()
    {
        return [
            'prefix' => 'h',
            'longPrefix' => 'help',
            'description' => 'Prints a help statement'
        ];
    }

    /**
     * Provides script description
     *
     * @return string
     */
    protected function provideDescription()
    {
        return __('A script to migrate result from KeyValue storage to RDS.' . PHP_EOL .
            ' It copies data from KeyValue to RdsStorage and switch config from taoResultServer/resultservice.conf.php.');
    }

    /**
     * Check if it is the dry run mode
     *
     * @return mixed
     */
    protected function isDryrun()
    {
        return !$this->getOption('force');
    }

    /**
     * Display execution time
     *
     * @return bool
     */
    protected function showTime()
    {
        return true;
    }
    
}