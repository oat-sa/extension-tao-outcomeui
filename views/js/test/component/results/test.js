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
    'taoOutcomeUi/component/results'
], function ($, Promise, resultsListFactory) {
    'use strict';

    var resultsListApi = [
        {title: 'init'},
        {title: 'render'},
        {title: 'refresh'},
        {title: 'destroy'},
        {title: 'addAction'},
        {title: 'getConfig'},
        {title: 'getClassUri'},
        {title: 'getActions'}
    ];


    QUnit.module('resultsList');


    QUnit.test('module', function (assert) {
        var config = {
            classUri: 'http://tao.dev/class#123',
            dataUrl: 'http://tao.dev/data',
            model: []
        };
        var areaBroker = {
            getListArea: function() {}
        };
        var plugins = [];

        QUnit.expect(3);

        assert.equal(typeof resultsListFactory, 'function', "The resultsListFactory module exposes a function");
        assert.equal(typeof resultsListFactory(config, areaBroker, plugins), 'object', "The resultsListFactory factory produces an object");
        assert.notStrictEqual(resultsListFactory(config, areaBroker, plugins), resultsListFactory(config, areaBroker, plugins), "The resultsListFactory factory provides a different object on each call");
    });


    QUnit
        .cases(resultsListApi)
        .test('instance API ', function (data, assert) {
            var config = {
                classUri: 'http://tao.dev/class#123',
                dataUrl: 'http://tao.dev/data',
                model: []
            };
            var areaBroker = {
                getListArea: function() {}
            };
            var plugins = [];
            var instance = resultsListFactory(config, areaBroker, plugins);

            QUnit.expect(1);

            assert.equal(typeof instance[data.title], 'function', 'The resultsListFactory instance exposes a "' + data.title + '" function');
        });


    QUnit.test('resultsList#error', function(assert) {
        var config = {
            classUri: 'http://tao.dev/class#123',
            dataUrl: 'http://tao.dev/data',
            model: []
        };
        var areaBroker = {
            getListArea: function() {}
        };
        var plugins = [];

        QUnit.expect(8);

        assert.throws(function() {
            resultsListFactory();
        }, 'Parameters are mandatory');

        assert.throws(function() {
            resultsListFactory(1, areaBroker, plugins);
        }, 'Need valid config object');

        assert.throws(function() {
            resultsListFactory({}, areaBroker, plugins);
        }, 'The config cannot be empty');

        assert.throws(function() {
            resultsListFactory({
                classUri: config.classUri
            }, areaBroker, plugins);
        }, 'Missing dataUrl and model');

        assert.throws(function() {
            resultsListFactory({
                classUri: config.classUri,
                dataUrl: config.dataUrl
            }, areaBroker, plugins);
        }, 'Missing dataUrl');

        assert.throws(function() {
            resultsListFactory({
                classUri: config.classUri,
                model: config.model
            }, areaBroker, plugins);
        }, 'Missing model');

        assert.throws(function() {
            resultsListFactory(config, null, plugins);
        }, 'The area broker is mandatory');

        assert.throws(function() {
            resultsListFactory(config, {}, plugins);
        }, 'The area broker must provide the getListArea() method');
    });


    QUnit.asyncTest('resultsList.init', function(assert) {
        var config = {
            classUri: 'http://tao.dev/class#123',
            dataUrl: 'http://tao.dev/data',
            model: []
        };
        var areaBroker = {
            getListArea: function() {}
        };
        var plugins = [function() {
            return {
                getName: function getName() {
                    return 'testPlugin';
                },
                init: function init() {
                    assert.ok(true, 'The plugin has been initialized');
                }
            };
        }];
        var instance = resultsListFactory(config, areaBroker, plugins);

        QUnit.expect(5);

        instance.on('init', function() {
            assert.ok(true, 'The instance has been initialized');
            QUnit.start();
        });

        assert.equal(instance.init(), instance, 'The init method returns the instance');

        assert.deepEqual(instance.getConfig(), config, 'The config should be accessible');
        assert.equal(instance.getClassUri(), config.classUri, 'The class URI should be accessible');
    });


    QUnit.asyncTest('resultsList.init#error', function(assert) {
        var config = {
            classUri: 'http://tao.dev/class#123',
            dataUrl: 'http://tao.dev/data',
            model: []
        };
        var areaBroker = {
            getListArea: function() {}
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
        var instance = resultsListFactory(config, areaBroker, plugins);

        QUnit.expect(2);

        instance
            .on('init', function() {
                assert.ok(false, 'The instance should not be initialized');
                QUnit.start();
            })
            .on('error', function() {
                assert.ok(true, 'The plugin has broken the init');
                QUnit.start();
            })
            .init();
    });


    QUnit.asyncTest('resultsList.addAction', function(assert) {
        var config = {
            classUri: 'http://tao.dev/class#123',
            dataUrl: 'http://tao.dev/data',
            model: []
        };
        var areaBroker = {
            getListArea: function() {}
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
        var plugins = [function() {
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
                }
            };
        }];
        var instance = resultsListFactory(config, areaBroker, plugins);

        QUnit.expect(18);

        instance
            .on('addaction', function(descriptor) {
                assert.ok(true, 'An action has been added');
                assert.equal(typeof descriptor, 'object', 'The descriptor is provided');
                assert.equal(typeof descriptor.id, 'string', 'The identifier is provided');
                assert.equal(typeof descriptor.label, 'string', 'The label is provided');
                assert.equal(typeof descriptor.action, 'function', 'The action is provided');
            })
            .on('init', function() {
                assert.ok(true, 'The instance has been initialized');
                QUnit.start();
            })
            .init();

        assert.deepEqual(instance.getActions(), actions, 'The actions have been registered');
    });


    QUnit.asyncTest('resultsList.render', function(assert) {
        var config = {
            classUri: 'http://tao.dev/class#123',
            dataUrl: '../../taoOutcomeUi/views/js/test/component/results/data.json',
            model: [{
                id: 'name',
                label: 'Name'
            }]
        };
        var $container = $('#fixture-1');
        var areaBroker = {
            getListArea: function() {
                return $container;
            }
        };
        var plugins = [function() {
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
        var expectedId = "http:\/\/tao.dev\/tao.rdf#i14938999025623262";
        var expectedName = "billy.laporte";
        var instance = resultsListFactory(config, areaBroker, plugins);

        QUnit.expect(13);

        assert.equal($container.children().length, 0, 'There is nothing in the container');

        instance
            .on('init', function() {
                assert.ok(true, 'The instance has been initialized');
                instance.render();
            })
            .on('render', function() {
                assert.ok(true, 'The instance has been rendered');
            })
            .on('load', function() {
                assert.ok(true, 'The data has been loaded');
                assert.equal($container.find('table').length, 1, 'The container has a table');
                assert.equal($container.find('tr[data-item-identifier="' + expectedId + '"]').length, 1, 'The expected line has been rendered');
                assert.equal($container.find('tr[data-item-identifier="' + expectedId + '"] td.name').text().trim(), expectedName, 'The right content has been rendered');
                assert.equal($container.find('tr[data-item-identifier="' + expectedId + '"] button').length, 1, 'There is an action');
                assert.equal($container.find('tr[data-item-identifier="' + expectedId + '"] button').text().trim(), 'action1', 'The action contains the right label');
                $container.find('tr[data-item-identifier="' + expectedId + '"] button').click();
            })
            .init();
    });


    QUnit.asyncTest('resultsList.render#error', function(assert) {
        var config = {
            classUri: 'http://tao.dev/class#123',
            dataUrl: '../../taoOutcomeUi/views/js/test/component/results/data.json',
            model: [{
                id: 'name',
                label: 'Name'
            }]
        };
        var $container = $('#fixture-2');
        var areaBroker = {
            getListArea: function() {
                return $container;
            }
        };
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
        var instance = resultsListFactory(config, areaBroker, plugins);

        QUnit.expect(5);

        instance
            .on('init', function() {
                assert.ok(true, 'The instance has been initialized');
                instance.render();
            })
            .on('render', function() {
                assert.ok(false, 'The instance should not be rendered');
                QUnit.start();
            })
            .on('load', function() {
                assert.ok(true, 'The data should be loaded');
                QUnit.start();
            })
            .on('error', function() {
                assert.ok(true, 'The plugin has broken the render');
            })
            .init();
    });


    QUnit.asyncTest('resultsList.refresh', function(assert) {
        var config = {
            classUri: 'http://tao.dev/class#123',
            dataUrl: '../../taoOutcomeUi/views/js/test/component/results/data.json',
            model: [{
                id: 'name',
                label: 'Name'
            }]
        };
        var $container = $('#fixture-3');
        var areaBroker = {
            getListArea: function() {
                return $container;
            }
        };
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
        var instance = resultsListFactory(config, areaBroker, plugins);
        var loads = 0;

        QUnit.expect(6);

        instance
            .on('init', function() {
                assert.ok(true, 'The instance has been initialized');
                instance.render();
            })
            .on('render', function() {
                assert.ok(true, 'The instance has been rendered');
            })
            .on('load', function() {
                assert.ok(true, 'The data has been loaded');
                if (++loads === 2) {
                    QUnit.start();
                } else {
                    instance.refresh();
                }
            })
            .init();
    });


    QUnit.asyncTest('resultsList.destroy', function(assert) {
        var config = {
            classUri: 'http://tao.dev/class#123',
            dataUrl: '../../taoOutcomeUi/views/js/test/component/results/data.json',
            model: [{
                id: 'name',
                label: 'Name'
            }]
        };
        var $container = $('#fixture-1');
        var areaBroker = {
            getListArea: function() {
                return $container;
            }
        };
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
        var instance = resultsListFactory(config, areaBroker, plugins);

        QUnit.expect(10);

        assert.equal($container.children().length, 0, 'There is nothing in the container');

        instance
            .on('init', function() {
                assert.ok(true, 'The instance has been initialized');
                instance.render();
            })
            .on('render', function() {
                assert.ok(true, 'The instance has been rendered');
            })
            .on('load', function() {
                assert.ok(true, 'The data has been loaded');
                assert.equal($container.find('table').length, 1, 'The container has a table');
                instance.destroy();
            })
            .on('destroy', function() {
                assert.ok(true, 'The instance has been destroyed');
                assert.equal($container.children().length, 0, 'There is nothing in the container');
                QUnit.start();
            })
            .init();
    });


    QUnit.asyncTest('resultsList.destroy#error', function(assert) {
        var config = {
            classUri: 'http://tao.dev/class#123',
            dataUrl: '../../taoOutcomeUi/views/js/test/component/results/data.json',
            model: [{
                id: 'name',
                label: 'Name'
            }]
        };
        var $container = $('#fixture-1');
        var areaBroker = {
            getListArea: function() {
                return $container;
            }
        };
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
        var instance = resultsListFactory(config, areaBroker, plugins);

        QUnit.expect(9);

        assert.equal($container.children().length, 0, 'There is nothing in the container');

        instance
            .on('init', function() {
                assert.ok(true, 'The instance has been initialized');
                instance.render();
            })
            .on('render', function() {
                assert.ok(true, 'The instance has been rendered');
            })
            .on('load', function() {
                assert.ok(true, 'The data has been loaded');
                assert.equal($container.find('table').length, 1, 'The container has a table');
                instance.destroy();
            })
            .on('error', function() {
                assert.ok(true, 'An error should be triggered');
                QUnit.start();
            })
            .on('destroy', function() {
                assert.ok(false, 'The instance should not be fully destroyed');
                QUnit.start();
            })
            .init();
    });

});
