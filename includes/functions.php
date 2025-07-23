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
	if ( count( $gallery_images ) > 8 && apply_filters( 'wc_variation_images_limit', true ) ) {
		$gallery_images = array_slice( $gallery_images, 0, 3 );
	}

	$hide_gallery = get_option( 'wcvi_disable_image_slider', 'no' );

	// Add product/variation image id in gallery image array.
	array_unshift( $gallery_images, $variation_image_id );

	$gallery_position = get_option( 'wcvi_gallery_position', 'bottom' );
	$hide_lightbox    = 'no' === get_option( 'wcvi_disable_image_lightbox', 'no' ) ? 'data-fancybox=gallery' : '';

	ob_start();
	?>
	<div class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $wrapper_classes ) ) ); ?>"
		data-columns="<?php echo esc_attr( $columns ); ?>"
		style="opacity: 1;">
		<figure class="woocommerce-product-gallery__wrapper wc-variation-images-gallery" style="height: auto;">
			<div class="wc-variation-images-viewer __<?php echo esc_attr( $gallery_position ); ?>">
				<?php if ( 'no' !== get_option( 'wcvi_disable_image_slider', 'no' ) ) { ?>
				<div class="selected-image">
					<a href="" id="image-link" class="light-box-icon" <?php echo esc_attr( $hide_lightbox ); ?>>
						<svg fill="white" height="20px" width="20px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1.05 1.05" enable-background="new 0 0 42 42" xml:space="preserve">
							<path d="M0.383 0.503c0 0.078 0.065 0.143 0.143 0.143s0.143 -0.065 0.143 -0.143 -0.065 -0.143 -0.143 -0.143 -0.143 0.065 -0.143 0.143m0.203 0.308C0.753 0.772 1.012 0.55 1.012 0.55s-0.193 -0.3 -0.45 -0.333c-0.015 -0.003 -0.065 -0.003 -0.075 -0.003 -0.25 0.025 -0.45 0.343 -0.45 0.343s0.217 0.215 0.425 0.248c0.023 0.01 0.098 0.01 0.123 0.005M0.278 0.517c0 -0.13 0.11 -0.235 0.248 -0.235s0.248 0.105 0.248 0.235S0.663 0.75 0.525 0.75s-0.248 -0.105 -0.248 -0.233"/>
						</svg>
					</a>
					<img class="main-image" id="main-image" src="" alt="Selected Image">
				</div>
				<div class="image-list" id="image-list">
					<?php
					$html           = '';
					$gallery_images = array_filter( $gallery_images );
					if ( $gallery_images ) {
						foreach ( $gallery_images as $attachment_id ) {
							/**  sizes: thumbnail, medium, large, and full. */
							$image_url = wp_get_attachment_image_src( $attachment_id, 'full' );
							$html     .= '<img class="thumbnail" src="' . $image_url[0] . '" alt="image">';
						}
					} else {
						$html  = '<div class="woocommerce-product-gallery__image--placeholder">';
						$html .= sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ), esc_html__( 'Awaiting product image', 'wc-variation-images' ) );
						$html .= '</div>';
					}
					echo wp_kses_post( $html );
				} else {
					?>
						<div style="--swiper-navigation-color: #fff; --swiper-pagination-color: #fff" class="swiper mySwiper2">
							<div class="swiper-wrapper">
								<?php
								if ( $gallery_images ) {
									foreach ( $gallery_images as $attachment_id ) {
										/**  sizes: thumbnail, medium, large, and full. */
										$image_url = wp_get_attachment_image_src( $attachment_id, 'full' );
										?>
										<div class="swiper-slide selected-image">
											<a href="<?php echo esc_url( $image_url[0] ); ?>" class="light-box-icon" <?php echo esc_attr( $hide_lightbox ); ?>>
												<svg fill="white" height="20px" width="20px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1.05 1.05" enable-background="new 0 0 42 42" xml:space="preserve">
													<path d="M0.383 0.503c0 0.078 0.065 0.143 0.143 0.143s0.143 -0.065 0.143 -0.143 -0.065 -0.143 -0.143 -0.143 -0.143 0.065 -0.143 0.143m0.203 0.308C0.753 0.772 1.012 0.55 1.012 0.55s-0.193 -0.3 -0.45 -0.333c-0.015 -0.003 -0.065 -0.003 -0.075 -0.003 -0.25 0.025 -0.45 0.343 -0.45 0.343s0.217 0.215 0.425 0.248c0.023 0.01 0.098 0.01 0.123 0.005M0.278 0.517c0 -0.13 0.11 -0.235 0.248 -0.235s0.248 0.105 0.248 0.235S0.663 0.75 0.525 0.75s-0.248 -0.105 -0.248 -0.233"/>
												</svg>
											</a>
											<img class="product-image" src="<?php echo esc_url( $image_url[0] ); ?>"  alt="slider-img" data-zoom-image="large-image-url.jpg"/>
										</div>
										<?php
									}
								}
								?>
							</div>
							<div class="swiper-button-next"></div>
							<div class="swiper-button-prev"></div>
						</div>
						<div thumbsSlider="" class="swiper mySwiper">
							<div class="swiper-wrapper gallery-bottom">
								<?php
								if ( $gallery_images ) {
									foreach ( $gallery_images as $attachment_id ) {
										/**  sizes: thumbnail, medium, large, and full. */
										$image_url = wp_get_attachment_image_src( $attachment_id, 'full' );
										?>
										<div class="swiper-slide">
											<img src="<?php echo esc_url( $image_url[0] ); ?>"  alt="slider-img"/>
										</div>
										<?php
									}
								}
								?>
							</div>
						</div>
					<?php
				}
				?>
			</div>
			</div>
		</figure>
	</div>
	<?php
	return ob_get_clean();
}
