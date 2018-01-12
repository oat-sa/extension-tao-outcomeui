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
 * Copyright (c) 2009-2012 (original work) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *               2012-2017 Open Assessment Technologies SA;
 *
 */

namespace oat\taoOutcomeUi\controller;

use \common_Exception;
use oat\tao\helpers\UserHelper;
use oat\tao\model\accessControl\AclProxy;
use oat\taoDelivery\model\execution\ServiceProxy;
use oat\taoOutcomeUi\model\export\ColumnsProvider;
use oat\generis\model\OntologyAwareTrait;
use oat\taoOutcomeUi\model\export\ResultsExporter;
use oat\taoOutcomeUi\model\ResultsService;
use oat\taoOutcomeUi\model\table\ResultsPayload;
use oat\taoOutcomeUi\model\Wrapper\ResultServiceWrapper;
use oat\taoResultServer\models\classes\ResultServerService;
use oat\taoTaskQueue\model\TaskLogActionTrait;
use tao_helpers_Uri;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;

class ToolsResults extends \tao_actions_SaSModule
{
    const PARAMETER_COLUMNS = 'columns';
    const PARAMETER_DELIVERY_URI = 'uri';
    const PARAMETER_FILTER = 'filter';

    use OntologyAwareTrait;
    use TaskLogActionTrait;

    /**
     * constructor: initialize the service and the default data
     * @return Results
     */
    public function __construct()
    {
        parent::__construct();

        $this->service = $this->getServiceManager()->get(ResultServiceWrapper::SERVICE_ID)->getService();
        $this->deliveryService = DeliveryAssemblyService::singleton();
        $this->defaultData();
    }
    /**
     * Return the Result Table entry page displaying the datatable and the filters to be applied.
     *
     * @throws \tao_models_classes_MissingRequestParameterException
     */
    public function index()
    {
        if ($this->hasRequestParameter('id')) {
            $delivery = new \core_kernel_classes_Resource(\tao_helpers_Uri::decode($this->getRequestParameter('id')));
            $this->setData('delivery_id', $delivery->getUri());
        }

        $this->setView('toolsResults.tpl');
    }

    public function getResults()
    {
        $page = $this->getRequestParameter('page');
        $limit = $this->getRequestParameter('rows');
        $order = $this->getRequestParameter('sortby');
        $sort = $this->getRequestParameter('sortorder');
        $start = $limit * $page - $limit;

        $gau = array(
            'order' => $order,
            'orderdir' => strtoupper($sort),
            'offset' => $start,
            'limit' => $limit,
            'recursive' => true
        );

        $delivery = new \core_kernel_classes_Resource('https://act.local/tao.rdf#i151394604861931209');

        try {
            $this->getResultStorage($delivery);

            $data = array();
            $readOnly = array();
            $user = \common_session_SessionManager::getSession()->getUser();
            $rights = array(
                'view' => !AclProxy::hasAccess($user, 'oat\taoOutcomeUi\controller\Results', 'viewResult', array()),
                'delete' => !AclProxy::hasAccess($user, 'oat\taoOutcomeUi\controller\Results', 'delete', array()));
            $results = $this->getClassService()->getImplementation()->getResultByDelivery(array($delivery->getUri()), $gau);
            $count = $this->getClassService()->getImplementation()->countResultByDelivery(array($delivery->getUri()));
            foreach ($results as $res) {

                $deliveryExecution = ServiceProxy::singleton()->getDeliveryExecution($res['deliveryResultIdentifier']);

                try {
                    $startTime = \tao_helpers_Date::displayeDate($deliveryExecution->getStartTime());
                } catch (\common_exception_NotFound $e) {
                    \common_Logger::w($e->getMessage());
                    $startTime = '';
                }

                $user = UserHelper::getUser($res['testTakerIdentifier']);
                $userName = UserHelper::getUserName($user, true);
                if (empty($userName)) {
                    $userName = $res['testTakerIdentifier'];
                }

                $data[] = array(
                    'id' => $deliveryExecution->getIdentifier(),
                    'delivery_uri' => $delivery->getUri(),
                    'delivery_label' => $delivery->getLabel(),
                    'ttaker' => _dh($userName),
                    'time' => $startTime,
                );

                $readOnly[$deliveryExecution->getIdentifier()] = $rights;
            }

            $this->returnJson(array(
                'data' => $data,
                'page' => floor($start / $limit) + 1,
                'total' => ceil($count / $limit),
                'records' => count($data),
                'readonly' => $readOnly
            ));
        } catch (\common_exception_Error $e) {
            $this->returnJson(array(
                'error' => $e->getMessage()
            ));
        }
    }

