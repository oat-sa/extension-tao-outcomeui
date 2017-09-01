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
    'taoOutcomeUi/component/results/pluginsLoader',
    'taoOutcomeUi/component/results/list'
], function ($, _, module, loggerFactory, urlHelper, binder, loadingBar, feedback, resultsPluginsLoader, resultsListFactory) {
    'use strict';

    var logger = loggerFactory('controller/inspectResults');

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
                        resultsPluginsLoader.remove(plugin.module);
                    } else {
                        resultsPluginsLoader.add(plugin);
                    }
                }
            });

            if ($container.length) {
                resultsPluginsLoader.load()
                    .then(function () {
                        resultsListFactory(listConfig, resultsPluginsLoader.getPlugins())
                            .on('error', reportError)
                            .on('success', function (message) {
                                feedback().success(message);
                            })
                            .before('loading', function() {
                                loadingBar.start();
                            })
                            .after('loaded', function () {
                                loadingBar.stop();
                            })
                            .render($('.inspect-results-grid', $container));
                    })
                    .catch(reportError);
            }


            binder.register('download_csv', function (item) {
                $.fileDownload(this.url, {
                    httpMethod: 'GET',
                    data: {uri: item.uri}
                });
            });
        }
    };
});
