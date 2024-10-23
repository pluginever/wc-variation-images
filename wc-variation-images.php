<?php
/**
 * Plugin Name:          WC Variation Images
 * Plugin URI:           https://pluginever.com/plugins/wc-variation-images-pro
 * Description:          Adds additional gallery images per product variation.
 * Version:              1.1.1
 * Author:               PluginEver
 * Author URI:           https://pluginever.com
 * License:              GPL v2 or later
 * License URI:          https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:          wc-variation-images
 * Domain Path:          languages/
 * Requires Plugins:     woocommerce
 * Requires at least:    5.2
 * Tested up to:         6.6
 * Requires PHP:         7.4
 * WC requires at least: 3.0.0
 * WC tested up to:      9.3
 *
 * @package WooCommerceVariationImages
 */

/**
 * Copyright (c) 2024 PluginEver (email : support@pluginever.com)
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
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

use WooCommerceVariationImages\Plugin;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

// Require the autoloader.
require_once __DIR__ . '/vendor/autoload.php';

// Instantiate the plugin.
WooCommerceVariationImages\Plugin::create(
	array(
		'file'         => __FILE__,
		'settings_url' => admin_url( 'admin.php?page=wc-variation-images' ),
		'docs_url'     => 'https://pluginever.com/docs/wc-variation-images/',
		'support_url'  => 'https://pluginever.com/support/',
		'review_url'   => 'https://wordpress.org/support/plugin/wc-variation-images/reviews/#new-post',
	)
);
