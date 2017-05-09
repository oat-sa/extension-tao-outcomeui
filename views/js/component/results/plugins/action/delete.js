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
    'uri',
    'core/plugin',
    'util/url',
    'util/encode',
    'ui/dialog/confirm',
    'ui/feedback'
], function ($, __, uri, pluginFactory, urlHelper, encode, dialogConfirm, feedback) {
    'use strict';

    /**
     * Will add a "Delete" button on each line of the list of results
     */
    return pluginFactory({
        name: 'delete',

        init: function init() {
            var resultsList = this.getHost();

            // this action will be available for each displayed line in the list
            resultsList.addAction({
                id: 'delete',
                label: __('Delete'),
                icon: 'delete',
                action: function deleteResults(id) {
                    // prompt a confirmation dialog and then delete the result
                    dialogConfirm(__('Please confirm deletion'), function () {
                        $.ajax({
                            url: urlHelper.route('delete', 'Results', 'taoOutcomeUi'),
                            type: "POST",
                            data: {
                                uri: uri.encode(id)
                            },
                            dataType: 'json'
                        }).done(function (response) {
                            if (response.deleted) {
                                feedback().success(__('Result has been deleted'));
                                resultsList.refresh();
                            } else {
                                feedback().error(__('Something went wrong...') + '<br>' + encode.html(response.error), {encodeHtml: false});
                                resultsList.trigger('error', response.error);
                            }
                        }).fail(function (err) {
                            feedback().error(__('Something went wrong...'));
                            resultsList.trigger('error', err);
                        });
                    });
                }
            });
        }
    });
});
