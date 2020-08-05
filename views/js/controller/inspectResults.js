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
    'core/dataProvider/request',
    'util/url',
    'layout/actions/binder',
    'layout/loading-bar',
    'ui/feedback',
    'ui/taskQueue/taskQueue',
    'taoOutcomeUi/component/results/pluginsLoader',
    'taoOutcomeUi/component/results/list',
    'ui/taskQueueButton/treeButton'
], function ($, _, __, module, loggerFactory, request, urlHelper, binder, loadingBar, feedback, taskQueue, resultsPluginsLoader, resultsListFactory, treeTaskButtonFactory) {
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
            var taskButtonExportSQL;

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

            if ($('#results-sql-export').length) {
                taskButtonExportSQL = treeTaskButtonFactory({
                    replace: true,
                    icon: 'export',
                    label: __('Export SQL'),
                    taskQueue: taskQueue
                }).render($('#results-sql-export'));

                binder.register('export_sql', function remove(actionContext) {
                    var data = _.pick(actionContext, ['uri', 'classUri', 'id']);
                    var uniqueValue = data.uri || data.classUri || '';
                    taskButtonExportSQL.setTaskConfig({
                        taskCreationUrl: this.url,
                        taskCreationData: {uri: uniqueValue}
                    }).start();
                });
            }

            binder.register('open_url', function(actionContext) {
                const data = _.pick(actionContext, ['uri', 'classUri', 'id']);

                request(this.url, data, 'POST')
                    .then(response => {
                        const url = response.url;

                        if (url) {
                            window.open(url, '_blank');
                        } else {
                            feedback().info(__('The URL does not exist.'));
                        }
                    })
                    .catch(reportError);
            });
        }
    };
});
