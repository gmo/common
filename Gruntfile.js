/* jshint node:true */
module.exports = function(grunt) {
	'use strict';

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		exec: {
			composerInstall: {
				cmd: 'composer install'
			},
			runPhpUnitTests: {
				cmd: './vendor/bin/phpunit --tap --log-tap ./tests/tap-files/phpunit.tap ./tests/UnitTest'
			}
		}
	});

	grunt.registerTask('build', ['exec:composerInstall']);
	grunt.registerTask('test', ['build', 'exec:runPhpUnitTests']);

	grunt.loadNpmTasks('grunt-exec');
	grunt.loadNpmTasks('grunt-phpunit');
};