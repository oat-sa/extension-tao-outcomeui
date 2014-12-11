/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery', 
    'i18n', 
    'module',
    'helpers', 
    'layout/actions/binder',
    'layout/section',
    'uri',
    'ui/datatable'
], function($, __, module, helpers, binder, section, uri) {
    'use strict';

    /**
     * Load results into the datatable
     * @param {jQueryElement} $container - the  container 
     * @param {Object} model - the data model for the table
     * @param {String} implementation - The delivery result server implementation
     * @param {String} classUri - The delivery result uri
     */
    function loadResults($container, model, implementation, classUri){
        var params = {
            implementation : implementation,
            classUri : classUri
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
                    'view' : function openResource(id){
                                var action = {binding : "load", url: helpers._url('viewResult', 'Results', 'taoOutcomeUi')};
                                binder.exec(action, {uri : uri.encode(id), classUri : classUri} || this._resourceContext);
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
            loadResults($container, conf.model, conf.implementation, conf.uri);
        }
    };

    return inspectResultController;
});
