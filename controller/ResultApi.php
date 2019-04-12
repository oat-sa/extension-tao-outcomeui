<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 4/12/19
 * Time: 10:11 AM
 */

namespace oat\taoOutcomeUi\controller;


use oat\tao\helpers\Template;
use oat\taoDelivery\model\execution\DeliveryExecution;
use oat\taoDelivery\model\execution\ServiceProxy;
use oat\taoOutcomeUi\model\ResultsService;
use Renderer;

class ResultApi extends \tao_actions_RestController
{
    public function state()
    {
        $test = null;
        try{
            if (!$this->isRequestGet()) {
                throw new \common_exception_BadRequest(sprintf('Bad Request Method: %s.', $this->getRequestMethod()));
            }

            if(!$this->hasRequestParameter('deliveryExecution')){
                throw  new \common_exception_MissingParameter('Missing required parameter');
            }
            $deliveryExecution = ServiceProxy::singleton()->getDeliveryExecution($this->getRequestParameter('deliveryExecution'));

            if (!$deliveryExecution->exists()) {
                throw new \common_exception_NotFound('Delivery Execution not found');
            }
            $this->returnJson($this->getStateReport($deliveryExecution));

        }catch (\common_exception_MissingParameter $e){
            $this->returnJson($this->generateErrorResponse(3, $e->getMessage()));
        }catch (\common_exception_BadRequest $e) {
            $this->returnJson($this->generateErrorResponse(2, $e->getMessage()));
        }catch (\common_exception_NotFound $e){
            $this->returnJson($this->generateErrorResponse(5, $e->getMessage()));
        }catch (\Exception $e) {
            $this->returnJson($this->generateErrorResponse('', $e->getMessage()));
        }
    }

    protected function getStateReport($deliveryExecution)
    {
        $state =  $deliveryExecution->getState();
        $resultService = ResultsService::singleton();
        $scores =$resultService->getScores($deliveryExecution->getIdentifier());
        $scoreReport = null;
        if ($state->getUri() === DeliveryExecution::STATE_FINISHED) {
            $renderer = new Renderer();
            $template = Template::getTemplate('stateReport.tpl', 'taoOutcomeUi');
            $renderer->setData('scores', $scores);
            $renderer->setTemplate($template);
            $scoreReport = $renderer->render();
        }

        return [
            'success' => true,
            'state' => $state->getUri(),
            'scoreReport' => $scoreReport,
            'scores' => $scores,
        ];
    }

    protected function generateErrorResponse( $errorCode='', $errorMsg){
        return [
            'success'=>false,
            'errorCode' => $errorCode,
            'errorMsg'=>$errorMsg
        ];
    }
}

