module.exports = function(grunt) {
	'use strict';
	grunt.initConfig({
		jshint: {
			options: {
				jshintrc: 'js/.jshintrc'
			},
			src: ['Gruntfile.js', 'app/module/**/*.js']
		},
		nggettext_compile: {
            iphone: {
              files: {
                './app/language/origin/en.js': ['./app/language/extract/en.po'],
                './app/language/origin/fr.js': ['./app/language/extract/fr.po']
              }
            },
          },
		nggettext_extract: {
            iphone: {
              files: {
                './app/language/extract/iphone.pot': [
                    './app/templates/*/*/*/*.html',
                    './app/templates/*/*/*.html',
                    './app/templates/*/*.html',
                    './app/module/*/*/*.js',
                    './app/module/*/*.js',
                    './app/global/*/*/*.js',
                    './app/global/*/*.js',
                    './app/global/*.js'
                    ]
              }
            },
          },
		requirejs: {
			iphone: {
				options: {
					baseUrl: "./",
                    name: "app",
                    out: "./dist.iphone/js/main.js",
					paths: {
						app: "./app/settings/iphone",
					},
					optimize: "none",
					mainConfigFile: "./app/settings/iphone.js",
				}
			},
			ipad: {
				options: {
					baseUrl: "./",
                    name: "app",
                    out: "./dist.ipad/js/main.js",
					paths: {
						app: "./app/settings/ipad",
					},
					optimize: "none",
					mainConfigFile: "./app/settings/ipad.js",
				}
			},
			android: {
				options: {
					baseUrl: "./",
                    name: "app",
                    out: "./dist.android/js/main.js",
					paths: {
						app: "./app/settings/android",
					},
					optimize: "none",
					mainConfigFile: "./app/settings/android.js",
				}
			},
        },
        sass: {
            iphone: {
                options: {
                    style: 'expanded',
                },
                files: {
                    "./dist.iphone/css/init.css": "./app/themes/iphone/init.scss",
                    "./dist.iphone/css/main.css": "./app/themes/iphone/ionic.scss",
                },
                // optimize: true,
            },
            ipad: {
                options: {
                    style: 'expanded',
                },
                files: {
                    "./dist.ipad/css/init.css": "./app/themes/ipad/init.scss",
                    "./dist.ipad/css/main.css": "./app/themes/ipad/ionic.scss",
                }
            },
            android: {
                options: {
                    style: 'expanded',
                },
                files: {
                    "./dist.android/css/init.css": "./app/themes/android/init.scss",
                    "./dist.android/css/main.css": "./app/themes/android/ionic.scss",
                }
            }

        }
	});

	// grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-sass');
	grunt.loadNpmTasks('grunt-contrib-requirejs');
	grunt.loadNpmTasks('grunt-angular-gettext');
	
	// grunt.registerTask('build', ['jshint', 'requirejs']);
	grunt.registerTask('build', ['requirejs', 'sass']);
	grunt.registerTask('iphone', ['requirejs:iphone','sass:iphone']);
	grunt.registerTask('ipad', ['requirejs:ipad', 'sass:ipad']);
	grunt.registerTask('android', ['requirejs:android', 'sass:android']);
	// grunt.registerTask('default', ['jshint']);
};
