<?php

namespace Pluginever\WCVariationImages\Admin;
class MetaBox {

	function __construct() {
		add_action( 'save_post', array( $this, 'variation_image_save' ), 1, 2 );

		add_action( 'woocommerce_save_product_variation', array( $this, 'save_product_variation' ), 10, 2 );
	}

	public function variation_image_save( $post_id, $post ) {

		if ( empty( $post_id ) || empty( $post ) || !isset($_POST['wpwvi_image_variation_thumb']) ) {
			return;
		}

		// Dont' save meta boxes for revisions or autosaves
		if ( defined( 'DOING_AUTOSAVE' ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}

		// Check the nonce
		if ( empty( $_POST['woocommerce_meta_nonce'] ) || ! wp_verify_nonce( $_POST['woocommerce_meta_nonce'], 'woocommerce_save_data' ) ) {
			return;
		}

		// Check the post being saved == the $post_id to prevent triggering this call for other save_post events
		if ( empty( $_POST['post_ID'] ) || $_POST['post_ID'] != $post_id ) {
			return;
		}

		// Check user has permission to edit
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Check the post type
		if ( ! in_array( $post->post_type, array( 'product' ) ) ) {
			return;
		}

		$ids = $_POST['wpwvi_image_variation_thumb'];
		// sanitize
		array_walk_recursive( $ids, 'sanitize_text_field' );

		if ( 0 < count( $ids ) ) {
			foreach ( $ids as $parent_id => $attachment_ids ) {
				if ( !empty( $attachment_ids ) ) {
					if(count( $attachment_ids ) > 3 ){
						$attachment_ids = array_slice( $attachment_ids, 0, 3);
					}
					update_post_meta( $parent_id, 'wpwvi_variation_images', $attachment_ids );
				} else {
					update_post_meta( $parent_id, 'wpwvi_variation_images', '' );
				}
			}
		}

		return true;
	}

	public function save_product_variation( $variation_id, $i ) {

		if ( ! isset( $_POST['wpwvi_image_variation_thumb'] ) ) {
			return;
		}

		$ids =  $_POST['wpwvi_image_variation_thumb'][ $variation_id ];
		if( count( $ids ) > 3){
			$ids = array_slice( $ids, 0, 3);
		}
		update_post_meta( $variation_id, 'wpwvi_variation_images', $ids );
		return true;
	}

}
