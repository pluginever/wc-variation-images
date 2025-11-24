const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const path = require('path');
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');
module.exports = [
	{
		...defaultConfig,
		entry: {
			...defaultConfig.entry(),
			'css/admin': './resources/css/admin.scss',
			'css/frontend': './resources/css/frontend.scss',
			'css/black-friday': './resources/css/black-friday.scss',
			'js/admin': './resources/js/admin.js',
			'js/frontend': './resources/js/frontend.js',
		},
		output: {
			...defaultConfig.output,
			filename: '[name].js',
			path: __dirname + '/assets/',
		},
		module: {
			rules: [
				...defaultConfig.module.rules,
				{
					test: /\.svg$/,
					issuer: /\.(sc|sa|c)ss$/,
					type: 'asset/resource',
					generator: {
						filename: 'images/[name].[hash:8][ext]',
					},
				}
			],
		},
		plugins: [
			...defaultConfig.plugins,
			// Copy files to the assets folder.
			new CopyWebpackPlugin({
				patterns: [
					{
						from: path.resolve(__dirname, 'resources/images'),
						to: path.resolve(__dirname, 'assets/images'),
					},
					{
						from: path.resolve(__dirname, 'resources/js/slider'),
						to: path.resolve(__dirname, 'assets/js'),
					},
					{
						from: path.resolve(__dirname, 'resources/css/slider'),
						to: path.resolve(__dirname, 'assets/css'),
					},
				]
			}),

			new RemoveEmptyScriptsPlugin({
				stage: RemoveEmptyScriptsPlugin.STAGE_AFTER_PROCESS_PLUGINS,
				remove: /\.(js)$/,
			}),
		],
	},
];
