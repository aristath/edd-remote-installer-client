/* global module */
module.exports = function( grunt ) {

	grunt.initConfig( {

		// Compile CSS
		sass: {
			dist: {
				files: {
					'assets/css/styles.css': 'assets/scss/styles.scss'
				}
			}
		},

		// Watch task (run with "grunt watch")
		watch: {
			css: {
				files: [
					'assets/scss/*.scss'
				],
				tasks: [ 'sass' ]
			}
		}
	} );

	grunt.loadNpmTasks( 'grunt-contrib-sass' );
	grunt.loadNpmTasks( 'grunt-contrib-watch' );
	grunt.registerTask( 'default', [ 'sass:dist' ] );
};
