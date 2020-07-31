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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

declare(strict_types=1);

namespace oat\taoOutcomeUi\migrations;

use common_Exception;
use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\service\exception\InvalidServiceManagerException;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoOutcomeUi\model\ResultsService;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202007260136104049_taoOutcomeUi extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Add option allow_sql_result to ResultsService';
    }

    /**
     * @param Schema $schema
     * @throws common_Exception
     * @throws InvalidServiceManagerException
     */
    public function up(Schema $schema): void
    {
        $this->getServiceManager()->register(ResultsService::SERVICE_ID, new ResultsService(
            [
                ResultsService::OPTION_ALLOW_SQL_EXPORT => false
            ]
        ));
    }

    /**
     * @param Schema $schema
     * @throws InvalidServiceManagerException
     */
    public function down(Schema $schema): void
    {
        $this->getServiceManager()->unregister(ResultsService::SERVICE_ID);
    }
}
