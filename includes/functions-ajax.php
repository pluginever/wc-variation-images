<?php
// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * send variation image in single product page
 *
 * since 1.0.0
 *
 * return string
 */
function wc_variation_images_load_variation_images() {
	if ( ! wp_verify_nonce( $_POST['nonce'], 'wc_variation_images' ) ) {
		wp_send_json_error( 'error' );
	}


	if ( ! isset( $_POST['product_id'] ) ) {
		wp_send_json_error( 'error' );
	}

	$variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : '';
	$product_id   = absint( $_POST['product_id'] );

	$image = wc_variation_images_get_variation_images( $product_id, $variation_id );
	wp_send_json_success( array( 'images' => $image ) );
}

add_action( 'wp_ajax_wc_variation_images_load_variation_images', 'wc_variation_images_load_variation_images' );
add_action( 'wp_ajax_nopriv_wc_variation_images_load_variation_images', 'wc_variation_images_load_variation_images' );

/**
 * retrieve product variation image
 *
 * since 1.0.0
 *
 * @param $product_id
 * @param $variation_id
 *
 * @return false|string
 */
function wc_variation_images_get_variation_images( $product_id, $variation_id ) {

	//when variation id not found replace product_id as variation id
	if ( $variation_id == '' ) {
		$variation_id = $product_id;
	}
	$columns                      = apply_filters( 'woocommerce_product_thumbnails_columns', 4 );
	$has_variation_gallery_images = (bool) get_post_meta( $variation_id, 'wc_variation_images_variation_images', true );
	$variation_product            = wc_get_product( $variation_id );
	$variation_parent             = $variation_product->get_data();
	$variation_image_id           = absint( $variation_parent['image_id'] );
	if ( empty( $variation_image_id ) ) {
		$variation_image_id = get_post_thumbnail_id( $product_id );
	}
	$wrapper_classes = apply_filters( 'woocommerce_single_product_image_gallery_classes', array(
		'woocommerce-product-gallery',
		'woocommerce-product-gallery--' . ( $variation_image_id ? 'with-images' : 'without-images' ),
		'woocommerce-product-gallery--columns-' . absint( $columns ),
		'images',
	) );

	$gallery_images = array();

	if ( $has_variation_gallery_images ) {
		$gallery_images = (array) get_post_meta( $variation_id, 'wc_variation_images_variation_images', true );
		if ( count( $gallery_images ) > 3 ) {
			$gallery_images = array_slice( $gallery_images, 0, 3 );
		}
	} else {
		$product_gallery_images = get_post_meta( $product_id, '_product_image_gallery', true );
		if ( ! empty( $product_gallery_images ) ) {
			$gallery_images = explode( ',', $product_gallery_images );
		}
	}

	//add product/variation image id in gallery image array
	array_unshift( $gallery_images, $variation_image_id );
	ob_start();
	?>
	<div class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $wrapper_classes ) ) ); ?>"
	     data-columns="<?php echo esc_attr( $columns ); ?>"
	     style="opacity: 0;">
		<figure class="woocommerce-product-gallery__wrapper wc-variation-images-gallery">
			<?php
			$html           = '';
			$gallery_images = array_filter( $gallery_images );
			if ( $gallery_images ) {
				foreach ( $gallery_images as $attachment_id ) {
					if ( ! empty( $attachment_id ) ) {
						$variation_image = wc_get_gallery_image_html( $attachment_id ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
						$html            .= apply_filters( 'wc_variation_images_content', $variation_image, $attachment_id ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
					}
				}
			} else {
				$html = '<div class="woocommerce-product-gallery__image--placeholder">';
				$html .= sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ), esc_html__( 'Awaiting product image', 'wc-variation-images' ) );
				$html .= '</div>';
			}
			echo $html;
			?>
		</figure>
	</div>
	<?php
	return ob_get_clean();
}


