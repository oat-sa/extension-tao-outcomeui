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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Aleksej Tikhanovich, <aleksej@taotesting.com>
 */

define([
    'jquery',
    'i18n',
    'util/url',
    'uri',
    'ui/feedback',
    'util/locale',
    'util/encode',
    'layout/loading-bar',
    'layout/actions/binder',
    'ui/dialog/confirm',
    'tpl!taoOutcomeUi/controller/resultModal',
    'ui/datatable'
], function ($, __, url, uri, feedback, locale, encode, loadingBar, binder, dialogConfirm, resultModalTpl) {
    'use strict';

    var $resultsListContainer = $('.results-list-container');

    function getRequestErrorMessage (xhr) {
        loadingBar.start();
        var message = '';
        try {
            var responseJSON = $.parseJSON(xhr.responseText);
            if (responseJSON.message) {
                message = responseJSON.message;
            } else {
                message = xhr.responseText;
            }
        } catch (e) {
            message = xhr.responseText;
        }
        return message;
    }

    function viewResult(rowId) {
        var res = parseRowId(rowId);
        loadingBar.start();
        $.ajax({
            url : url.route('viewResult', 'Results', 'taoOutcomeUi', {id : res[0], classUri: res[1]}),
            type : 'GET',
            success : function (result) {
                var $container = $(resultModalTpl()).append(result);
                $resultsListContainer.append($container);
                $container.modal({
                    startClosed : true,
                    minWidth : 450
                });
                $container
                    .modal('open')
                    .on('closed.modal', function(){
                        $container.modal('destroy');
                        $(this).remove();
                    });
                loadingBar.stop();
            },
            error : function (xhr, err) {
                var message = getRequestErrorMessage(xhr);
                feedback().error(message, {encodeHtml : false});
                loadingBar.stop();
            }
        });
    }

    function removeResult(rowId) {
        var res = parseRowId(rowId);
        // prompt a confirmation dialog and then delete the result
        dialogConfirm(__('Please confirm deletion'), function () {
            $.ajax({
                url: url.route('delete', 'Results', 'taoOutcomeUi'),
                type: "POST",
                data: {
                    uri: uri.encode(res[0])
                },
                dataType: 'json'
            }).done(function (response) {
                if (response.deleted) {
                    feedback().success(__('Result has been deleted'));
                    $resultsListContainer.datatable('refresh');
                } else {
                    feedback().error(__('Something went wrong...') + '<br>' + encode.html(response.error), {encodeHtml: false});
                    $resultsListContainer.trigger('error', response.error);
                }
            }).fail(function (err) {
                feedback().error(__('Something went wrong...'));
                $resultsListContainer.trigger('error', err);
            });
        });
    }

    function downloadResult(rowId) {
        var res = parseRowId(rowId);
        $.fileDownload(url.route('downloadXML', 'Results', 'taoOutcomeUi'), {
            preparingMessageHtml: __("We are preparing your report, please wait..."),
            failMessageHtml: __("There was a problem generating your report, please try again."),
            httpMethod: 'GET',
            data: {
                id: res[0],
                delivery: res[1]
            }
        });
    }

    function parseRowId(rowId) {
        return rowId.split("|");
    }

    return {
        start: function () {
            $resultsListContainer
                .datatable({
                    url: url.route('data', 'ResultsMonitoring', 'taoOutcomeUi'),
                    filter: true,
                    labels: {
                        filter: __('Search by results')
                    },
                    model: [{
                        id: 'delivery',
                        label: __('Delivery'),
                        sortable: false
                    }, {
                        id: 'deliveryResultIdentifier',
                        label: __('Delivery Execution'),
                        sortable: false,
                    }, {
                        id: 'testTakerIdentifier',
                        label: __('Test Taker'),
                        sortable: false
                    }, {
                        id: 'start_time',
                        label: __('Start Time'),
                        sortable: false
                    }],
                    paginationStrategyTop: 'simple',
                    paginationStrategyBottom: 'pages',
                    rows: 25,
                    actions : {
                        'view' : {
                            id: 'view',
                            label: __('View'),
                            icon: 'view',
                            action: viewResult
                        },
                        'remove' : {
                            id : 'remove',
                            title : __('Delete result'),
                            icon : 'bin',
                            label : __('Delete'),
                            action: removeResult
                        },
                        'download' :{
                            id : 'download',
                            title : __('Download result'),
                            icon : 'download',
                            label : __('Download'),
                            action: downloadResult
                        }
                    }
                });
        }
    };
});