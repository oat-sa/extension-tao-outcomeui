/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'module',
    'core/logger',
    'util/url',
    'layout/actions/binder',
    'layout/loading-bar',
    'ui/feedback',
    'core/taskQueue/taskQueue',
    'taoOutcomeUi/component/results/pluginsLoader',
    'taoOutcomeUi/component/results/list',
    'ui/taskQueueButton/treeButton'
], function ($, _, __, module, loggerFactory, urlHelper, binder, loadingBar, feedback, taskQueue, resultsPluginsLoader, resultsListFactory, treeTaskButtonFactory) {
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
                searchable: config.searchable,
                classUri: classUri
            };
            var taskButton;

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
            } else {
                loadingBar.stop();
            }

            taskButton = treeTaskButtonFactory({
                replace : true,
                icon : 'export',
                label : __('Export CSV'),
                taskQueue : taskQueue
            }).render($('#results-csv-export'));

            binder.register('export_csv', function remove(actionContext){
                var data = _.pick(actionContext, ['uri', 'classUri', 'id']);
                var uniqueValue = data.uri || data.classUri || '';
                taskButton.setTaskConfig({
                    taskCreationUrl : this.url,
                    taskCreationData : {uri : uniqueValue}
                }).start();
            });
        }
    };
});
