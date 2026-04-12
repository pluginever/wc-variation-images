/**
 * External dependencies
 */
const path = require( 'path' );
const CopyWebpackPlugin = require( 'copy-webpack-plugin' );
const baseConfig = require( '@byteever/scripts/config/webpack.config' );

module.exports = {
	...baseConfig,
	entry: {
		...baseConfig.entry,
		'css/admin': './assets/src/css/admin.scss',
		'css/frontend': './assets/src/css/frontend.scss',
		'js/admin': './assets/src/js/admin.js',
		'js/frontend': './assets/src/js/frontend.js'
	},
	plugins: [
		...baseConfig.plugins,
		new CopyWebpackPlugin( {
			patterns: [
				{
					from: path.resolve( __dirname, 'assets/src/js/slider' ),
					to: path.resolve( __dirname, 'assets/build/js' ),
					noErrorOnMissing: true,
				},
				{
					from: path.resolve( __dirname, 'assets/src/css/slider' ),
					to: path.resolve( __dirname, 'assets/build/css' ),
					noErrorOnMissing: true,
				},
			],
		} ),
	],
};
