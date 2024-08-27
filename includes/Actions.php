<?php

namespace WooCommerceVariationImages;

defined( 'ABSPATH' ) || exit;

/**
 * Class Actions.
 *
 * @since 1.0.0
 *
 * @package WooCommerceVariationImages
 */
class Actions {
	/**
	 * Actions constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'wp', array( $this, 'wc_variation_images_gallery_control' ), 100 );
		add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'wc_variation_images_upload_images' ), 10, 3 );
		add_filter( 'woocommerce_single_product_image_gallery_classes', array( $this, 'wc_variation_images_add_gallery_class' ) );
		add_action( 'wp_ajax_wc_variation_images_load_variation_images', array( $this, 'wc_variation_images_load_variation_images' ) );
	}

	/**
	 * Add class WooCommerce single product page.
	 *
	 * @param array $class_name Class names.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function wc_variation_images_add_gallery_class( $class_name ) {
		$class_name[] = sanitize_html_class( 'wc-variation-images-product-gallery' );
		return $class_name;
	}

	/**
	 * Upload variation images
	 *
	 * @param int         $loop Item loop.
	 * @param array       $variation_data Variation Data.
	 * @param \WC_Product $variation Variation Object.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function wc_variation_images_upload_images( $loop, $variation_data, $variation ) {
		$variation_id     = absint( $variation->ID );
		$variation_images = get_post_meta( $variation_id, 'wc_variation_images_variation_images', true );
		?>
		<div class="form-row form-row-full wc-variation-images-gallery-wrapper">
			<h4><?php esc_html_e( 'Variation Images', 'wc-variation-images' ); ?></h4>
			<div class="wc-variation-images-image-container">
				<ul id="wc-variation-images-image-list-<?php echo absint( $variation_id ); ?>"
					class="wc-variation-images-image-list">
					<?php
					if ( is_array( $variation_images ) && ! empty( $variation_images ) ) {
						foreach ( $variation_images as $image_id ) :
							$image_arr = wp_get_attachment_image_src( $image_id );
							?>
							<li class="wc-variation-images-image-info">
								<input type="hidden"
										name="wc_variation_images_image_variation_thumb[<?php echo esc_attr( $variation_id ); ?>][]"
										value="<?php echo esc_attr( $image_id ); ?>">
								<img src="<?php echo esc_url( $image_arr[0] ); ?>" alt="">
								<span class="wc-variation-images-remove-image dashicons dashicons-dismiss"></span>
							</li>
							<?php
						endforeach;
					}
					?>
				</ul>
			</div>
			<p class="wc-variation-images-add-container hide-if-no-js">
				<a href="#" data-wc_variation_images_variation_id="<?php echo absint( $variation->ID ); ?>"
					class="button wc-variation-images-add-image"><?php esc_html_e( 'Add Variation Images', 'wc-variation-images' ); ?></a>
			</p>
		</div>
		<?php
	}

	/**
	 * Control Woocommerce Gallery Settings
	 *
	 * since 1.0.0
	 */
	public function wc_variation_images_gallery_control() {
		global $post;
		if ( ! $post ) {
			return;
		}
		$product = wc_get_product( $post->ID );
		if ( is_product() && 'variable' === $product->get_type() ) {
			$hide_zoom     = wc_variation_images_get_settings( 'wc_variation_images_hide_image_zoom', 'no', 'wc_variation_images_general_settings' );
			$hide_lightbox = wc_variation_images_get_settings( 'wc_variation_images_hide_image_lightbox', 'no', 'wc_variation_images_general_settings' );
			$hide_gallery  = wc_variation_images_get_settings( 'wc_variation_images_hide_image_slider', 'no', 'wc_variation_images_general_settings' );
			if ( 'yes' === $hide_zoom ) {
				remove_theme_support( 'wc-product-gallery-zoom' );
			}
			if ( 'yes' === $hide_lightbox ) {
				remove_theme_support( 'wc-product-gallery-lightbox' );
			}
			if ( 'yes' === $hide_gallery ) {
				remove_theme_support( 'wc-product-gallery-slider' );
			}
		}
	}

	/**
	 * send variation image in single product page
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function wc_variation_images_load_variation_images() {
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

		wp_die();
	}
}
