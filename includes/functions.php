<?php

use WooCommerceVariationImages\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Get the plugin instance.
 *
 * @since 1.0.1
 * @return WooCommerceVariationImages\Plugin
 */
function wc_variation_images() {
	return Plugin::instance();
}
