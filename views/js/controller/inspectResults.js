/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'module',
    'core/logger',
    'util/url',
    'layout/actions/binder',
    'layout/loading-bar',
    'ui/feedback',
    'taoOutcomeUi/component/results/areaBroker',
    'taoOutcomeUi/component/results/loader',
    'taoOutcomeUi/component/results/list'
], function ($, _, module, loggerFactory, urlHelper, binder, loadingBar, feedback, areaBrokerFactory, pluginLoader, resultsListFactory) {
    'use strict';

    var logger = loggerFactory('controller/inspectResults');

    /**
     * Set up the areaBroker mapping from the actual DOM
     * @returns {areaBroker} already mapped
     */
    function loadAreaBroker($container) {
        return areaBrokerFactory($container, {
            'list': $('.inspect-results-grid', $container)
        });
    }

    /**
     * Take care of errors
     * @param err
     */
    function reportError(err) {
        loadingBar.stop();

        logger.error(err);

        if (err instanceof Error) {
            feedback().error(err.message);
        }
    }

    /**
     * @exports taoOutcomeUi/controller/resultTable
     */
    return {

        /**
         * Controller entry point
         */
        start: function () {
            var config = module.config() || {};
            var $container = $('#inspect-result');
            var classUri = $container.data('uri');
            var listConfig = {
                dataUrl: urlHelper.route('getResults', 'Results', 'taoOutcomeUi', {
                    classUri: classUri
                }),
                model: config.dataModel,
                classUri: classUri
            };

            loadingBar.start();

            _.forEach(config.plugins, function (plugin) {
                if (plugin && plugin.module) {
                    if (plugin.exclude) {
                        pluginLoader.remove(plugin.module);
                    } else {
                        pluginLoader.add(plugin);
                    }
                }
            });

            pluginLoader.load()
                .then(function () {
                    resultsListFactory(listConfig, loadAreaBroker($container), pluginLoader.getPlugins())
                        .on('error', reportError)
                        .on('success', function (message) {
                            feedback().success(message);
                        })
                        .on('init', function () {
                            this.render();
                        })
                        .on('render', function () {
                            loadingBar.stop();
                        })
                        .init();
                })
                .catch(reportError);

            binder.register('download_csv', function (item) {
                $.fileDownload(this.url, {
                    httpMethod: 'GET',
                    data: {uri: item.uri}
                });
            });
        }
    };
});
