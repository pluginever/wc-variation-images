<?php

namespace WooCommerceVariationImages;

defined( 'ABSPATH' ) || exit;

/**
 * Class Actions.
 *
 * @since 1.0.0
 *
 * @package WooCommerceVariationImages
 */
class Actions {
	/**
	 * Actions constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'wp_ajax_wc_variation_images_load_variation_images', array( $this, 'wc_variation_images_load_variation_images' ) );
	}

	/**
	 * send variation image in single product page
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function wc_variation_images_load_variation_images() {
		wp_verify_nonce( 'wc_variation_images_ajax', 'nonce' );

		if ( ! isset( $_POST['product_id'] ) ) {
			wp_send_json_error( 'error' );
		}

		$variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : '';
		$product_id   = absint( $_POST['product_id'] );

		$image = wc_variation_images_get_variation_images( $product_id, $variation_id );
		wp_send_json_success( array( 'images' => $image ) );

		wp_die();
	}
}
