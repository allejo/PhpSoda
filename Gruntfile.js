module.exports = function(grunt) {
    require('load-grunt-tasks')(grunt);

    grunt.initConfig({
        phpunit: {
            classes: {
                dir: 'tests/'
            },
            options: {
                bin: 'vendor/bin/phpunit',
                colors: true
            }
        },
        shell: {
            autoload: {
                command: 'vendor/bin/phpab -o src/autoload.php -b src composer.json'
            }
        },
        watch: {
            phpunit: {
                files: [ 'tests/*Test.php' ],
                tasks: [ 'phpunit' ]
            }
        }
    });

    grunt.registerTask('default', [ 'shell:autoload', 'phpunit' ]);
};