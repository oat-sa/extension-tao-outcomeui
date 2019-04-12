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

namespace oat\taoOutcomeUi\controller;


use oat\tao\helpers\Template;
use oat\taoDelivery\model\execution\DeliveryExecution;
use oat\taoDelivery\model\execution\ServiceProxy;
use oat\taoOutcomeUi\model\ResultsService;
use Renderer;

class ResultsApi extends \tao_actions_RestController
{
    public function state()
    {
        try{
            if (!$this->isRequestGet()) {
                throw new \common_exception_BadRequest(sprintf('Bad Request Method: %s.', $this->getRequestMethod()));
            }

            if(!$this->hasGetParameter('deliveryExecution')){
                throw  new \common_exception_MissingParameter('Missing required parameter');
           }
            $deliveryExecution = ServiceProxy::singleton()->getDeliveryExecution($this->getGetParameter('deliveryExecution'));
            if (!$deliveryExecution->exists()) {
                throw new \common_exception_NotFound('Delivery Execution not found');
            }
            $this->returnJson($this->getStateReport($deliveryExecution));

        }catch (\common_exception_MissingParameter $e){
            $this->returnJson($this->generateErrorResponse(3, $e->getMessage()));
        }catch (\common_exception_BadRequest $e) {
            $this->returnJson($this->generateErrorResponse(2, $e->getMessage()));
        }catch (\common_exception_NotFound $e){
            $this->returnJson($this->generateErrorResponse(4, $e->getMessage()));
        }catch (\Exception $e) {
            $this->returnJson($this->generateErrorResponse(5, $e->getMessage()));
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

