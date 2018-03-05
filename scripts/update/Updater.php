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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA;
 *
 *
 */

namespace oat\taoOutcomeUi\scripts\update;

use oat\generis\model\data\ModelManager;
use oat\oatbox\event\EventManager;
use oat\oatbox\service\ConfigurableService;
use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionCreated;
use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionState;
use oat\taoOutcomeUi\model\ResultsService;
use oat\taoOutcomeUi\model\search\ResultCustomFieldsService;
use oat\taoOutcomeUi\model\search\ResultsWatcher;
use oat\taoOutcomeUi\model\Wrapper\ResultServiceWrapper;
use oat\taoOutcomeUi\scripts\install\RegisterTestPluginService;
use oat\taoOutcomeUi\scripts\task\ExportDeliveryResults;
use oat\taoTaskQueue\model\TaskLogInterface;

/**
 *
 * @author Joel Bout <joel@taotesting.com>
 */
class Updater extends \common_ext_ExtensionUpdater
{

    /**
     *
     * @param string $initialVersion
     * @return string $versionUpdatedTo
     */
    public function update($initialVersion)
    {
        // move ResultsManagerRole to model 1
        if ($this->isVersion('2.6')) {
            $rdf = ModelManager::getModel()->getRdfInterface();
            $toChange = array();
            foreach ($rdf as $triple) {
                if ($triple->subject == 'http://www.tao.lu/Ontologies/TAOResult.rdf#ResultsManagerRole') {
                    $toChange[] = $triple;
                }
            }
            foreach ($toChange as $triple) {
                $rdf->remove($triple);
                $triple->modelid = 1;
                $rdf->add($triple);
            }
            $this->setVersion('2.6.1');
        }

        $this->skip('2.6.1', '4.3.1');

        if ($this->isVersion('4.3.1')) {
            $this->runExtensionScript(RegisterTestPluginService::class);

            $this->setVersion('4.4.0');
        }

        if ($this->isVersion('4.4.0')) {
            $this->runExtensionScript(RegisterTestPluginService::class);

            $this->setVersion('4.4.1');
        }

        $this->skip('4.4.1', '4.5.2');

        if ($this->isVersion('4.5.2') || $this->isVersion('4.6.0')) {

            $service = new ResultServiceWrapper(['class' => ResultsService::class]);
            $this->getServiceManager()->register(ResultServiceWrapper::SERVICE_ID , $service);
            $this->setVersion('4.6.1');
        }

        $this->skip('4.6.1', '4.14.0');

        if ($this->isVersion('4.14.0')) {
            /** @var TaskLogInterface|ConfigurableService $taskLogService */
            $taskLogService = $this->getServiceManager()->get(TaskLogInterface::SERVICE_ID);

            $taskLogService->linkTaskToCategory(ExportDeliveryResults::class, TaskLogInterface::CATEGORY_EXPORT);

            $this->getServiceManager()->register(TaskLogInterface::SERVICE_ID, $taskLogService);

            $this->setVersion('5.0.0');
        }

        $this->skip('5.0.0', '5.2.2');

        if ($this->isVersion('5.2.2')) {
            $this->getServiceManager()->register(ResultCustomFieldsService::SERVICE_ID, new ResultCustomFieldsService());
            $this->getServiceManager()->register(ResultsWatcher::SERVICE_ID, new ResultsWatcher());
            /** @var EventManager $eventManager */
            $eventManager = $this->getServiceManager()->get(EventManager::SERVICE_ID);
            $eventManager->attach(DeliveryExecutionCreated::class, [ResultsWatcher::SERVICE_ID, 'catchCreatedDeliveryExecutionEvent']);
            $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);

            $this->setVersion('5.3.1');
        }

        $this->skip('5.3.0', '5.4.0');

        if ($this->isVersion('5.4.0')) {
            /** @var EventManager $eventManager */
            $eventManager = $this->getServiceManager()->get(EventManager::SERVICE_ID);
            $eventManager->attach(DeliveryExecutionState::class, [ResultServiceWrapper::class, 'deleteResultCache']);
            $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);

            $this->setVersion('5.5.0');
        }
        $this->skip('5.5.0', '5.5.2');
    }
}
