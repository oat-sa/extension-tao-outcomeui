<?php

declare(strict_types=1);

namespace oat\taoOutcomeUi\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\tools\accessControl\SetRolesAccess;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\tao\scripts\update\OntologyUpdater;
use oat\taoOutcomeUi\model\user\LimitedResultsManagerRole;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202305231207284050_taoOutcomeUi extends AbstractMigration
{
    private const CONFIG = [
        SetRolesAccess::CONFIG_RULES => [
            LimitedResultsManagerRole::LIMITED_RESULTS_MANAGER_ROLE => [
                ['ext' => 'taoOutcomeUi', 'mod' => 'Results'],
                ['ext' => 'taoOutcomeUi', 'mod' => 'ResultTable'],
                ['ext' => 'taoDeliveryRdf', 'mod' => 'DeliveryMgmt']
            ],
        ],
    ];

    public function getDescription(): string
    {
        return 'Add and configure limited results manager';
    }

    public function up(Schema $schema): void
    {
        OntologyUpdater::syncModels();
        $setRolesAccess = $this->propagate(new SetRolesAccess());
        $setRolesAccess([
            '--' . SetRolesAccess::OPTION_CONFIG, self::CONFIG,
        ]);
    }

    public function down(Schema $schema): void
    {
        $setRolesAccess = $this->propagate(new SetRolesAccess());
        $setRolesAccess([
            '--' . SetRolesAccess::OPTION_REVOKE,
            '--' . SetRolesAccess::OPTION_CONFIG, self::CONFIG,
        ]);
    }
}
