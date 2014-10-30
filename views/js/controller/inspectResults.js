/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery', 
    'i18n', 
    'module',
    'helpers', 
    'layout/section', 
    'generis.facetFilter', 
    'ui/datatable'
], function($, __, module, helpers, section, GenerisFacetFilterClass) {
    'use strict';

    /**
     * Build tthe facet filtering component
     * @param {Object} filterNodes - the filter the component will contain
     * @param {Function} cb - called with the selected filters
     */
    function buildFacetFilter(filterNodes, cb){
		return new GenerisFacetFilterClass('#facet-filter', filterNodes, {
			template: 'accordion',
			callback: {
				'onFilter': function(facetFilter) {
					cb(facetFilter.getFormatedFilterSelection());
				}
			}
		});
    }
   
    /**
     * Load results into the datatable
     * @param {Object} model - the data model for the table
     * @param {String} [filter = 'all'] - to filter results
     */
    function loadResults(model, filter){
        var params = {
            filter : filter
        };
        $('#inspect-results-grid')
            .empty()
            .data('ui.datatable', null)
            .on('load.datatable', function(){
                $('#buildTableButton')
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
                url  : helpers._url('getResults', 'InspectResults', 'taoOutcomeUi', params),
               model : model
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

            //set up the facet filter and load results on changes
            var facetFilter = buildFacetFilter(conf.filterNodes, function (filters){
                loadResults(conf.model, filters);
            });

            //load results also at the beginning unfiltered
            loadResults(conf.model);
        }
    };

    return inspectResultController;
});
