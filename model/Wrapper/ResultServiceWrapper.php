<?php
/**
 * Created by PhpStorm.
 * User: christophe
 * Date: 20/06/17
 * Time: 15:11
 */

namespace oat\taoOutcomeUi\model\Wrapper;


use oat\oatbox\service\ConfigurableService;
use oat\oatbox\service\ServiceManager;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionState;
use oat\taoOutcomeUi\model\ResultsService;

class ResultServiceWrapper extends ConfigurableService
{

    const SERVICE_ID  = 'taoOutcomeUi/resultService';

    const RESULT_COLUMNS_CHUNK_SIZE_OPTION = 'resultColumnsChunkSize';
    /**
     * @var ResultsService
     */
    protected $resultService;

    public function getService() {
        if(is_null($this->resultService)) {
            $serviceClass = $this->getOption('class');
            $this->resultService = $serviceClass::singleton();
        }
        return $this->resultService;
    }

    public static function deleteResultCache(DeliveryExecutionState $event)
    {
        // if the delivery execution has been re-activated, we have to delete the result cache already existing for this execution
        if ($event->getPreviousState() == DeliveryExecutionInterface::STATE_FINISHIED ) {
            /** @var ResultsService $resultService */
            $resultService = ServiceManager::getServiceManager()->get(self::SERVICE_ID)->getService();
            $resultService->deleteCacheFor($event->getDeliveryExecution()->getIdentifier());
        }
    }
}