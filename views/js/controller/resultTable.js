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
 * Copyright (c) 2014-2022 (original work) Open Assessment Technologies SA;
 */

/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'module',
    'util/url',
    'ui/feedback',
    'ui/taskQueue/taskQueue',
    'ui/taskQueueButton/standardButton',
    'ui/dateRange/dateRange',
    'layout/loading-bar',
    'ui/datatable'
], function($, _, __, module, urlUtil, feedback, taskQueue, standardTaskButtonFactory, dateRangeFactory, loadingBar) {
    'use strict';

    /**
     * @exports taoOutcomeUi/controller/resultTable
     */
    var resulTableController =  {

        /**
         * Controller entry point
         */
        start : function start() {

            var conf = module.config();
            var $container = $(".result-table");
            var $filterField = $('.result-filter', $container);
            var $filterButtonsContainer = $('.filter-buttons', $container);
            var $tableContainer = $('.result-table-container', $container);
            var $dateStartRangeContainer = $('.de-start-range', $container);
            var $dateEndRangeContainer = $('.de-end-range', $container);
            var filter = conf.filter || 'lastSubmitted';
            var deStartFrom = '';
            var deStartTo = '';
            var deEndFrom = '';
            var deEndTo = '';
            var uri = conf.uri || '';
            //keep columns through calls
            var columns = [];
            var groups = {};

            //setup the date range pickers
            var filterDeStartRange = dateRangeFactory($dateStartRangeContainer, {
                startPicker: {
                    setup: 'datetime',
                    format: 'YYYY-MM-DD HH:mm:ss',
                    field: {
                        name: 'periodStart',
                    }
                },
                endPicker: {
                    setup: 'datetime',
                    format: 'YYYY-MM-DD HH:mm:ss',
                    field: {
                        name: 'periodEnd'
                    }
                }
            })
                .on('change', function (v) {
                    console.log('changed', v)
                })
                .on('render', function () {
                    $('button', $dateStartRangeContainer).hide();
                });

            var filterDeEndRange = dateRangeFactory($dateEndRangeContainer, {
                startPicker: {
                    setup: 'datetime',
                    format: 'YYYY-MM-DD HH:mm:ss',
                    field: {
                        name: 'periodStart',
                    }
                },
                endPicker: {
                    setup: 'datetime',
                    format: 'YYYY-MM-DD HH:mm:ss',
                    field: {
                        name: 'periodEnd'
                    }
                }
            })
                .on('render', function () {
                    $('button', $dateEndRangeContainer).hide();
                });

            /**
             * Load columns to rebuild the datatable dynamically
             * @param {String} url  - the URL that retrieve the columns
             * @param {String} [action = 'add'] - 'add' or 'remove' the retrieved columns
             * @param {Function} done - once the datatable is loaded
             */
            var buildGrid = function buildGrid(url, action, done) {
                loadingBar.start();
                $.ajax({
                    url: url,
                    dataType: 'json',
                    data: {filter: filter, uri: uri},
                    type: 'GET'
                }).done(function (response) {
                    if (response && response.columns) {
                        if (action === 'remove') {
                            columns = columns.filter(col => !response.columns.some(resCol => _.isEqual(col, resCol)));
                        } else {
                            if (typeof response.first !== 'undefined' && response.first === true) {
                                columns = response.columns.concat(columns);
                            } else {
                                columns = columns.concat(response.columns);
                            }
                        }
                    }
                    if (typeof done === 'function') {
                        done();
                    }
                }).always(function () {
                    loadingBar.stop();
                });
            };

            /**
             * Rebuild the datatable
             * @param {Function} done - once the datatable is loaded
             */
            var _buildTable = function _buildTable(done) {
                var model = [];

                //set up model from columns
                _.forEach(columns, function (col) {
                    var colId = col.prop ? (col.prop + '_' + col.contextType) : (col.contextId + '_' + col.variableIdentifier);
                    if (col.columnId) {
                        colId = col.columnId;
                    }
                    model.push({
                        id: colId,
                        label: col.label,
                        sortable: false
                    });
                });

                //re-build the datatable
                $tableContainer
                    .empty()
                    .data('ui.datatable', null)
                    .off('load.datatable')
                    .on('load.datatable', function () {
                        if (typeof done === 'function') {
                            done();
                            done = '';
                        }
                    })
                    .datatable({
                        url: urlUtil.route('feedDataTable', 'ResultTable', 'taoOutcomeUi',
                            {
                                filter: filter,
                                startfrom: deStartFrom,
                                startto: deStartTo,
                                endfrom: deEndFrom,
                                endto: deEndTo
                            }),
                        querytype: 'POST',
                        params: {columns: JSON.stringify(columns), '_search': false, uri: uri},
                        model: model
                    });
            };

            var filterChanged = function filterChanged() {
                filter = $filterField.select2('val');
                deStartFrom = filterDeStartRange.getStart();
                deStartTo = filterDeStartRange.getEnd();
                deEndFrom = filterDeEndRange.getStart();
                deEndTo = filterDeEndRange.getEnd();
                //rebuild the current table
                _buildTable();
            };

            //group button to toggle them
            $('[data-group]', $container).each(function () {
                var $elt = $(this);
                var group = $elt.data('group');
                var action = $elt.data('action');
                groups[group] = groups[group] || {};
                groups[group][action] = $elt;
            });

            //regarding button data, we rebuild the table
            $container.on('click', '[data-group]', function (e) {
                var $elt = $(this);
                var group = $elt.data('group');
                var action = $elt.data('action');
                var url = $elt.data('url');
                e.preventDefault();
                buildGrid(url, action, function () {
                    _.forEach(groups[group], function ($btn) {
                        $btn.toggleClass('hidden');
                    });
                });
            });

            //default table
            buildGrid(urlUtil.route('getTestTakerColumns', 'ResultTable', 'taoOutcomeUi', {filter: filter}));

            //setup the filtering
            $filterField.select2({
                minimumResultsForSearch: -1
            }).select2('val', filter);

            $('.result-filter-btn', $container)
                .click(function () {
                    filterChanged();
                });

            //instantiate the task creation button
            standardTaskButtonFactory({
                type: 'info',
                icon: 'export',
                title: __('Export CSV File'),
                label: __('Export CSV File'),
                taskQueue: taskQueue,
                taskCreationUrl: urlUtil.route('export', 'ResultTable', 'taoOutcomeUi'),
                taskCreationData: function getTaskRequestData() {
                    return {
                        filter: filter,
                        columns: JSON.stringify(columns),
                        uri: uri,
                        startfrom: deStartFrom,
                        startto: deStartTo,
                        endfrom: deEndFrom,
                        endto: deEndTo
                    };
                }
            }).on('error', function (err) {
                feedback().error(err);
            }).render($filterButtonsContainer);

            if (conf.allowSqlExport) {
                standardTaskButtonFactory({
                    type: 'info',
                    icon: 'export',
                    title: __('Export SQL File'),
                    label: __('Export SQL File'),
                    taskQueue: taskQueue,
                    taskCreationUrl: urlUtil.route('exportSQL', 'ResultTable', 'taoOutcomeUi'),
                    taskCreationData: function getTaskRequestData() {
                        filterChanged();
                        return {
                            filter: filter,
                            columns: JSON.stringify(columns),
                            uri: uri,
                            startfrom: deStartFrom,
                            startto: deStartTo,
                            endfrom: deEndFrom,
                            endto: deEndTo
                        };
                    }
                }).on('error', function (err) {
                    feedback().error(err);
                }).render($filterButtonsContainer);
            }
        }
    };
    return resulTableController;
});
