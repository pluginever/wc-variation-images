<?php

namespace WooCommerceVariationImages\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Class Products.
 *
 * @since 1.0.0
 *
 * @package WooCommerceVariationImages\Admin
 */
class Products {
	/**
	 * Products constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'woocommerce_save_product_variation', array( $this, 'wc_variation_images_save_product_variation' ), 10, 1 );
	}

	/**
	 * Save Variation Product.
	 *
	 * @param int $variation_id Variation ID.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function wc_variation_images_save_product_variation( $variation_id ) {
		$attachment_ids = array();
		if ( isset( $_POST['wc_variation_images_image_variation_thumb'][ $variation_id ] ) ) {
			// Sanitize.
			$attachment_ids = array_map( 'absint', $_POST['wc_variation_images_image_variation_thumb'][ $variation_id ] );
			// Filter.
			$attachment_ids = array_filter( $attachment_ids );
			// Unique.
			$attachment_ids = array_unique( $attachment_ids );
		}
		update_post_meta( $variation_id, 'wc_variation_images_variation_images', $attachment_ids );
	}
}
