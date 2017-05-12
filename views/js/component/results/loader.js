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
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'core/pluginLoader',
    'taoOutcomeUi/component/results/plugins/action/view',
    'taoOutcomeUi/component/results/plugins/action/delete',
    'taoOutcomeUi/component/results/plugins/action/download'
], function (pluginLoader, actionView, actionDelete, actionDownload) {
    'use strict';

    /**
     * Instantiates the plugin loader with all the required plugins configured
     */
    return pluginLoader({
        action: [actionView, actionDelete, actionDownload]
    });
});