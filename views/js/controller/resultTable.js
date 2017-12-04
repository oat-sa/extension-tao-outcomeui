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
 * Copyright (c) 2014-2017 (original work) Open Assessment Technologies SA;
 */

/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'helpers',
    'module',
    'taoTaskQueue/model/taskQueue',
    'taoTaskQueue/component/taskCreationButton/taskCreationButton',
    'ui/datatable'
], function($, _, __, helpers, module, taskQueue, taskCreationButtonFactory) {
    'use strict';

    /**
     * @exports taoOutcomeUi/controller/resultTable
     */
    var resulTableController =  {

        /**
         * Controller entry point
         */
        start : function(){

           var conf = module.config();
           var $container = $(".result-table");
           var $filterField = $('.result-filter', $container);
           var $tableContainer = $('.result-table-container', $container);
           var filter = conf.filter || 'lastSubmitted';
           var uri = conf.uri || '';
            //keep columns through calls
            var columns = [];
            var groups = {};
            var $actionBar = $container.find('.actions');

            /**
             * Load columns to rebuild the datatable dynamically
             * @param {String} url  - the URL that retrieve the columns
             * @param {String} [action = 'add'] - 'add' or 'remove' the retrieved columns
             * @param {Function} done - once the datatable is loaded
             */
            var buildGrid = function buildGrid(url, action, done){
                $.ajax({
                    url : url,
                    dataType : 'json',
                    data : {filter : filter, uri : uri},
                    type :'GET'
                }).done(function(response){
                    if(response && response.columns){
                        if(action === 'remove'){
                            columns = _.reject(columns, function(col){
                               return _.find(response.columns, function(resCol){
                                    return _.isEqual(col, resCol);
                               });
                            });
                        } else {
                            if(typeof response.first !== 'undefined' && response.first === true){
                                columns = response.columns.concat(columns);
                            }
                            else{
                                columns = columns.concat(response.columns);
                            }
                        }
                        _buildTable(done);
                    }
                });
            };

            /**
             * Rebuild the datatable
             * @param {Function} done - once the datatable is loaded
             */
            var _buildTable = function _buildTable(done){
                var model = [];

                //set up model from columns
                _.forEach(columns, function(col){
                    model.push({
                        id : col.prop ? (col.prop + '_' + col.contextType) : (col.contextId + '_' + col.variableIdentifier),
                        label : col.label,
                        sortable: false
                    });
                });

                //re-build the datatable
                $tableContainer
                    .empty()
                    .data('ui.datatable', null)
                    .off('load.datatable')
                    .on('load.datatable', function(){

                        //integrate the task creation button
                        taskCreationButtonFactory({
                            type : 'info',
                            icon : 'export',
                            title : __('Export CSV File'),
                            label : __('Export CSV File'),
                            taskQueue : taskQueue,
                            sourceElement : $container.find('.result-table-container'),
                            requestUrl : helpers._url('export', 'ResultTable', 'taoOutcomeUi'),
                            getRequestData : function getRequestData(){
                                return {
                                    filter: filter,
                                    columns: JSON.stringify(columns),
                                    uri: uri
                                };
                            }
                        }).render($actionBar);

                        if(_.isFunction(done)){
                            done();
                            done = '';
                        }
                    })
                    .datatable({
                        url : helpers._url('feedDataTable', 'ResultTable', 'taoOutcomeUi', {filter : filter}),
                        querytype : 'POST',
                        params: {columns: JSON.stringify(columns), '_search': false, uri: uri},
                        model :  model
                    });
            };

            //group button to toggle them
            $('[data-group]', $container).each(function(){
                var $elt = $(this);
                var group = $elt.data('group');
                var action = $elt.data('action');
                groups[group] = groups[group] || {};
                groups[group][action] = $elt;
            });

            //regarding button data, we rebuild the table
            $container.on('click', '[data-group]', function(e){
                var $elt    = $(this);
                var group   = $elt.data('group');
                var action  = $elt.data('action');
                var url     = $elt.data('url');
                e.preventDefault();
                buildGrid(url, action, function(){
                    _.forEach(groups[group], function($btn){
                       $btn.toggleClass('hidden');
                    });
                });
            });

            //default table
            buildGrid(helpers._url('getTestTakerColumns', 'ResultTable', 'taoOutcomeUi', {filter : filter}));

            //setup the filtering
            $filterField.select2({
                minimumResultsForSearch : -1
            }).select2('val', filter);

            $('.result-filter-btn', $container).click(function() {
                filter = $filterField.select2('val');
                //rebuild the current table
                _buildTable();
            });
        }
    };
    return resulTableController;
});
