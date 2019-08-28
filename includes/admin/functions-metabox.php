<?php
// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * since 1.0.0
 *
 * @param $post_id
 * @param $post
 *
 * @return int
 */
function wc_variation_images_save_meta( $post_id, $post ) {

	if ( empty( $post_id ) || empty( $post ) || ! isset( $_POST['wc_variation_images_image_variation_thumb'] ) ) {
		return $post_id;
	}

	// Dont' save meta boxes for revisions or autosaves
	if ( defined( 'DOING_AUTOSAVE' ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
		return $post_id;
	}

	// Check the nonce
	if ( empty( $_POST['woocommerce_meta_nonce'] ) || ! wp_verify_nonce( $_POST['woocommerce_meta_nonce'], 'woocommerce_save_data' ) ) {
		return $post_id;
	}

	// Check the post being saved == the $post_id to prevent triggering this call for other save_post events
	if ( empty( $_POST['post_ID'] ) || $_POST['post_ID'] != $post_id ) {
		return $post_id;
	}

	// Check user has permission to edit
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}

	// Check the post type
	if ( ! in_array( $post->post_type, array( 'product' ) ) ) {
		return $post_id;
	}

	$ids = $_POST['wc_variation_images_image_variation_thumb'];
	// sanitize
	array_walk_recursive( $ids, 'sanitize_text_field' );

	if ( 0 < count( $ids ) ) {
		foreach ( $ids as $parent_id => $attachment_ids ) {
			if ( ! empty( $attachment_ids ) ) {
				if ( count( $attachment_ids ) > 3 ) {
					$attachment_ids = array_slice( $attachment_ids, 0, 3 );
				}
				update_post_meta( $parent_id, 'wc_variation_images_variation_images', $attachment_ids );
			} else {
				update_post_meta( $parent_id, 'wc_variation_images_variation_images', '' );
			}
		}
	}
}

add_action( 'save_post', 'wc_variation_images_save_meta', 1, 2 );

/**
 * since 1.0.0
 *
 * @param $variation_id
 * @param $i
 *
 * @return void|int
 */
function wc_variation_images_save_product_variation( $variation_id, $i ) {
	$ids = array();
	if ( isset( $_POST['wc_variation_images_image_variation_thumb'][ $variation_id ] ) ) {
		$ids = $_POST['wc_variation_images_image_variation_thumb'][ $variation_id ];
		array_walk_recursive( $ids, 'sanitize_text_field' );
		if ( count( $ids ) > 3 ) {
			$ids = array_slice( $ids, 0, 3 );
		}
	}
	update_post_meta( $variation_id, 'wc_variation_images_variation_images', $ids );
}

add_action( 'woocommerce_save_product_variation', 'wc_variation_images_save_product_variation', 10, 2 );
