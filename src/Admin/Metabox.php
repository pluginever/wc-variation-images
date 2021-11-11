<?php
/**
 * WC Variation Images Metabox Class
 *
 *
 * @package     PluginEver\WC_Variation_Images\Admin
 * @since     1.0.0
 */

namespace PluginEver\WC_Variation_Images\Admin;

use \ByteEver\PluginFramework\v1_0_0 as Framework;

defined( 'ABSPATH' ) || exit;

/**
 * Class Metabox
*/
class Metabox {
	/**
	 * constructor
	*/
	public function __construct() {
		add_action( 'woocommerce_product_after_variable_attributes', array( __CLASS__, 'upload_images' ), 10, 3 );
		add_action( 'woocommerce_save_product_variation', array( __CLASS__, 'save_product_variation' ), 10, 2 );
	}

	/**
	 * Upload variation images
	 *
	 * @param int $loop Loop
	 * @param mixed $variation_data Variation Data
	 * @param mixed $variation Variation
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function upload_images( $loop, $variation_data, $variation ) {
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
				   class="button wc-variation-images-add-image"><?php esc_html_e( 'Add Variation Images', 'wc-variation-images' ) ?></a>
			</p>
		</div>
		<?php
	}

	/**
	 * Save product variations
	 *
	 * @param int $variation_id Variation ID
	 * @param int $i Loop
	 *
	 * @return void|int
	 * @since 1.0.0
	 */
	public static function save_product_variation( $variation_id, $i ) {
		$attachment_ids = array();

		if ( isset( $_POST['wc_variation_images_image_variation_thumb'][ $variation_id ] ) ) {
			//sanitize
			$attachment_ids = array_map( 'absint', $_POST['wc_variation_images_image_variation_thumb'][ $variation_id ] );
			//filter
			$attachment_ids = array_filter( $attachment_ids );
			//unique
			$attachment_ids = array_unique( $attachment_ids );
		}
		update_post_meta( $variation_id, 'wc_variation_images_variation_images', $attachment_ids );
	}
}
