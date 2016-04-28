module.exports = function ( grunt ) {

    // load all grunt tasks matching the ['grunt-*', '@*/grunt-*'] patterns
    require( 'load-grunt-tasks' )( grunt );
    require( 'time-grunt' )( grunt );

    // Project configuration.
    grunt.initConfig( {
        pkg: grunt.file.readJSON( 'package.json' ),
        uglify: {
            options: {
                banner: '/*! <%= pkg.name %> - v<%= pkg.version %> - ' +
                '<%= grunt.template.today("yyyy-mm-dd") %> */'
            },
            js: {
                files: {
                    '../js/backend.min.js': ['js/backend.js'],
                    '../js/frontend.min.js': ['js/frontend.js']
                }
            }
        }
    } );

    // Default task(s).
    grunt.registerTask( 'dist', [ 'uglify' ] );

};