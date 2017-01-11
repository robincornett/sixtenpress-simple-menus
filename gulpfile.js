'use strict';

var projectName  = 'sixtenpress-simple-menus',
	version      = '1.2.4',
	destination  = '../../build',
	gulp         = require( 'gulp' ),
	zip          = require( 'gulp-zip' ),
	config       = {
		sassPath: 'sass',
		bowerDir: 'bower_components',
		sixten  : '../sixtenpress'
	},
	buildInclude = [
		'**',

		// exclude:
		'!node_modules/**/*',
		'!bower_components/**/*',
		'!sass/**/*',
		'!dist/**/*',
		'!node_modules',
		'!bower_components',
		'!sass',
		'!dist',
		'!gulpfile.js',
		'!package.json',
		'!bower.json'
	];

gulp.task( 'assets', function () {
	gulp.src( config.sixten + '/includes/common/**' )
		.pipe( gulp.dest( 'includes/common' ) );
} );

gulp.task( 'zip', function () {
	return gulp.src( buildInclude, { base: '../' } )
		.pipe( zip( projectName + '.' + version + '.zip' ) )
		.pipe( gulp.dest( destination ) );
} );

gulp.task( 'build', [ 'zip' ] );
