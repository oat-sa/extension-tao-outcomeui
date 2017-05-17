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
    'lodash',
    'uri',
    'core/eventifier',
    'core/promise',
    'ui/datatable'
], function (_, uri, eventifier, Promise) {
    'use strict';

    /**
     * Component that lists all the results entry points for a particular delivery
     *
     * @param {Object} config
     * @param {String} config.classUri
     * @param {String} config.dataUrl
     * @param {Object} config.model
     * @param {Object} areaBroker
     * @param {Array} pluginFactories
     * @returns {resultsList}
     */
    function resultsListFactory(config, areaBroker, pluginFactories) {
        var resultsList;
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
        if (!areaBroker || !_.isFunction(areaBroker.getListArea)) {
            throw new TypeError('The areaBroker is required');
        }

        classUri = uri.decode(config.classUri);

        /**
         *
         * @typedef {resultsList}
         */
        resultsList = eventifier({

            /**
             * Initializes the component
             * @returns {resultsList} chains
             * @fires resultsList#init - once initialized
             * @fires resultsList#error - if something went wrong
             */
            init: function init() {
                var self = this;

                //instantiate the plugins first
                _.forEach(pluginFactories, function (pluginFactory) {
                    var plugin = pluginFactory(self, areaBroker);
                    plugins[plugin.getName()] = plugin;
                });

                //initialize all the plugins
                pluginRun('init')
                    .then(function () {

                        /**
                         * @event resultsList#init the initialization is done
                         * @param {Object} item - the loaded item
                         */
                        self.trigger('init', self.item);
                    })
                    .catch(function (err) {
                        self.trigger('error', err);
                    });

                return this;
            },

            /**
             * Renders the component
             *
             * @returns {resultsList} chains
             * @fires resultsList#render - once everything is in place
             * @fires resultsList#load - each time the list is refreshed
             * @fires resultsList#error - if something went wrong
             */
            render: function render() {
                var self = this;

                areaBroker.getListArea()
                    .empty()
                    .data('ui.datatable', null)
                    .off('.datatable')
                    .on('load.datatable', function() {
                        self.trigger('load');
                    })
                    .datatable({
                        url  : config.dataUrl,
                        model : config.model,
                        actions : actions
                    });

                pluginRun('render')
                    .then(function () {
                        self.trigger('render');
                    })
                    .catch(function (err) {
                        self.trigger('error', err);
                    });

                return this;
            },

            /**
             * Refreshes the list
             * @returns {resultsList} chains
             */
            refresh: function refresh() {
                areaBroker.getListArea().datatable('refresh');
                return this;
            },

            /**
             * Cleans up everything and destroys the component
             * @returns {resultsList} chains
             * @fires resultsList#destroy - once everything has been destroyed
             * @fires resultsList#error - if something went wrong
             */
            destroy: function destroy() {
                var self = this;

                pluginRun('destroy')
                    .then(function () {
                        areaBroker.getListArea().empty();

                        self.trigger('destroy');
                    })
                    .catch(function (err) {
                        self.trigger('error', err);
                    });
                return this;
            },

            /**
             * Add a line action
             * @param {String|Object} name
             * @param {Function|Object} [action]
             * @returns {resultsList} chains
             * @fires resultsList#addaction - notify the action add
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

                this.trigger('addaction', descriptor);

                return this;
            },

            /**
             * Gives an access to the config
             * @returns {Object}
             */
            getConfig: function getConfig() {
                return config;
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

        return resultsList;
    }

    return resultsListFactory;
});
