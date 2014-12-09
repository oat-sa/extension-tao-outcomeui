/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery', 
    'i18n', 
    'module',
    'helpers', 
    'layout/section', 
    'uri',
    'ui/datatable'
], function($, __, module, helpers, section, uri) {
    'use strict';

    /**
     * Load results into the datatable
     * @param {jQueryElement} $container - the  container 
     * @param {Object} model - the data model for the table
     * @param {String} implementation - The delivery result server implementation
     */
    function loadResults($container, model, implementation){
        var params = {
            implementation : implementation
        };
        $('.inspect-results-grid', $container)
            .empty()
            .data('ui.datatable', null)
            .on('load.datatable', function(){
                $('.export-table', $container)
                    .off('click')
                    .removeClass('disabled')
                    .on('click', function(e){
                        e.preventDefault();
                        section.create({
                            id      : 'buildTableTab',
                            name    : __('Export Delivery Results'),
                            url     : helpers._url('index', 'ResultTable', 'taoOutcomeUi', params),
                            contentBlock : true
                        }).show();
                    });
            })
            .datatable({ 
                url  : helpers._url('getResults', 'Results', 'taoOutcomeUi', params),
                model : model,
                actions : {
                    'preview' : function openResource(id){
                                    $('.tree').trigger('selectnode.taotree', [{id : uri.encode(id)}]);
                                }
    }
            });
    }

    /**
     * @exports taoOutcomeUi/controller/resultTable
     */
    var inspectResultController =  {

        /**
         * Controller entry point
         */
        start : function(){
            var conf = module.config();
            var $container = $('#inspect-result');
            //load results also at the beginning unfiltered
            loadResults($container, conf.model, conf.implementation);
        }
    };

    return inspectResultController;
});
