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

		$image = $this->get_variation_images( $product_id, $variation_id );
		wp_send_json_success(array( 'images' => $image ));
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
	
	public function get_variation_images($product_id,$variation_id){
		$columns           = apply_filters( 'woocommerce_product_thumbnails_columns', 4 );
		$post_thumbnail_id = $variation_id;
		
		$has_variation_gallery_images = (bool)get_post_meta($variation_id, 'wpwvi_variation_images', true);
		$product = wc_get_product($variation_id);
		$variation_parent = $product->get_data();
		$variation_image_id = $variation_parent['image_id'];
		
		$wrapper_classes   = apply_filters( 'woocommerce_single_product_image_gallery_classes', array(
			'woocommerce-product-gallery',
			'woocommerce-product-gallery--' . ( $variation_image_id  ? 'with-images' : 'without-images' ),
			'woocommerce-product-gallery--columns-' . absint( $columns ),
			'images',
		) );
		if ($has_variation_gallery_images) {
			$gallery_images = (array)get_post_meta($variation_id, 'wpwvi_variation_images', true);
		} else {
			$gallery_images = $product->get_gallery_image_ids();
		}
		ob_start();
		?>
		<div class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $wrapper_classes ) ) ); ?>" data-columns="<?php echo esc_attr( $columns ); ?>" style="opacity: 0; transition: opacity .25s ease-in-out;">
			<figure class="woocommerce-product-gallery__wrapper">
				<?php
					if ( $variation_image_id ) {
						$html = wc_get_gallery_image_html( $variation_image_id, true );
					} else {
						$html  = '<div class="woocommerce-product-gallery__image--placeholder">';
						$html .= sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ), esc_html__( 'Awaiting product image', 'woocommerce' ) );
						$html .= '</div>';
					}
					echo $html;
					
					if ( $gallery_images ) {
						foreach ( $gallery_images as $attachment_id ) {
							echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', wc_get_gallery_image_html( $attachment_id ), $attachment_id ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
						}
					}
					?>
			</figure>
		</div>
		<?php 
		return ob_get_clean();
	}

}
new Ajax();
