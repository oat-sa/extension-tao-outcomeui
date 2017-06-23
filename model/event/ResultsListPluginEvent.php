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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA ;
 *
 */

namespace oat\taoOutcomeUi\model\event;

use oat\oatbox\event\Event;

/**
 * Class ResultsListPluginEvent
 * @package oat\taoOutcomeUi\model\event
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
class ResultsListPluginEvent implements Event
{
    /**
     * @var array
     */
    private $plugins;

    /**
     * ResultsListPluginEvent constructor.
     * @param array $plugins
     */
    public function __construct($plugins)
    {
        $this->plugins = $plugins;
    }

    /**
     * @param array $plugins
     */
    public function setPlugins($plugins)
    {
        $this->plugins = $plugins;
    }

    /**
     * @return array
     */
    public function getPlugins()
    {
        return $this->plugins;
    }

    /**
     * Return a unique name for this event
     *
     * @return string
     */
    public function getName()
    {
        return __CLASS__;
    }
}
