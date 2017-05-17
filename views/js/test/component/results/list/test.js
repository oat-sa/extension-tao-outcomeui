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
    'core/promise',
    'taoOutcomeUi/component/results/list'
], function ($, Promise, resultsListFactory) {
    'use strict';

    var dataUrl = '../../taoOutcomeUi/views/js/test/component/results/list/data.json';
    var resultsListApi = [
        {title: 'init'},
        {title: 'render'},
        {title: 'destroy'},
        {title: 'refresh'},
        {title: 'addAction'},
        {title: 'getConfig'},
        {title: 'getClassUri'},
        {title: 'getActions'}
    ];


    QUnit.module('resultsList');


    QUnit.test('module', function (assert) {
        var config = {
            classUri: 'http://tao.dev/class#123',
            dataUrl: dataUrl,
            model: []
        };
        var plugins = [];

        QUnit.expect(3);

        assert.equal(typeof resultsListFactory, 'function', "The resultsListFactory module exposes a function");
        assert.equal(typeof resultsListFactory(config, plugins), 'object', "The resultsListFactory factory produces an object");
        assert.notStrictEqual(resultsListFactory(config, plugins), resultsListFactory(config, plugins), "The resultsListFactory factory provides a different object on each call");
    });


    QUnit
        .cases(resultsListApi)
        .test('instance API ', function (data, assert) {
            var config = {
                classUri: 'http://tao.dev/class#123',
                dataUrl: dataUrl,
                model: []
            };
            var plugins = [];
            var instance = resultsListFactory(config, plugins);

            QUnit.expect(1);

            assert.equal(typeof instance[data.title], 'function', 'The resultsListFactory instance exposes a "' + data.title + '" function');
        });


    QUnit.test('resultsList#error', function(assert) {
        var config = {
            classUri: 'http://tao.dev/class#123',
            dataUrl: dataUrl,
            model: []
        };
        var plugins = [];

        QUnit.expect(6);

        assert.throws(function() {
            resultsListFactory();
        }, 'Parameters are mandatory');

        assert.throws(function() {
            resultsListFactory(1, plugins);
        }, 'Need valid config object');

        assert.throws(function() {
            resultsListFactory({}, plugins);
        }, 'The config cannot be empty');

        assert.throws(function() {
            resultsListFactory({
                classUri: config.classUri
            }, plugins);
        }, 'Missing dataUrl and model');

        assert.throws(function() {
            resultsListFactory({
                classUri: config.classUri,
                dataUrl: config.dataUrl
            }, plugins);
        }, 'Missing dataUrl');

        assert.throws(function() {
            resultsListFactory({
                classUri: config.classUri,
                model: config.model
            }, plugins);
        }, 'Missing model');
    });


    QUnit.asyncTest('resultsList.init', function(assert) {
        var config = {
            classUri: 'http://tao.dev/class#123',
            dataUrl: dataUrl,
            model: []
        };
        var plugins = [function() {
            return {
                getName: function getName() {
                    return 'testPlugin';
                },
                init: function init() {
                    assert.ok(true, 'The plugin has been initialized');
                    QUnit.start();
                }
            };
        }];
        var instance;

        QUnit.expect(3);

        instance = resultsListFactory(config, plugins).render();
        assert.deepEqual(instance.getConfig(), config, 'The config should be accessible');
        assert.equal(instance.getClassUri(), config.classUri, 'The class URI should be accessible');
    });


    QUnit.asyncTest('resultsList.init#error', function(assert) {
        var config = {
            classUri: 'http://tao.dev/class#123',
            dataUrl: dataUrl,
            model: []
        };
        var plugins = [function() {
            return {
                getName: function getName() {
                    return 'testPlugin';
                },
                init: function init() {
                    assert.ok(true, 'The plugin has been initialized');
                    return Promise.reject();
                }
            };
        }];
        QUnit.expect(2);

        resultsListFactory(config, plugins)
            .on('init', function() {
                assert.ok(false, 'The instance should not be initialized');
                QUnit.start();
            })
            .on('error', function() {
                assert.ok(true, 'The plugin has broken the init');
                QUnit.start();
            })
            .render();
    });


    QUnit.asyncTest('resultsList.addAction', function(assert) {
        var config = {
            classUri: 'http://tao.dev/class#123',
            dataUrl: dataUrl,
            model: []
        };
        var actions = [{
            id: 'action1',
            label: 'action1',
            action: function action1() {}
        }, {
            id: 'action2',
            label: 'action2',
            action: function action2() {}
        }, {
            id: 'action3',
            label: 'action3',
            icon: 'action3',
            action: function action3() {}
        }];
        var plugins = [function(instance) {
            return {
                getName: function getName() {
                    return 'testPlugin';
                },
                init: function init() {
                    assert.ok(true, 'The plugin has been initialized');

                    instance.addAction(actions[0].id, actions[0].action);
                    instance.addAction(actions[1].id, {
                        action: actions[1].action
                    });
                    instance.addAction(actions[2]);

                    assert.deepEqual(instance.getActions(), actions, 'The actions have been registered');
                    assert.ok(true, 'The instance has been initialized');
                    QUnit.start();
                }
            };
        }];

        QUnit.expect(3);
        resultsListFactory(config, plugins).render();
    });


    QUnit.asyncTest('resultsList.render', function(assert) {
        var $container = $('#fixture-1');
        var expectedId = "http:\/\/tao.dev\/tao.rdf#i14938999025623262";
        var expectedName = "billy.laporte";
        var config = {
            classUri: 'http://tao.dev/class#123',
            dataUrl: dataUrl,
            model: [{
                id: 'name',
                label: 'Name'
            }]
        };

        var plugins = [function(instance) {
            return {
                getName: function getName() {
                    return 'testPlugin';
                },
                init: function init() {
                    instance.addAction('action1', function action1(id) {
                        assert.ok(true, 'The action has been activated');
                        assert.equal(id, expectedId, 'The right ID has been sent');
                        QUnit.start();
                    });
                    assert.ok(true, 'The plugin has been initialized');
                },
                render: function render() {
                    assert.ok(true, 'The plugin has been rendered');
                }
            };
        }];
        QUnit.expect(12);

        assert.equal($container.children().length, 0, 'There is nothing in the container');

        resultsListFactory(config, plugins)
            .on('render', function() {
                assert.ok(true, 'The instance has been rendered');
            })
            .on('loaded', function() {
                assert.ok(true, 'The data has been loaded');
                assert.equal($container.find('table').length, 1, 'The container has a table');
                assert.equal($container.find('tr[data-item-identifier="' + expectedId + '"]').length, 1, 'The expected line has been rendered');
                assert.equal($container.find('tr[data-item-identifier="' + expectedId + '"] td.name').text().trim(), expectedName, 'The right content has been rendered');
                assert.equal($container.find('tr[data-item-identifier="' + expectedId + '"] button').length, 1, 'There is an action');
                assert.equal($container.find('tr[data-item-identifier="' + expectedId + '"] button').text().trim(), 'action1', 'The action contains the right label');
                $container.find('tr[data-item-identifier="' + expectedId + '"] button').click();
            })
            .render($container);
    });


    QUnit.asyncTest('resultsList.render#error', function(assert) {
        var config = {
            classUri: 'http://tao.dev/class#123',
            dataUrl: dataUrl,
            model: [{
                id: 'name',
                label: 'Name'
            }]
        };
        var $container = $('#fixture-2');
        var plugins = [function() {
            return {
                getName: function getName() {
                    return 'testPlugin';
                },
                init: function init() {
                    assert.ok(true, 'The plugin has been initialized');
                },
                render: function render() {
                    assert.ok(true, 'The plugin has been rendered');
                    return Promise.reject();
                }
            };
        }];
        QUnit.expect(3);

        resultsListFactory(config, plugins)
            .on('render', function() {
                assert.ok(false, 'The instance should not be rendered');
                QUnit.start();
            })
            .on('loaded', function() {
                assert.ok(true, 'The data should not be loaded');
                QUnit.start();
            })
            .on('error', function() {
                assert.ok(true, 'The plugin has broken the render');
                QUnit.start();
            })
            .render($container);
    });


    QUnit.asyncTest('resultsList.refresh', function(assert) {
        var config = {
            classUri: 'http://tao.dev/class#123',
            dataUrl: dataUrl,
            model: [{
                id: 'name',
                label: 'Name'
            }]
        };
        var $container = $('#fixture-3');

        var plugins = [function() {
            return {
                getName: function getName() {
                    return 'testPlugin';
                },
                init: function init() {
                    assert.ok(true, 'The plugin has been initialized');
                },
                render: function render() {
                    assert.ok(true, 'The plugin has been rendered');
                }
            };
        }];
        var loads = 0;

        QUnit.expect(5);

        resultsListFactory(config, plugins)
            .on('render', function() {
                assert.ok(true, 'The instance has been rendered');
            })
            .on('loaded', function() {
                assert.ok(true, 'The data has been loaded');
                if (++loads === 2) {
                    QUnit.start();
                } else {
                    this.refresh();
                }
            })
            .render($container);
    });


    QUnit.asyncTest('resultsList.destroy', function(assert) {
        var config = {
            classUri: 'http://tao.dev/class#123',
            dataUrl: dataUrl,
            model: [{
                id: 'name',
                label: 'Name'
            }]
        };
        var $container = $('#fixture-4');
        var plugins = [function() {
            return {
                getName: function getName() {
                    return 'testPlugin';
                },
                init: function init() {
                    assert.ok(true, 'The plugin has been initialized');
                },
                render: function render() {
                    assert.ok(true, 'The plugin has been rendered');
                },
                destroy: function destroy() {
                    assert.ok(true, 'The plugin has been destroyed');
                }
            };
        }];
        QUnit.expect(9);

        assert.equal($container.children().length, 0, 'There is nothing in the container');

        resultsListFactory(config, plugins)
            .on('render', function() {
                assert.ok(true, 'The instance has been rendered');
            })
            .on('loaded', function() {
                assert.ok(true, 'The data has been loaded');
                assert.equal($container.find('table').length, 1, 'The container has a table');
                this.destroy();
            })
            .on('destroy', function() {
                assert.ok(true, 'The instance has been destroyed');
                assert.equal($container.children().length, 0, 'There is nothing in the container');
                QUnit.start();
            })
            .render($container);
    });


    QUnit.asyncTest('resultsList.destroy#error', function(assert) {
        var config = {
            classUri: 'http://tao.dev/class#123',
            dataUrl: dataUrl,
            model: [{
                id: 'name',
                label: 'Name'
            }]
        };
        var $container = $('#fixture-5');
        var plugins = [function() {
            return {
                getName: function getName() {
                    return 'testPlugin';
                },
                init: function init() {
                    assert.ok(true, 'The plugin has been initialized');
                },
                render: function render() {
                    assert.ok(true, 'The plugin has been rendered');
                },
                destroy: function destroy() {
                    assert.ok(true, 'The plugin has been destroyed');
                    return Promise.reject();
                }
            };
        }];
        QUnit.expect(9);

        assert.equal($container.children().length, 0, 'There is nothing in the container');

        resultsListFactory(config, plugins)
            .on('render', function() {
                assert.ok(true, 'The instance has been rendered');
            })
            .on('loaded', function() {
                assert.ok(true, 'The data has been loaded');
                assert.equal($container.find('table').length, 1, 'The container has a table');
                this.destroy();
            })
            .on('error', function() {
                assert.ok(true, 'An error should be triggered');
                QUnit.start();
            })
            .on('destroy', function() {
                assert.ok(true, 'The instance should be destroyed');
            })
            .render($container);
    });

});
