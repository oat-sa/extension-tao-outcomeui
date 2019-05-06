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
use oat\tao\model\accessControl\func\AccessRule;
use oat\tao\model\accessControl\func\AclProxy;
use oat\tao\model\taskQueue\TaskLogInterface;
use oat\tao\scripts\update\OntologyUpdater;
use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionCreated;
use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionState;
use oat\taoDeliveryRdf\controller\DeliveryMgmt;
use oat\taoOutcomeUi\controller\Results;
use oat\taoOutcomeUi\model\ResultsService;
use oat\taoOutcomeUi\model\ResultsViewerService;
use oat\taoOutcomeUi\model\review\Reviewer;
use oat\taoOutcomeUi\model\search\ResultCustomFieldsService;
use oat\taoOutcomeUi\model\search\ResultsWatcher;
use oat\taoOutcomeUi\model\Wrapper\ResultServiceWrapper;
use oat\taoOutcomeUi\scripts\install\RegisterTestPluginService;
use oat\taoOutcomeUi\scripts\task\ExportDeliveryResults;
use oat\taoTests\models\event\TestChangedEvent;

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

        $this->skip('4.14.0', '5.2.2');

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

        $this->skip('5.5.0', '5.9.2');

        if ($this->isVersion('5.9.2')) {
            /** @var TaskLogInterface|ConfigurableService $taskLogService */
            $taskLogService = $this->getServiceManager()->get(TaskLogInterface::SERVICE_ID);

            $taskLogService->linkTaskToCategory(ExportDeliveryResults::class, TaskLogInterface::CATEGORY_EXPORT);

            $this->getServiceManager()->register(TaskLogInterface::SERVICE_ID, $taskLogService);

            $this->setVersion('5.10.0');
        }

        $this->skip('5.10.0', '5.10.1');

        if ($this->isVersion('5.10.1')) {
            $service = new ResultsViewerService();
            $this->getServiceManager()->register(ResultsViewerService::SERVICE_ID , $service);

            $this->setVersion('5.11.0');
        }

        $this->skip('5.11.0', '5.11.3');

        if ($this->isVersion('5.11.3')) {
            OntologyUpdater::syncModels();
            AclProxy::applyRule(new AccessRule(AccessRule::GRANT, Reviewer::REVIEWER_ROLE, DeliveryMgmt::class . '@getOntologyData'));
            AclProxy::applyRule(new AccessRule(AccessRule::GRANT, Reviewer::REVIEWER_ROLE, Results::class . '@index'));
            AclProxy::applyRule(new AccessRule(AccessRule::GRANT, Reviewer::REVIEWER_ROLE, Results::class . '@getResults'));
            AclProxy::applyRule(new AccessRule(AccessRule::GRANT, Reviewer::REVIEWER_ROLE, Results::class . '@viewResult'));
            AclProxy::applyRule(new AccessRule(AccessRule::GRANT, Reviewer::REVIEWER_ROLE, Results::class . '@downloadXML'));
            AclProxy::applyRule(new AccessRule(AccessRule::GRANT, Reviewer::REVIEWER_ROLE, Results::class . '@getFile'));
            AclProxy::applyRule(new AccessRule(AccessRule::GRANT, Reviewer::REVIEWER_ROLE, Results::class . '@getResultsListPlugin'));
            AclProxy::applyRule(new AccessRule(AccessRule::GRANT, Reviewer::REVIEWER_ROLE, Results::class . '@export'));
            $this->setVersion('5.12.0');
        }

        $this->skip('5.12.0', '5.12.2');

        if ($this->isVersion('5.12.2')) {
            /** @var ResultServiceWrapper $resultsService */
            $resultsService = $this->getServiceManager()->get(ResultServiceWrapper::SERVICE_ID);
            $options = $resultsService->getOptions();
            $options[ResultServiceWrapper::RESULT_COLUMNS_CHUNK_SIZE_OPTION] = 20;
            $resultsService->setOptions($options);
            $this->getServiceManager()->register(ResultServiceWrapper::SERVICE_ID, $resultsService);
            $this->setVersion('5.13.0');
        }

        $this->skip('5.13.0', '7.4.3');

        if ($this->isVersion('7.4.3')) {
            /** @var EventManager $eventManager */
            $eventManager = $this->getServiceManager()->get(EventManager::SERVICE_ID);
            $eventManager->attach(TestChangedEvent::EVENT_NAME, [ResultsWatcher::SERVICE_ID, 'catchTestChangedEvent']);
            $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);

            $this->setVersion('7.5.0');
        }

        $this->skip('7.5.0', '7.5.3');
    }
}
