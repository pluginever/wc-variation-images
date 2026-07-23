<?php
/**
 * Plugin Name:          Variation Images for WooCommerce
 * Plugin URI:           https://pluginever.com/plugins/wc-variation-images-pro
 * Description:          Adds additional gallery images per product variation in WooCommerce.
 * Version:              1.3.5
 * Requires at least:    5.2
 * Tested up to:         6.9
 * Requires PHP:         7.4
 * Author:               PluginEver
 * Author URI:           https://pluginever.com
 * License:              GPL v2 or later
 * License URI:          https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:          wc-variation-images
 * Domain Path:          /languages
 * WC requires at least: 3.0.0
 * WC tested up to:      10.6
 * Requires Plugins:     woocommerce
 *
 * @package           PluginEver\VariationImages
 * @author            PluginEver <support@pluginever.com>
 * @copyright         2026 PluginEver
 * @license           GPL-2.0-or-later
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 */

use PluginEver\VariationImages\Installer;
use PluginEver\VariationImages\Plugin;

defined( 'ABSPATH' ) || exit;

// Load the Composer autoloader.
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/includes/functions.php';

$data = array(
	'version'      => '1.3.5',
	'settings_url' => admin_url( 'admin.php?page=wc-variation-images-settings' ),
	'pro_basename' => 'wc-variation-images-pro/wc-variation-images-pro.php',
	'store_url'    => 'https://pluginever.com',
	'upgrade_url'  => 'https://pluginever.com/plugins/wc-variation-images-pro/',
	'docs_url'     => 'https://pluginever.com/docs/wc-variation-images/',
	'support_url'  => 'https://pluginever.com/support/',
	'review_url'   => 'https://wordpress.org/support/plugin/wc-variation-images/reviews/#new-post',
);


Plugin::create( __FILE__, $data );

/**
 * Get the main plugin instance.
 *
 * @since 1.0.0
 * @return Plugin Plugin instance.
 */
function wc_variation_images(): Plugin {
	return Plugin::instance();
}

// Register the plugin activation and deactivation hooks.
wc_variation_images()->on_activation( array( Installer::class, 'install' ) );
wc_variation_images()->on_deactivation( array( Installer::class, 'deactivate' ) );

// Declare WooCommerce feature compatibility.
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
		}
	}
);

// Boot the plugin.
wc_variation_images()->bootstrap();
