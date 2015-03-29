/* jshint node:true */
module.exports = function( grunt ) {
	'use strict';

	grunt.initConfig({

		// Setting folder templates
		dirs: {
			css:    'assets/css',
			fonts:  'assets/fonts',
			images: 'assets/images',
			js:     'assets/js'
		},

		// Javascript linting with jshint
		jshint: {
			options: {
				jshintrc: '.jshintrc'
			},
			all: [
				'Gruntfile.js',
				'<%= dirs.js %>/*/*/*.js',
				'!<%= dirs.js %>/*/*/*.min.js'
			]
		},

		// Minify .js files
		uglify: {
			options: {
				preserveComments: 'some'
			},
			admin: {
				files: [{
					expand: true,
					cwd: '<%= dirs.js %>/admin/',
					src: [
						'*.js',
						'!*.min.js'
					],
					dest: '<%= dirs.js %>/admin/',
					ext: '.min.js'
				}]
			},
			credit: {
				files: [{
					expand: true,
					cwd: '<%= dirs.js %>/credit-card/',
					src: [
						'*.js',
						'!*.min.js'
					],
					dest: '<%= dirs.js %>/credit-card/',
					ext: '.min.js'
				}]
			},
			debit: {
				files: [{
					expand: true,
					cwd: '<%= dirs.js %>/debit-card/',
					src: [
						'*.js',
						'!*.min.js'
					],
					dest: '<%= dirs.js %>/debit-card/',
					ext: '.min.js'
				}]
			}
		},

		// Compress the css files
		cssmin: {
			dist: {
				expand: true,
				cwd: '<%= dirs.css %>/',
				src: [
					'*.css',
					'!*.min.css'
				],
				dest: '<%= dirs.css %>/',
				ext: '.min.css'
			}
		},

		// Watch changes for assets
		watch: {
			js: {
				files: [
					'<%= dirs.js %>/*/*.js',
					'!<%= dirs.js %>/*/*.min.js'
				],
				tasks: ['jshint', 'uglify']
			},
			css: {
				files: [
					'<%= dirs.css %>/*.css',
					'!<%= dirs.css %>/*.min.css'
				],
				tasks: ['cssmin']
			}
		},

		// Image optimization
		imagemin: {
			dist: {
				options: {
					optimizationLevel: 7,
					progressive: true
				},
				files: [{
					expand: true,
					cwd: './',
					src: 'screenshot-*.png',
					dest: './'
				}]
			}
		}
	});

	// Load NPM tasks to be used here
	grunt.loadNpmTasks( 'grunt-contrib-watch' );
	grunt.loadNpmTasks( 'grunt-contrib-jshint' );
	grunt.loadNpmTasks( 'grunt-contrib-uglify' );
	grunt.loadNpmTasks( 'grunt-contrib-cssmin' );
	grunt.loadNpmTasks( 'grunt-contrib-imagemin' );

	// Register tasks
	grunt.registerTask( 'default', [
		'cssmin',
		'jshint',
		'uglify'
	]);
};
