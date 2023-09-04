<?php

declare(strict_types=1);

namespace oat\taoOutcomeUi\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoQtiTest\models\DeliveryItemTypeService;

final class Version202309041508164049_taoOutcomeUi extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migrate default item type config from oat\taoOutcomeUi\model\ResultsViewerService to oat\taoQtiTest\models\DeliveryItemTypeService';
    }

    public function up(Schema $schema): void
    {
        $previousConfigFilePath = realpath(__DIR__ . '/../../config/taoOutcomeUi/resultsViewer.conf.php');
        if (!file_exists($previousConfigFilePath)) {
            return;
        }

        $configData = file_get_contents($previousConfigFilePath);
        if(!$configData) {
            return;
        }

        preg_match("~'defaultItemType' => '(.*?)'~", $configData, $matches);
        if (!isset($matches[1])) {
            return;
        }

        /** @var DeliveryItemTypeService $service */
        $service = $this->getServiceManager()->getContainer()->get(DeliveryItemTypeService::SERVICE_ID);
        $service->setDefaultItemType($matches[1]);
        $this->getServiceManager()->register(DeliveryItemTypeService::SERVICE_ID, $service);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
