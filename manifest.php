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
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *               2012-2017 (update and modification) Open Assessment Technologies SA;
 *
 */

use oat\taoOutcomeUi\scripts\install\RegisterEvent;
use oat\taoOutcomeUi\scripts\install\RegisterTestPluginService;
use oat\taoOutcomeUi\scripts\install\SetUpQueueTasks;
use oat\taoOutcomeUi\scripts\install\SetupSearchService;
use oat\taoOutcomeUi\model\review\Reviewer;
use oat\taoDeliveryRdf\controller\DeliveryMgmt;
use oat\tao\model\accessControl\func\AccessRule;
use oat\taoOutcomeUi\controller\Results;

$extpath = dirname(__FILE__) . DIRECTORY_SEPARATOR;

return [
    'name'           => 'taoOutcomeUi',
    'label'          => 'Result visualisation',
    'description'    => 'TAO Results extension',
    'license'        => 'GPL-2.0',
    'version'        => '7.5.3',
    'author'         => 'Open Assessment Technologies, CRP Henri Tudor',
    // taoItems is only needed for the item model property retrieval
    'requires'       => [
        'generis' => '>=7.9.8',
        'taoResultServer' => '>=7.0.0',
        'taoItems'        => '>=6.0.0',
        'taoDeliveryRdf'  => '>=6.0.0',
        'tao'             => '>=21.15.0'
    ],
    'install'        => [
        'php' => [
            RegisterTestPluginService::class,
            SetUpQueueTasks::class,
            SetupSearchService::class,
            RegisterEvent::class
        ],
        'rdf' => [
            __DIR__ . '/install/ontology/reviewerRole.rdf',
        ]
    ],
    'uninstall'      => [],
    'update'         => 'oat\\taoOutcomeUi\\scripts\\update\\Updater',
    'managementRole' => 'http://www.tao.lu/Ontologies/TAOResult.rdf#ResultsManagerRole',
    'acl'            => [
        ['grant', 'http://www.tao.lu/Ontologies/TAOResult.rdf#ResultsManagerRole', ['ext' => 'taoOutcomeUi']],
        [AccessRule::GRANT, Reviewer::REVIEWER_ROLE, DeliveryMgmt::class.'@getOntologyData'],
        [AccessRule::GRANT, Reviewer::REVIEWER_ROLE, Results::class . '@index'],
        [AccessRule::GRANT, Reviewer::REVIEWER_ROLE, Results::class . '@getResults'],
        [AccessRule::GRANT, Reviewer::REVIEWER_ROLE, Results::class . '@viewResult'],
        [AccessRule::GRANT, Reviewer::REVIEWER_ROLE, Results::class . '@downloadXML'],
        [AccessRule::GRANT, Reviewer::REVIEWER_ROLE, Results::class . '@getFile'],
        [AccessRule::GRANT, Reviewer::REVIEWER_ROLE, Results::class . '@getResultsListPlugin'],
        [AccessRule::GRANT, Reviewer::REVIEWER_ROLE, Results::class . '@export'],
    ],
    'routes'         => [
        '/taoOutcomeUi' => 'oat\\taoOutcomeUi\\controller'
    ],
    'constants'      => [
        // views directory
        "DIR_VIEWS"           => $extpath . "views" . DIRECTORY_SEPARATOR,

        // default module name
        'DEFAULT_MODULE_NAME' => 'Results',

        // default action name
        'DEFAULT_ACTION_NAME' => 'index',

        // BASE PATH: the root path in the file system (usually the document root)
        'BASE_PATH'           => $extpath,

        // BASE URL (usually the domain root)
        'BASE_URL'            => ROOT_URL . 'taoOutcomeUi/',
    ],
    'extra'          => [
        'structures' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR . 'structures.xml'
    ]
];
