/* jshint node:true */
module.exports = function(grunt) {
	'use strict';

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		exec: {
			composerInstall: {
				cmd: 'composer install'
			}
		},

		phpunit: {
			classes: {
				dir: 'tests/'
			},
			options: {
				bin: 'vendor/bin/phpunit',
				colors: true
			}
		}
	});

	grunt.registerTask('build', ['exec:composerInstall']);
	grunt.registerTask('test', ['build', 'phpunit']);


	grunt.loadNpmTasks('grunt-exec');
	grunt.loadNpmTasks('grunt-phpunit');
};