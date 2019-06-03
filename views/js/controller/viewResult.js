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
 * Copyright (c) 2014-2019 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'module',
    'jquery',
    'i18n',
    'util/url',
    'layout/section',
    'taoItems/previewer/factory',
    'jquery.fileDownload'
], function (module, $,  __,  urlHelper, section, previewerFactory) {
    'use strict';

    /**
     * @exports taoOutcomeUi/controller/viewResult
     */
    const viewResultController =  {

        /**
         * Controller entry point
         */
        start(){
            const conf = module.config();
            const $container = $('#view-result');
            const $resultFilterField = $('.result-filter', $container);
            const $classFilterField = $('[name="class-filter"]', $container);
            let classFilter = JSON.parse(conf.filterTypes) || [];
            //set up filter field
            $resultFilterField.select2({
                minimumResultsForSearch : -1
            }).select2('val', conf.filterSubmission || 'all');

            for(let i in classFilter){
                $(`[value="${classFilter[i]}"]`).prop('checked', 'checked');
            }

            $('.result-filter-btn', $container).click( () => {
                classFilter = [''];
                $classFilterField.each(function() {
                    if($(this).prop('checked')){
                        classFilter.push($(this).val());
                    }
                });
                section.loadContentBlock(
                    urlHelper.route('viewResult', 'Results', 'taoOutcomeUi'), {
                        id: conf.id,
                        classUri:  conf.classUri,
                        filterSubmission: $resultFilterField.select2('val'),
                        filterTypes: classFilter
                    }
                );
            });


            //bind the download buttons
            $('.download', $container).on('click', function() {
                var variableUri = $(this).val();
                $.fileDownload(urlHelper.route('getFile', 'Results', 'taoOutcomeUi'), {
                    preparingMessageHtml: __("We are preparing your report, please wait..."),
                    failMessageHtml: __("There was a problem generating your report, please try again."),
                    httpMethod: "POST",
                    //This gives the current selection of filters (facet based query) and the list of columns selected from the client (the list of columns is not kept on the server side class.taoTable.php
                    data: {'variableUri': variableUri, 'deliveryUri':conf.classUri}
                });
            });

            $('.preview', $container).on('click', function(e) {
                const $this = $(this);
                const deliveryId = $this.data('deliveryId');
                const resultId = $this.data('resultId');
                const itemDefinition = $this.data('definition');
                let uri = $this.data('uri');
                const type = $this.data('type');
                const state = $this.data('state');
                e.preventDefault();

                if (deliveryId && resultId && itemDefinition) {
                    uri = {
                        uri: uri,
                        resultId: resultId,
                        itemDefinition: itemDefinition,
                        deliveryUri: deliveryId
                    };
                }

                previewerFactory(type, uri, state, {
                    readOnly: true,
                    fullPage: true
                });
            });

        }
    };

    return viewResultController;
});
