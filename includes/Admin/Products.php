<?php

namespace WooCommerceVariationImages\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Class Products.
 *
 * @since 1.0.0
 *
 * @package WooCommerceVariationImages\Admin
 */
class Products {
	/**
	 * Products constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'handle_upload_variation_images' ), 10, 3 );
		add_action( 'woocommerce_save_product_variation', array( $this, 'wc_variation_images_save_product_variation' ), 10, 1 );
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
	public function handle_upload_variation_images( $loop, $variation_data, $variation ) {
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
								<input type="hidden" name="wc_variation_images_image_variation_thumb[<?php echo esc_attr( $variation_id ); ?>][]" value="<?php echo esc_attr( $image_id ); ?>">
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
				<a href="#" data-wc_variation_images_variation_id="<?php echo absint( $variation->ID ); ?>" class="button wc-variation-images-add-image"><?php esc_html_e( 'Add Variation Images', 'wc-variation-images' ); ?></a>
			</p>
		</div>
		<?php
	}

	/**
	 * Save Variation Product.
	 *
	 * @param int $variation_id Variation ID.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function wc_variation_images_save_product_variation( $variation_id ) {
		$attachment_ids = array();
		if ( isset( $_POST['wc_variation_images_image_variation_thumb'][ $variation_id ] ) ) {
			// Sanitize.
			$attachment_ids = array_map( 'absint', $_POST['wc_variation_images_image_variation_thumb'][ $variation_id ] );
			// Filter.
			$attachment_ids = array_filter( $attachment_ids );
			// Unique.
			$attachment_ids = array_unique( $attachment_ids );
		}
		update_post_meta( $variation_id, 'wc_variation_images_variation_images', $attachment_ids );
	}
}
