<?php
/**
 * WC Variation Images Scripts Functions
 *
 *
 * @package     PluginEver\WC_Variation_Images
 * @since     1.0.0
 */

namespace PluginEver\WC_Variation_Images;

defined( 'ABSPATH' ) || exit;

/**
 * Class Scripts
 *
 * @package     PluginEver\WC_Variation_Images
 */
class Scripts {
	/**
	 * Script version number
	 *
	 * @var integer
	 */
	protected $version;

	/**
	 * Script and style suffix
	 *
	 * @var string
	 */
	protected $suffix;

	/**
	 * Class constructors
	 */
	public function __construct() {
		$this->version = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : wc_variation_images();
		$this->suffix  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_scripts_handler' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'frontend_scripts_handler' ) );
		add_action( 'admin_footer', array( __CLASS__, 'admin_template_js' ) );
	}

	/**
	 * Enqueue admin scripts
	 *
	 * @since 1.0.0
	 */
	public static function admin_scripts_handler() {
		wc_variation_images()->register_style( 'wc-variation-images', "/css/admin/admin-style.css" );
		wp_enqueue_style( 'wc-variation-images' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wc_variation_images()->register_script( 'wc-variation-images', 'js/admin/admin-script.js', [ 'jquery', 'jquery-ui-sortable' ] );
		wp_localize_script( 'wc-variation-images', 'WC_VARIATION_IMAGES', [
			'ajaxurl'                    => admin_url( 'admin-ajax.php' ),
			'nonce'                      => wp_create_nonce( 'wc_variation_images' ),
			'variation_image_title'      => __( 'Variation Images', 'wc-variation-images' ),
			'add_variation_image_text'   => __( 'Add Additional Images', 'wc-variation-images' ),
			'admin_media_title'          => __( 'Variation Images', 'wc-variation-images' ),
			'admin_media_add_image_text' => __( 'Add to Variation', 'wc-variation-images' ),
			'admin_tip_message'          => __( 'Click on link below to add additional images. Click on image itself to remove the image. Click and drag image to re-order the image position.', 'wc-variation-images' ),
		] );
		wp_enqueue_script( 'wc-variation-images' );
	}

	/**
	 * Enqueue frontend scripts
	 *
	 * @since 1.0.0
	 */
	public function frontend_scripts_handler() {
		wc_variation_images()->register_style( 'wc-variation-images', 'css/frontend/frontend-style.css' );
		wp_enqueue_style( 'wc-variation-images' );
		self::add_inline_style();

		wc_variation_images()->register_script( 'wc-variation-images', "js/frontend/frontend-script.js", [ 'jquery' ] );
		wp_localize_script( 'wc-variation-images', 'WC_VARIATION_IMAGES', [
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'wc_variation_images' )
		] );

		wp_enqueue_script( 'wc-variation-images' );
	}

	/**
	 * load html in admin footer
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public static function admin_template_js() {
		?>
		<script type="text/html" id="tmpl-wc-variation-images">
			<li class="wc-variation-images-image-info">
				<input type="hidden" name="wc_variation_images_image_variation_thumb[{{data.variation_id}}][]"
					   value="{{data.image_id}}">
				<img src="{{data.image_url}}">
				<span class="wc-variation-images-remove-image dashicons dashicons-dismiss"></span>
			</li>
		</script>
		<?php
	}

	/**
	 * Add inline style
	 *
	 * @since 1.0.0
	 */
	public static function add_inline_style() {
		global $post;
		if ( ! $post ) {
			return;
		}
		$product = wc_get_product( $post->ID );
		if ( is_product() && 'variable' == $product->get_type() ) {
			$plugin_settings = get_option( 'wc_variation_images_settings' );

			$gallery_large_width       = ! empty( $plugin_settings['gallery_width'] ) ? $plugin_settings['gallery_width'] : 30;
			$gallery_medium_width      = ! empty( $plugin_settings['gallery_medium_width'] ) ? $plugin_settings['gallery_medium_width'] : 0;
			$gallery_small_width       = ! empty( $plugin_settings['gallery_small_width'] ) ? $plugin_settings['gallery_small_width'] : 720;
			$gallery_extra_small_width = ! empty( $plugin_settings['gallery_extra_small_width'] ) ? $plugin_settings['gallery_extra_small_width'] : 320;

			ob_start();
			?>
			<style type="text/css">
				.wc-variation-images-product-gallery {
					width: <?php echo $gallery_large_width;?>% !important;
				}

				<?php if($gallery_medium_width>0){?>
				@media only screen and (max-width: 992px) {
					.wc-variation-images-product-gallery {
						width: <?php echo $gallery_medium_width;?>px !important;
						max-width: 100% !important;
					}
				}

				<?php }?>

				<?php if($gallery_small_width>0){?>
				@media only screen and (max-width: 768px) {
					.wc-variation-images-product-gallery {
						width: <?php echo $gallery_small_width;?>px !important;
						max-width: 100% !important;
					}
				}

				<?php }?>
				<?php if($gallery_extra_small_width>0){?>
				@media only screen and (max-width: 480px) {
					.wc-variation-images-product-gallery {
						width: <?php echo $gallery_extra_small_width;?>px !important;
						max-width: 100% !important;
					}
				}

				<?php }?>
			</style>
			<?php
			$inline_css = ob_get_clean();
			$inline_css = str_ireplace( array(
				'<style type="text/css">',
				'</style>',
				"\r\n",
				"\r",
				"\n",
				"\t"
			), '', $inline_css );

			$inline_css = preg_replace( "/\s+/", ' ', $inline_css );
			$inline_css = trim( $inline_css );

			wp_add_inline_style( 'wc-variation-images', $inline_css );
		}
	}
}
