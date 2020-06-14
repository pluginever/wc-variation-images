<?php
// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Control Woocommerce Gallery Settings
 *
 * since 1.0.0
 */
function wc_variation_images_gallery_control() {
	global $post;
	if ( ! $post ) {
		return;
	}
	$product = wc_get_product( $post->ID );
	if ( is_product() && 'variable' == $product->get_type() ) {
		$hide_zoom     = wc_variation_images_get_settings( 'wc_variation_images_hide_image_zoom', 'no', 'wc_variation_images_general_settings' );
		$hide_lightbox = wc_variation_images_get_settings( 'wc_variation_images_hide_image_lightbox', 'no', 'wc_variation_images_general_settings' );
		$hide_gallery  = wc_variation_images_get_settings( 'wc_variation_images_hide_image_slider', 'no', 'wc_variation_images_general_settings' );
		if ( 'yes' == $hide_zoom ) {
			remove_theme_support( 'wc-product-gallery-zoom' );
		}
		if ( 'yes' == $hide_lightbox ) {
			remove_theme_support( 'wc-product-gallery-lightbox' );
		}
		if ( 'yes' == $hide_gallery ) {
			remove_theme_support( 'wc-product-gallery-slider' );
		}
	}
}

add_action( 'wp', 'wc_variation_images_gallery_control', 100 );

/**
 * Upload variation images
 *
 * since 1.0.0
 *
 * @param $loop
 * @param $variation_data
 * @param $variation
 *
 * @return void
 */
function wc_variation_images_upload_images( $loop, $variation_data, $variation ) {
	$variation_id     = absint( $variation->ID );
	$variation_images = get_post_meta( $variation_id, 'wc_variation_images_variation_images', true );
	?>
    <div class="form-row form-row-full wc-variation-images-gallery-wrapper">
        <h4><?php esc_html_e( 'Variation Images', 'wc-variation-images' ) ?></h4>
        <div class="wc-variation-images-image-container">
            <ul id="wc-variation-images-image-list-<?php echo absint( $variation_id ); ?>"
                class="wc-variation-images-image-list">
				<?php
				if ( is_array( $variation_images ) && ! empty( $variation_images ) ) {
					foreach ( $variation_images as $image_id ):
						$image_arr = wp_get_attachment_image_src( $image_id );
						?>
                        <li class="wc-variation-images-image-info">
                            <input type="hidden"
                                   name="wc_variation_images_image_variation_thumb[<?php echo $variation_id ?>][]"
                                   value="<?php echo $image_id ?>">
                            <img src="<?php echo esc_url( $image_arr[0] ) ?>" alt="">
                            <span class="wc-variation-images-remove-image dashicons dashicons-dismiss"></span>
                        </li>
					<?php endforeach;
				} ?>
            </ul>
        </div>
        <p class="wc-variation-images-add-container hide-if-no-js">
            <a href="#" data-wc_variation_images_variation_id="<?php echo absint( $variation->ID ) ?>"
               class="button wc-variation-images-add-image"><?php _e( 'Add Variation Images', 'wc-variation-images' ) ?></a>
        </p>
    </div>
	<?php
}

add_action( 'woocommerce_product_after_variable_attributes', 'wc_variation_images_upload_images', 10, 3 );

/**
 * add class woocommece single product page
 *
 * since 1.0.0
 *
 * @param $class
 *
 * @return array
 */
function wc_variation_images_add_gallery_class( $class ) {
	$class[] = sanitize_html_class( 'wc-variation-images-product-gallery' );

	return $class;
}

add_filter( 'woocommerce_single_product_image_gallery_classes', 'wc_variation_images_add_gallery_class' );
