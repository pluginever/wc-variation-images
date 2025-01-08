<?php

namespace WooCommerceVariationImages\Controllers;

defined( 'ABSPATH' ) || exit;

/**
 * Class Helpers.
 *
 * @since 1.0.0
 *
 * @package WooCommerceVariationImages\Controllers
 */
class Helpers {
	/**
	 * Gallery Position List.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public static function gallery_position_list() {
		$positions = array(
			'bottom' => __( 'Bottom', 'wc-variation-images' ),
		);

		return apply_filters( 'wc_variation_images_gallery_positions', $positions );
	}
}
