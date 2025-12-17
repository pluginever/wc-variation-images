/**
 * External dependencies
 */
const baseConfig = require( '@byteever/scripts/config/webpack.config' );

module.exports = {
	...baseConfig,
	entry: {
		...baseConfig.entry,
		'css/admin': './assets/src/css/admin.scss',
		'css/frontend': './assets/src/css/frontend.scss',
		'css/black-friday': './assets/src/css/black-friday.scss',
		'js/admin': './assets/src/js/admin.js',
		'js/frontend': './assets/src/js/frontend.js'
	},
};
