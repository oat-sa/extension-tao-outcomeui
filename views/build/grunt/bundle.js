module.exports = function(grunt) { 

    var requirejs   = grunt.config('requirejs') || {};
    var clean       = grunt.config('clean') || {};
    var copy        = grunt.config('copy') || {};

    var root        = grunt.option('root');
    var libs        = grunt.option('mainlibs');
    var ext         = require(root + '/tao/views/build/tasks/helpers/extensions')(grunt, root);

    /**
     * Remove bundled and bundling files
     */
    clean.taoresultsbundle = ['output',  root + '/taoOutcomeUi/views/js/controllers.min.js'];
    
    /**
     * Compile tao files into a bundle 
     */
    requirejs.taoresultsbundle = {
        options: {
            baseUrl : '../js',
            dir : 'output',
            mainConfigFile : './config/requirejs.build.js',
            paths : { 'taoOutcomeUi' : root + '/taoOutcomeUi/views/js', 'taoItems' : root + '/taoItems/views/js' },
            modules : [{
                name: 'taoOutcomeUi/controller/routes',
                include : ext.getExtensionsControllers(['taoOutcomeUi']),
                exclude : ['mathJax', 'mediaElement'].concat(libs)
            }]
        }
    };

    /**
     * copy the bundles to the right place
     */
    copy.taoresultsbundle = {
        files: [
            { src: ['output/taoOutcomeUi/controller/routes.js'],  dest: root + '/taoOutcomeUi/views/js/controllers.min.js' },
            { src: ['output/taoOutcomeUi/controller/routes.js.map'],  dest: root + '/taoOutcomeUi/views/js/controllers.min.js.map' }
        ]
    };

    grunt.config('clean', clean);
    grunt.config('requirejs', requirejs);
    grunt.config('copy', copy);

    // bundle task
    grunt.registerTask('taoresultsbundle', ['clean:taoresultsbundle', 'requirejs:taoresultsbundle', 'copy:taoresultsbundle']);
};
