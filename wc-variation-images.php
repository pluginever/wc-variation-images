<?php
/**
 * @package PluginEver\WC_Variation_Images
 * @link    https://www.pluginever.com/plugins/woocommerce-variation-images
 * @license      GPL v3 or later
 *
 * Plugin Name: WooCommerce Variation Images
 * Description: Adds additional gallery images per product variation.
 * Version:     1.0.3
 * Plugin URI:  https://www.pluginever.com/plugins/woocommerce-variation-images
 * Reviews URI: https://wordpress.org/support/plugin/wc-variation-images/reviews/#new-post
 * Author:      pluginever
 * Author URI:  https://www.pluginever.com
 * Donate link: https://www.pluginever.com
 * Text Domain: wc-variation-images
 * Domain Path: /i18n/languages/
 * Requires PHP: 7.0.0
 * WC requires at least:  5.5.0
 *
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

// don't call the file directly
defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WC_VARIATION_IMAGES_PLUGIN_FILE' ) ) {
	define( 'WC_VARIATION_IMAGES_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'WC_VARIATION_IMAGES_PLUGIN_DIR' ) ) {
	define( 'WC_VARIATION_IMAGES_PLUGIN_DIR', __DIR__ );
}

// Autoloader.
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Returns the main instance of plugin.
 *
 * @return \PluginEver\WC_Variation_Images\Plugin|object
 */
function wc_variation_images(){
	return \PluginEver\WC_Variation_Images\Plugin::instance();
}

// Global for backwards compatibility.
$GLOBALS['wc_variation_images'] = wc_variation_images();