    /**
     * Returns the currently configured result storage
     *
     * @param \core_kernel_classes_Resource $delivery
     * @return \taoResultServer_models_classes_ReadableResultStorage
     */
    protected function getResultStorage($delivery)
    {
        $resultServerService = $this->getServiceManager()->get(ResultServerService::SERVICE_ID);
        $resultStorage = $resultServerService->getResultStorage($delivery->getUri());
        $this->getClassService()->setImplementation($resultStorage);
        return $resultStorage;
    }

    /**
     * Feeds js datatable component with the values to be exported.
     *
     * @throws common_Exception
     */
    public function feedDataTable()
    {
        if (!\tao_helpers_Request::isAjax()) {
            throw new \Exception('Only ajax call allowed.');
        }

        if (!$this->hasRequestParameter(self::PARAMETER_COLUMNS)) {
            throw new common_Exception('Parameter "'. self::PARAMETER_COLUMNS .'" missing');
        }

        $this->returnJSON((new ResultsPayload($this->getExporterService()->getExporter()))->getPayload());
    }

    /**
     * Exports results by a single delivery.
     *
     * Only creating the export task.
     *
     * @throws \Exception
     */
    public function export()
    {
        if (!\tao_helpers_Request::isAjax()) {
            throw new \Exception('Only ajax call allowed.');
        }

        return $this->returnTaskJson($this->getExporterService()->createExportTask());
    }

    /**
     * Returns test taker metadata columns.
     *
     * @throws \Exception
     */
    public function getTestTakerColumns()
    {
        if (!\tao_helpers_Request::isAjax()) {
            throw new \Exception('Only ajax call allowed.');
        }

        return $this->returnJson([
            'columns' => $this->getColumnsProvider()->getTestTakerColumns(),
            'first'   => true
        ]);
    }

    /**
     * Returns delivery metadata columns.
     *
     * @throws \Exception
     */
    public function getDeliveryColumns()
    {
        if (!\tao_helpers_Request::isAjax()) {
            throw new \Exception('Only ajax call allowed.');
        }

        return $this->returnJson([
            'columns' => $this->getColumnsProvider()->getDeliveryColumns()
        ]);
    }

    /**
     * Returns grade columns.
     *
     * @throws \Exception
     */
    public function getGradeColumns()
    {
        if (!\tao_helpers_Request::isAjax()) {
            throw new \Exception('Only ajax call allowed.');
        }

        return $this->returnJson([
            'columns' => $this->getColumnsProvider()->getGradeColumns()
        ]);
    }

    /**
     * Returns response columns.
     *
     * @throws \Exception
     */
    public function getResponseColumns()
    {
        if (!\tao_helpers_Request::isAjax()) {
            throw new \Exception('Only ajax call allowed.');
        }

        return $this->returnJson([
            'columns' => $this->getColumnsProvider()->getResponseColumns()
        ]);
    }

    /**
     * @return ColumnsProvider
     */
    private function getColumnsProvider()
    {
        return new ColumnsProvider($this->getDeliveryUri(), ResultsService::singleton());
    }

    /**
     * @return ResultsExporter
     * @throws common_Exception
     */
    private function getExporterService()
    {
        /** @var ResultsExporter $exporter */
        $exporter = $this->getServiceManager()
            ->propagate(new ResultsExporter($this->getDeliveryUri(), ResultsService::singleton()));

        if ($this->hasRequestParameter(self::PARAMETER_COLUMNS)) {
            $exporter->setColumnsToExport($this->getRawParameter(self::PARAMETER_COLUMNS));
        }

        if ($this->hasRequestParameter(self::PARAMETER_FILTER)) {
            $exporter->setVariableToExport($this->getRequestParameter(self::PARAMETER_FILTER));
        }

        return $exporter;
    }

    /**
     * @return string
     * @throws common_Exception
     */
    private function getDeliveryUri()
    {
        if (!$this->hasRequestParameter(self::PARAMETER_DELIVERY_URI)) {
            throw new common_Exception('Parameter "'. self::PARAMETER_DELIVERY_URI .'" missing');
        }

        return \tao_helpers_Uri::decode($this->getRequestParameter(self::PARAMETER_DELIVERY_URI));
    }
}
