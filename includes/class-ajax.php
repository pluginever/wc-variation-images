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

		// bail if no ids submitted
		if ( ! isset( $_POST['variation_ids'] ) ) {
			wp_send_json_error('error');
		}

		// sanitize
		$variation_ids = array_map( 'absint', $_POST['variation_ids'] );

		$variation_images = array();

		if ( 0 < count( $variation_ids ) ) {
			foreach( $variation_ids as $id ) {
				$ids = $this->get_images( $id );

				$html = '';
				$html .= '<input type="hidden" class="wc-variation-images-thumbs-save" name="wc_variation_images_thumbs[' . esc_attr( $id ) . ']" value="' . esc_attr( $ids ) . '">';
				$html .= '<ul class="wc-variation-images-list">';

				foreach( explode( ',', $ids ) as $attach_id ) {
					$attachment = wp_get_attachment_image_src( $attach_id, array( 40, 40 ) );

					if ( $attachment ) {
						$html .= '<li><a href="#" class="wc-variation-images-thumb" data-id="' . esc_attr( $attach_id ) . '"><img src="' . esc_attr( $attachment[0] ) . '" width="40" height="40" /><span class="overlay"></span></a></li>';
					}
				}

				$html .= '</ul>';

				$variation_images[ $id ] = $html;
			}
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
