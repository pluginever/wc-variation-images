<?php

namespace Pluginever\WCVariationImages;

class Ajax{

	/**
	 * Ajax constructor.
	 */
	public function __construct() {

		add_action('wp_ajax_wc_variation_images_load_variation_images', array($this, 'load_variation_images'));
	}

	public function load_variation_images(){
		if ( ! wp_verify_nonce( $_POST['nonce'], 'wc_variation_images' ) ) {
			wp_send_json_error('error');
		}

		// bail if no id submitted
		if ( ! isset( $_POST['variation_id'] ) ) {
			wp_send_json_error('error');
		}

		$variation_id = $_POST['variation_id'];
		$product_id = $_POST['variation_id'];

		if ( ! isset( $_POST['product_id'] ) ) {
			wp_send_json_error('error');
		}

		$has_variation_gallery_images = (bool)get_post_meta($variation_id, 'wpwvi_variation_images', true);
		$product = wc_get_product($product_id);

		if ($has_variation_gallery_images) {
			$gallery_images = (array)get_post_meta($variation_id, 'wpwvi_variation_images', true);
		} else {
			$gallery_images = $product->get_gallery_image_ids();
		}


		wp_send_json_success(array( 'images' => $variation_images ));
	}

	/**
	 * get images
	 *
	 * @since 1.0.0
	 * @return array $media_ids
	 */
	public function get_images( $id = 0 ) {
		$media_ids = get_post_meta( $id, '_wc_variation_images', true );

		return $media_ids;
	}

}
new Ajax();
