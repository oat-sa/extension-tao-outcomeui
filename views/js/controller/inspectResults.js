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
    
    function loadResults(model, filters){
        var params = {};
        if(filters){
            params.filter = filters;
        }
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
                            url     : helpers._url('index', 'ResultTable', 'taoResults', {'filter': filters}),
                            contentBlock : true
                        }).show();
                    });
            })
            .datatable({ 
                url  : helpers._url('getResults', 'InspectResults', 'taoResults', params),
               model : model
            });
    
        
    }

    return {
        start : function(){
            var conf = module.config();

            var facetFilter = buildFacetFilter(conf.filterNodes, function (filters){
                loadResults(conf.model, filters);
            });
            loadResults(conf.model);
        }
    };
});
