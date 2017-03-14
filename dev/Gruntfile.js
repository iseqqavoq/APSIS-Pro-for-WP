module.exports = function ( grunt ) {

    // load all grunt tasks matching the ['grunt-*', '@*/grunt-*'] patterns
    require( 'load-grunt-tasks' )( grunt );
    require( 'time-grunt' )( grunt );
    

    // Project configuration.
    grunt.initConfig( {
        pkg: grunt.file.readJSON( 'package.json' ),
        import: {
            options: {},
            dev: {
                files: {
                        '../js/backend.min.js': 'js/backend.js',
                        '../js/frontend.min.js': 'js/frontend.js',
                      }
            }
        },
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
        },
        watch: {
            js: {
                files: 'js/**/*.js',
                tasks: [ 'import:dev' ],
                options: {
                    interrupt: true
                }
            }
        }
    } );

    // Default task(s).
    grunt.registerTask( 'dist', [ 'uglify' ] );
    grunt.registerTask( 'default', [ 'import', 'watch' ] );

};