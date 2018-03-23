module.exports = function (grunt) {
    'use strict';

    var sass    = grunt.config('sass') || {};
    var watch   = grunt.config('watch') || {};
    var notify  = grunt.config('notify') || {};
    var root    = grunt.option('root') + '/taoOutcomeUi/views/';

    // Override include paths
    sass.taooutcomeui = {
        options : {},
        files : {}
    };

    //files goes heres
    sass.taooutcomeui.files[root + 'css/icon.css'] = root + 'scss/icon.scss';
    sass.taooutcomeui.files[root + 'css/result.css'] = root + 'scss/result.scss';
    sass.taooutcomeui.files[root + 'css/resultsMonitoring.css'] = root + 'scss/resultsMonitoring.scss';


    watch.taooutcomeuisass = {
        files : [root + 'scss/**/*.scss'],
        tasks : ['sass:taoqtiitem', 'notify:taoqtiitemsass'],
        options : {
            debounceDelay : 1000
        }
    };

    notify.taooutcomeuisass = {
        options: {
            title: 'Grunt SASS',
            message: 'SASS files compiled to CSS'
        }
    };

    grunt.config('sass', sass);
    grunt.config('watch', watch);
    grunt.config('notify', notify);

    //register an alias for main build
    grunt.registerTask('taooutcomeuisass', ['sass:taooutcomeui']);
};
