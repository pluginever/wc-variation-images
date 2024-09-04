<?php

use WooCommerceVariationImages\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Get the plugin instance.
 *
 * @since 1.0.1
 * @return WooCommerceVariationImages\Plugin
 */
function wc_variation_images() {
	return Plugin::instance();
}

/**
 * get settings options
 *
 * @param string $key Image key.
 * @param string $default_value Default value.
 * @param string $section Section Value.
 *
 * @since 1.0.0
 * @return mixed
 */
function wc_variation_images_get_settings( $key, $default_value = '', $section = '' ) {
	$option = get_option( $section, array() );
	return ! empty( $option[ $key ] ) ? $option[ $key ] : $default_value;
}

/**
 * retrieve product variation image
 *
 * @param int $product_id Product ID.
 * @param int $variation_id Variation ID.
 *
 * @since 1.0.0
 * @return false|string
 */
function wc_variation_images_get_variation_images( $product_id, $variation_id ) {

	// When variation id not found replace product_id as variation id.
	if ( empty( $variation_id ) ) {
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
	$wrapper_classes = apply_filters(
		'woocommerce_single_product_image_gallery_classes',
		array(
			'woocommerce-product-gallery',
			'woocommerce-product-gallery--' . ( $variation_image_id ? 'with-images' : 'without-images' ),
			'woocommerce-product-gallery--columns-' . absint( $columns ),
			'images',
		)
	);

	$gallery_images = array();

	if ( $has_variation_gallery_images ) {
		$gallery_images = (array) get_post_meta( $variation_id, 'wc_variation_images_variation_images', true );
	} else {
		$product_gallery_images = get_post_meta( $product_id, '_product_image_gallery', true );
		if ( ! empty( $product_gallery_images ) ) {
			$gallery_images = explode( ',', $product_gallery_images );
		}
	}

	// Show only 3 image in free version.
	if ( count( $gallery_images ) > 3 && apply_filters( 'wc_variation_images_limit', true ) ) {
		$gallery_images = array_slice( $gallery_images, 0, 3 );
	}

	$hide_gallery  = wc_variation_images_get_settings( 'wc_variation_images_hide_image_slider', 'no', 'wc_variation_images_general_settings' );

	// Add product/variation image id in gallery image array.
	array_unshift( $gallery_images, $variation_image_id );
	ob_start();
	?>
	<div class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $wrapper_classes ) ) ); ?>"
		data-columns="<?php echo esc_attr( $columns ); ?>"
		style="opacity: 1;">
		<figure class="woocommerce-product-gallery__wrapper wc-variation-images-gallery">
			<?php
			$html           = '';
			$gallery_images = array_filter( $gallery_images );
			if ( $gallery_images ) {
				$flag = true;
				foreach ( $gallery_images as $attachment_id ) {
					if ( ! $flag && 'yes' === $hide_gallery ) {
						add_filter(
							'woocommerce_gallery_image_size',
							function () {
								return array( 100, 100 );
							}
						);
					}
					if ( ! empty( $attachment_id ) ) {
						$variation_image = wc_get_gallery_image_html( $attachment_id ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
						$html           .= apply_filters( 'wc_variation_images_content', $variation_image, $attachment_id ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
					}
					$flag = false;
				}
			} else {
				$html  = '<div class="woocommerce-product-gallery__image--placeholder">';
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
