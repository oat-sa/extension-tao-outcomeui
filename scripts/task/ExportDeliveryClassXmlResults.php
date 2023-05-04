<?php

namespace oat\taoOutcomeUi\scripts\task;

use common_report_Report as Report;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\extension\script\ScriptAction;
use oat\taoDelivery\model\execution\ServiceProxy;
use oat\taoOutcomeUi\model\ResultsService;
use oat\taoResultServer\models\classes\QtiResultsService;

/**
 * Run example:
 *
 * http://www.tao.lu/Ontologies/TAODelivery.rdf\#AssembledDelivery
 * sudo -u www-data php index.php 'oat\taoOutcomeUi\scripts\task\ExportDeliveryClassXmlResults' -u {deliveryClassUri}
 *
 */
class ExportDeliveryClassXmlResults extends ScriptAction
{
    use OntologyAwareTrait;

    private const EXECUTION_ITERATIONS_TO_REINSTANTIATE_ZIP_OBJECT = 100;

    /**
     * @var Report
     */
    private $report;

    protected function provideOptions()
    {
        return [
            'deliveryClassUri' => [
                'prefix' => 'u',
                'longPrefix' => 'uri',
                'required' => true,
                'description' => 'Delivery Class URI.'
            ],
            'zipFilenamePrefix' => [
                'prefix' => 'p',
                'longPrefix' => 'filename_prefix',
                'required' => false,
                'defaultValue' => 'xml_results',
                'description' => 'Prefix for ZIP file name'
            ],
        ];
    }

    protected function provideDescription()
    {
        return 'The script will generate a zip with xml results';
    }

    protected function provideUsage()
    {
        return [
            'prefix' => 'h',
            'longPrefix' => 'help',
        ];
    }

    protected function run()
    {
        $this->report = Report::createInfo('Export started');

        $class = $this->getOption('deliveryClassUri');
        $zipFilenamePrefix = $this->getOption('zipFilenamePrefix');
        $deliveryClass = $this->getClass($class);
        $resultCount = 0;

        try {
            $fileName = \tao_helpers_Display::textCleaner($zipFilenamePrefix) . '-' . time() . '.zip';
            $path = \tao_helpers_File::concat([\tao_helpers_Export::getExportPath(), $fileName]);

            if (!\tao_helpers_File::securityCheck($path, true)) {
                throw new \Exception('Unauthorized file name');
            }
            if (file_exists($path)) {
                unlink($path);
            }
            $zipArchive = $this->instantiateZip($path, true);

            $deliveryNumber = 0;
            $executionAcrossDeliveriesNumber = 0;
            foreach ($deliveryClass->getInstances(true) as $delivery) {
                $deliveryNumber++;
                $this->report->add(
                    Report::createInfo(
                        sprintf(
                            'Starting to process delivery "%s"',
                            $delivery->getLabel()
                        )
                    )
                );

                $executions = $this->getResultsService()->getResultsByDelivery($delivery);

                $this->report->add(
                    Report::createInfo(
                        sprintf(
                            '%d results were found for delivery "%s"',
                            count($executions),
                            $delivery->getLabel()
                        )
                    )
                );

                $executionNumber = 0;
                foreach ($executions as $execution) {
                    $executionNumber++;
                    $executionAcrossDeliveriesNumber++;

                    // flush the archive by creating the object ZipArchive from scratch every 100 iterations to
                    // prevent ZipArchive from memory leaking (it builds up all the changes in memory that are not
                    // 'committed')
                    if (
                        $executionAcrossDeliveriesNumber % self::EXECUTION_ITERATIONS_TO_REINSTANTIATE_ZIP_OBJECT === 0
                    ) {
                        $zipArchive->close();
                        $zipArchive = $this->instantiateZip($path);
                    }
                    $xml = $this->getQtiResultsService()->getQtiResultXml($delivery->getUri(), $execution);
                    $executionObj = $this->getServiceProxy()->getDeliveryExecution($execution);

                    $userId = $executionObj->getUserIdentifier();
                    $xmlfileName = \common_Utils::isUri($userId) ? explode('#', $userId)[1] : $userId;
                    $xmlfileName .= '.xml';

                    $zipArchive->addFromString('results/' . $delivery->getLabel() . '/' . $xmlfileName, $xml);
                    $resultCount++;
                }
            }
        } catch (\Throwable $e) {
            if (isset($execution)) {
                $this->report->add(
                    Report::createFailure(
                        sprintf(
                            'Exception thrown while processing execution "%s": %s',
                            $execution,
                            $e->getMessage()
                        )
                    )
                );
            } else {
                $this->report->add(Report::createFailure($e->getMessage()));
            }
        }
        $this->report->add(
            Report::createSuccess(
                sprintf(
                    'Done! %d results for %d deliveries were exported. Please download the ZIP file at this path: %s',
                    $resultCount,
                    $deliveryNumber,
                    $path
                )
            )
        );

        return $this->report;
    }

    /**
     * @throws \Exception
     */
    private function instantiateZip($path, $create = false)
    {
        $zipArchive = new \ZipArchive();

        $zipOpenResult = $create ? $zipArchive->open($path, \ZipArchive::CREATE) : $zipArchive->open($path);

        if ($zipOpenResult !== true) {
            throw new \Exception(sprintf('Unable to create archive at path %s (error code %s)', $path, $zipOpenResult));
        }
        return $zipArchive;
    }

    /**
     * Get the temp export directory, before the final transfer
     *
     * @return string
     */
    protected function getTempExportDir()
    {
        return \tao_helpers_Export::getExportPath();
    }

    /**
     * @return ServiceProxy
     */
    protected function getServiceProxy()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(ServiceProxy::SERVICE_ID);
    }

    /**
     * @return QtiResultsService
     */
    protected function getQtiResultsService(): QtiResultsService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(QtiResultsService::SERVICE_ID);
    }

    /**
     * @return ResultsService
     */
    protected function getResultsService(): ResultsService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(ResultsService::SERVICE_ID);
    }
}
