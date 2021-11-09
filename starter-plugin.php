<?php
/**
 * A starter plugin for WordPress
 *
 * @package      ByteEver\Starter_Plugin
 * @link         https://github.com/byteever/byteever-plugin
 * @copyright    2020 ByteEver LLC
 * @license      GPL v3 or later
 *
 * Plugin Name:  Starter Plugin
 * Description:  A starter plugin for ByteEver.
 * Version:      1.0.0
 * Plugin URI:   https://www.byteever.com/products/plugin-scaffold/
 * Author:       ByteEver
 * Author URI:   https://www.byteever.com/
 * Text Domain:  text_domain
 * Domain Path: /i18n/languages/
 * Requires PHP: 7.0.0
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'STARTER_PLUGIN_FILE' ) ) {
	define( 'STARTER_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'STARTER_PLUGIN_DIR' ) ) {
	define( 'STARTER_PLUGIN_DIR', __DIR__ );
}

// Autoloader.
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Returns the main instance of plugin.
 *
 * @return \ByteEver\Starter_Plugin\Plugin|object
 */
function starter_plugin(){
	return \ByteEver\Starter_Plugin\Plugin::instance();
}

// Global for backwards compatibility.
$GLOBALS['starter_plugin'] = starter_plugin();
