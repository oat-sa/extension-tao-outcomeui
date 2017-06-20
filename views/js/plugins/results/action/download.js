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
    'jquery',
    'i18n',
    'core/plugin',
    'util/url',
    'jquery.fileDownload'
], function ($, __, pluginFactory, urlHelper) {
    'use strict';

    /**
     * Will add a "Download" button on each line of the list of results
     */
    return pluginFactory({
        name: 'download',

        init: function init() {
            var resultsList = this.getHost();

            // this action will be available for each displayed line in the list
            resultsList.addAction({
                id: 'download',
                label: __('Download'),
                icon: 'download',
                action: function downloadResults(id) {
                    $.fileDownload(urlHelper.route('downloadXML', 'Results', 'taoOutcomeUi'), {
                        preparingMessageHtml: __("We are preparing your report, please wait..."),
                        failMessageHtml: __("There was a problem generating your report, please try again."),
                        httpMethod: 'GET',
                        data: {
                            id: id,
                            delivery: resultsList.getClassUri()
                        }
                    });
                }
            });
        }
    });
});
