<?php
// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * since 1.0.0
 *
 * @param $variation_id
 * @param $i
 *
 * @return void|int
 */
function wc_variation_images_save_product_variation( $variation_id, $i ) {
	$attachment_ids = array();

	if ( isset( $_POST['wc_variation_images_image_variation_thumb'][ $variation_id ] ) ) {
		//sanitize
		$attachment_ids = array_map( 'absint', $_POST['wc_variation_images_image_variation_thumb'][ $variation_id ] );
		//filter
		$attachment_ids = array_filter( $attachment_ids );
		//unique
		$attachment_ids = array_unique( $attachment_ids );
	}
	update_post_meta( $variation_id, 'wc_variation_images_variation_images', $attachment_ids );
}

add_action( 'woocommerce_save_product_variation', 'wc_variation_images_save_product_variation', 10, 2 );
