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
    'lodash',
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
], function ($, _, __, url, uri, feedback, locale, encode, loadingBar, binder, dialogConfirm, resultModalTpl) {
    'use strict';

    var $resultsListContainer = $('.results-list-container');
    var $window = $(window);

    /**
     * Internet Explorer and Edge will not open the detail view when the table row was below originally below the fold.
     * This is not cause by a too low container or some sort of overlay. As a workaround they get just as many rows
     * as they can handle in one fold.
     * @returns {number}
     */
    function getNumRows() {
        var lineHeight       = 30;
        var searchPagination = 70;
        var $upperElem       = $('.content-container h2');
        var topSpace         = $upperElem.offset().top
            + $upperElem.height()
            + parseInt($upperElem.css('margin-bottom'), 10)
            + lineHeight
            + searchPagination;
        var availableHeight = $window.height() - topSpace - $('footer.dark-bar').outerHeight();
        if(!window.MSInputMethodContext && !document.documentMode && !window.StyleMedia) {
           return 25;
        }
        return Math.min(Math.floor(availableHeight / lineHeight), 25);
    }



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
                    startClosed : false,
                    minWidth : 450,
                    top: 50
                });
                $container.css({'max-height': $window.height() - 80 + 'px', 'overflow': 'auto'});
                $container.on('click', function(e) {
                    var $target = $(e.target);
                    var $element = null;

                    if ($target.is('a') && $target.hasClass("preview")) {
                        $element = $target;
                    } else {
                        if ($target.is('span') && $target.parent().hasClass("preview")) {
                            $element = $target.parent();
                        }
                    }

                    if ($element) {
                        // the trigger button might itself be inside a modal, in this case close that modal before doing anything else
                        // only one modal should be open
                        var $modalContainer = $element.parents('.modal');
                        if ($modalContainer.length) {
                            $modalContainer.trigger('closed.modal');
                        }
                        $('.preview-overlay').css({ zIndex: $container.modal().css('z-index') + 1 });
                    }
                });
                $container
                    .on('closed.modal', function(){
                        $container.modal('destroy');
                        $('.modal-bg').remove();
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

    function checkValidItem() {
        if (!this.start_time) {
            return true
        }
        return false;
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
            var $contentBlock = $resultsListContainer.parents(".content-block");

            var resizeContainer = function() {
                var padding = $contentBlock.innerHeight() - $contentBlock.height();

                //calculate height for contentArea
                $contentBlock.height(
                    $window.height()
                    - $("footer.dark-bar").outerHeight()
                    - $("header.dark-bar").outerHeight()
                    - $(".tab-container").outerHeight()
                    - $(".action-bar.content-action-bar").outerHeight()
                    - padding
                );
            };

            $window.on('resize', _.debounce(resizeContainer, 300));
            resizeContainer();

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
                    rows: getNumRows(),
                    sortby: 'result_id',
                    sortorder: 'desc',
                    actions : {
                        'view' : {
                            id: 'view',
                            label: __('View'),
                            icon: 'external',
                            action: viewResult,
                            disabled: checkValidItem
                        },
                        'download' :{
                            id : 'download',
                            title : __('Download result'),
                            icon : 'download',
                            label : __('Download'),
                            action: downloadResult,
                            disabled: checkValidItem
                        }
                    }
                });
        }
    };
});