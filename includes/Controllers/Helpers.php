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

	/**
	 * Lightbox Settings.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public static function get_lightbox_data() {
		$lightbox_config = array();

		$lightbox_config = apply_filters( 'wc_variation_images_lightbox_config', $lightbox_config );
		$lightbox_config = wp_json_encode( $lightbox_config );
		return $lightbox_config;
	}

	/**
	 * Slider Settings.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public static function get_slider_data() {
		$slider_config = array();

		$slider_config = apply_filters( 'wc_variation_images_slider_config', $slider_config );
		$slider_config = wp_json_encode( $slider_config );
		return $slider_config;
	}
}
