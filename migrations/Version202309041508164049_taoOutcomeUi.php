<?php

declare(strict_types=1);

namespace oat\taoOutcomeUi\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoOutcomeUi\model\ResultsViewerService;
use oat\taoQtiTest\models\DeliveryItemTypeService;

final class Version202309041508164049_taoOutcomeUi extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migrate default item type config from oat\taoOutcomeUi\model\ResultsViewerService to oat\taoQtiTest\models\DeliveryItemTypeService';
    }

    public function up(Schema $schema): void
    {
        if (!$this->getServiceManager()->has(ResultsViewerService::SERVICE_ID)) {
            return;
        }

        /** @var ResultsViewerService $service */
        $service = $this->getServiceManager()->get(ResultsViewerService::SERVICE_ID);
        $defaultItemType = $service->getDefaultItemType();

        if (!$defaultItemType) {
            return;
        }

        /** @var DeliveryItemTypeService $service */
        $service = $this->getServiceManager()->get(DeliveryItemTypeService::SERVICE_ID);
        $service->setDefaultItemType($defaultItemType);
        $this->getServiceManager()->register(DeliveryItemTypeService::SERVICE_ID, $service);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}

