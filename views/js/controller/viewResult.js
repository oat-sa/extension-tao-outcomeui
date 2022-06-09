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
 * Copyright (c) 2014-2021 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'module',
    'jquery',
    'i18n',
    'util/url',
    'core/logger',
    'core/request',
    'layout/section',
    'taoItems/previewer/factory',
    'ui/dialog/confirm',
    'uri',
    'ui/feedback',
    'core/router',
    'jquery.fileDownload'
], function (
    module,
    $,
    __,
    urlHelper,
    loggerFactory,
    request,
    section,
    previewerFactory,
    dialogConfirm,
    uriHelper,
    feedback,
    router
) {
    'use strict';

    const logger = loggerFactory('taoOutcomeUi/viewResults');
    const downloadUrl = urlHelper.route('getFile', 'Results', 'taoOutcomeUi');
    const dataUrl = urlHelper.route('getVariableFile', 'Results', 'taoOutcomeUi');

    /**
     * Requests a file content given the URIs
     * @param {String} variableUri - The URI of a variable
     * @param {String} deliveryUri - The URI of a delivery
     * @returns {Promise}
     */
    function requestFileContent(variableUri, deliveryUri) {
        return request({
            url: dataUrl,
            method: 'POST',
            data: { variableUri, deliveryUri }
        })
            .then(response => {
                // The response may contain more than the expected data,
                // like the success status, which is not relevant here.
                // Hence this rewriting.
                const { data, name, mime } = response;
                return { data, name, mime };
            });
    }

    /**
     * Makes sure the response contains the file data if it is a file record
     * @param {Object} response
     * @param {String} deliveryUri
     * @returns {Promise}
     */
    function refineFileResponse(response, deliveryUri) {
        const { file } = response && response.base || {};
        if (file && file.uri && !file.data) {
            return requestFileContent(file.uri, deliveryUri)
                .then(fileData => {
                    if (fileData && fileData.data) {
                        response.base.file = fileData;
                    } else {
                        response.base = null;
                    }
                })
                .catch(e => logger.error(e));
        }
        return Promise.resolve();
    }

    /**
     * Makes sure the item state contains the file data in the response if it is a file record
     * @param {Object} state
     * @param {String} deliveryUri
     * @returns {Promise}
     */
    function refineItemState(state, deliveryUri) {
        if (!state) {
            return Promise.resolve(state);
        }

        const filePromises = Object.keys(state).map(identifier => {
            const { response } = state[identifier];
            return refineFileResponse(response, deliveryUri);
        });
        return Promise.all(filePromises).then(() => state);
    }

    /**
     * @exports taoOutcomeUi/controller/viewResult
     */
    const viewResultController =  {

        /**
         * Controller entry point
         */
        start(){
            const conf = module.config();
            const deliveryUri = conf.classUri;
            const $container = $('#view-result');
            const $resultFilterField = $('.result-filter', $container);
            const $classFilterField = $('[name="class-filter"]', $container);
            let classFilter = JSON.parse(conf.filterTypes) || [];

            //set up filter field
            $resultFilterField.select2({
                minimumResultsForSearch : -1
            }).select2('val', conf.filterSubmission || 'all');

        for (let i in classFilter) {
            $(`[value = "${classFilter[i]}"]`).prop('checked', 'checked');
        }

            $('.result-filter-btn', $container).click(() => {
                classFilter = [''];
                $classFilterField.each(function () {
                    if ($(this).prop('checked')) {
                        classFilter.push($(this).val());
                    }
                });
                section.loadContentBlock(
                    urlHelper.route('viewResult', 'Results', 'taoOutcomeUi'),
                    {
                        id: conf.id,
                        classUri:  conf.classUri,
                        filterSubmission: $resultFilterField.select2('val'),
                        filterTypes: classFilter
                    }
                );
            });


            //bind the xml download button
            $('#xmlDownload', $container).on('click', function () {
                $.fileDownload(urlHelper.route('downloadXML', 'Results', 'taoOutcomeUi'), {
                    failMessageHtml: __("Unexpected error occurred when generating your report. Please contact your system administrator."),
                    httpMethod: 'GET',
                    data: {
                        id: conf.id,
                        delivery: conf.classUri
                    }
                });
            });

            // bind the file download button
            $('[id^=fileDownload]', $container).on('click', function () {
                const variableUri = $(this).val();
                $.fileDownload(downloadUrl, {
                    httpMethod: 'POST',
                    data: {
                        variableUri,
                        deliveryUri
                    }
                });
            });

            //bind the download buttons
            $('.delete', $container).on('click', function () {
                dialogConfirm(__('Please confirm deletion'), function () {
                    $.ajax({
                        url: urlHelper.route('delete', 'Results', 'taoOutcomeUi'),
                        type: "POST",
                        data: {
                            uri: uriHelper.encode(conf.id)
                        },
                        dataType: 'json'
                    }).done(function (response) {
                        if (response.deleted) {
                            feedback().success(__('Result has been deleted'));
                            // Hack to go back to the list of results
                            router.dispatch('tao/Main/index');
                        } else {
                            feedback().error(__('Something went wrong...') + '<br>' + encode.html(response.error), {encodeHtml: false});
                        }
                    }).fail(function () {
                        feedback().error(__('Something went wrong...'));
                    });
                });
            });

            $('.print', $container).on('click', function () {
                window.print();
            });

            $('.preview', $container).on('click', function (e) {
                const $btn = $(this);
                const deliveryId = $btn.data('deliveryId');
                const resultId = $btn.data('resultId');
                const itemDefinition = $btn.data('definition');
                let uri = $btn.data('uri');
                const type = $btn.data('type');
                e.preventDefault();
                if ($btn.prop('disabled')) {
                    return;
                }
                $btn.prop('disabled', true).addClass('disabled');

                if (deliveryId && resultId && itemDefinition) {
                    uri = {
                        uri: uri,
                        resultId: resultId,
                        itemDefinition: itemDefinition,
                        deliveryUri: deliveryId
                    };
                }

                Promise.resolve($btn.data('state'))
                    .then(state => refineItemState(state, deliveryUri))
                    .then(state => {
                        $btn.removeProp('disabled').removeClass('disabled');
                        previewerFactory(type, uri, state, {
                            view: 'reviewRenderer',
                            fullPage: true
                        });
                    })
                    .catch(err => logger.error(err));
            });

        }
    };

    return viewResultController;
});
