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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'jquery',
    'i18n',
    'lodash',
    'uri',
    'core/promise',
    'ui/component',
    'taoOutcomeUi/component/results/areaBroker',
    'tpl!taoOutcomeUi/component/results/list',
    'ui/datatable'
], function ($, __, _, uri, Promise, component, resultsAreaBroker, listTpl) {
    'use strict';

    /**
     * Component that lists all the results entry points for a particular delivery
     *
     * @param {Object} config
     * @param {String} config.classUri
     * @param {String} config.dataUrl
     * @param {Object} config.model
     * @param {Array} pluginFactories
     * @returns {resultsList}
     */
    function resultsListFactory(config, pluginFactories) {
        var resultsList;
        var areaBroker;
        var plugins = {};
        var actions = [];
        var classUri;

        /**
         * Calls a method from each plugins
         *
         * @param {String} method - the name of the method to run
         * @returns {Promise} Resolved when all plugins are done
         */
        function pluginRun(method) {
            var execStack = [];

            _.forEach(plugins, function (plugin) {
                if (_.isFunction(plugin[method])) {
                    execStack.push(plugin[method]());
                }
            });

            return Promise.all(execStack);
        }

        if (!_.isPlainObject(config)) {
            throw new TypeError('The configuration is required');
        }
        if (!_.isString(config.classUri) || !config.classUri) {
            throw new TypeError('The class URI is required');
        }
        if (!_.isString(config.dataUrl) || !config.dataUrl) {
            throw new TypeError('The service URL is required');
        }
        if (!_.isPlainObject(config.model) && !_.isArray(config.model)) {
            throw new TypeError('The data model is required');
        }

        classUri = uri.decode(config.classUri);

        /**
         *
         * @typedef {resultsList}
         */
        resultsList = component({

            /**
             * Refreshes the list
             * @returns {resultsList} chains
             */
            refresh: function refresh() {
                areaBroker.getListArea().datatable('refresh');
                return this;
            },

            /**
             * Add a line action
             * @param {String|Object} name
             * @param {Function|Object} [action]
             * @returns {resultsList} chains
             */
            addAction: function addAction(name, action) {
                var descriptor;

                if (_.isPlainObject(name)) {
                    descriptor = name;
                } else if (_.isPlainObject(action)) {
                    descriptor = action;
                    action.id = name;
                } else {
                    descriptor = {
                        id: name,
                        label: name
                    };
                }

                if (_.isFunction(action)) {
                    descriptor.action = action;
                }
                if (!descriptor.label) {
                    descriptor.label = descriptor.id;
                }

                actions.push(descriptor);

                return this;
            },

            /**
             * Gives an access to the config
             * @returns {Object}
             */
            getConfig: function getConfig() {
                return this.config;
            },

            /**
             * Gets the class URI
             * @returns {String}
             */
            getClassUri: function getClassUri() {
                return classUri;
            },

            /**
             * Gets the registered actions
             * @returns {Array}
             */
            getActions: function getActions() {
                return actions;
            }
        });

        return resultsList
            .before('render', function onRender() {
                var self = this;

                areaBroker = resultsAreaBroker(this.$component, {
                    'list': $('.list', this.$component)
                });

                _.forEach(pluginFactories, function (pluginFactory) {
                    var plugin = pluginFactory(self, areaBroker);
                    plugins[plugin.getName()] = plugin;
                });

                return pluginRun('init')
                    .then(function() {
                        return pluginRun('render');
                    })
                    .then(function () {
                        areaBroker.getListArea()
                            .empty()
                            .on('query.datatable', function () {
                                self.setState('loading', true)
                                    .trigger('loading');
                            })
                            .on('load.datatable', function () {
                                self.setState('loading', false)
                                    .trigger('loaded');
                            })
                            .datatable({
                                url: self.config.dataUrl,
                                model: self.config.model,
                                actions: actions,
                                filter: self.config.searchable,
                                labels: {
                                    filter: __('Search by delivery results')
                                }
                            });
                    })
                    .catch(function (err) {
                        self.trigger('error', err);
                        return Promise.reject(err);
                    });
            })
            .before('destroy', function onDestroy() {
                var self = this;

                return pluginRun('destroy')
                    .then(function () {
                        areaBroker.getListArea().empty();
                    })
                    .catch(function (err) {
                        self.trigger('error', err);
                    });
            })
            .setTemplate(listTpl)
            .init(config);
    }

    return resultsListFactory;
});
