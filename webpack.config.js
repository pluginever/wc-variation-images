/**
 * External dependencies
 */
const path = require( 'path' );
const baseConfig = require( '@byteever/scripts/config/webpack.config' );
const CopyWebpackPlugin = require( 'copy-webpack-plugin' );

module.exports = {
	...baseConfig,
	entry: {
		...baseConfig.entry,
		'css/admin': './assets/src/css/admin.scss',
		'css/frontend': './assets/src/css/frontend.scss',
		'js/admin': './assets/src/js/admin.js',
		'js/frontend': './assets/src/js/frontend.js',
	},
	plugins: [
		...( baseConfig.plugins || [] ),
		new CopyWebpackPlugin( {
			patterns: [
				// Vendor JS -- copy without webpack processing to preserve globals (Swiper, fancybox).
				{ from: 'assets/src/js/slider.js', to: path.resolve( __dirname, 'assets/build/js/slider.js' ) },
				{ from: 'assets/src/js/fancybox.js', to: path.resolve( __dirname, 'assets/build/js/fancybox.js' ) },
				// Vendor CSS -- copy as-is.
				{ from: 'assets/src/css/slider/slider.css', to: path.resolve( __dirname, 'assets/build/css/slider.css' ) },
				{ from: 'assets/src/css/slider/fancybox.css', to: path.resolve( __dirname, 'assets/build/css/fancybox.css' ) },
			],
		} ),
	],
};
