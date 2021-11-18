<?php
/**
 * WC Variation Images Main Plugin File
 *
 *
 * @package     PluginEver\WC_Variation_Images
 * @since     1.0.0
 */

namespace PluginEver\WC_Variation_Images;

use \ByteEver\PluginFramework\v1_0_0 as Framework;
use PluginEver\WC_Variation_Images\Admin\Metabox;
use PluginEver\WC_Variation_Images\Admin\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Class Plugin
 * @package PluginEver\WC_Variation_Images
 */
class Plugin extends Framework\Plugin {
	use Framework\Traits\Option;
	/**
	 * Single instance of plugin.
	 *
	 * @since 1.0.0
	 * @var object
	 */
	protected static $instance;

	/**
	 * Plugin file
	 *
	 * @since 1.0.0
	 * @var object
	 */
	public $plugin_file;

	/**
	 * Returns the main Plugin instance.
	 *
	 * Ensures only one instance is loaded at one time.
	 *
	 * @return Plugin
	 * @since 1.0.0
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Checks the environment on loading WordPress.
	 *
	 * Check the required environment, dependencies
	 * if not met then add admin error and return false.
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function is_environment_compatible() {
		$ret = parent::is_environment_compatible();
		if ( $ret && ! $this->is_plugin_active( 'woocommerce' ) ) {
			$this->add_admin_notice(
				sprintf(
					'%s requires WooCommerce to function. Please %sinstall WooCommerce &raquo;%s',
					'<strong>' . $this->get_plugin_name() . '</strong>',
					'<a href="' . esc_url( admin_url( 'plugin-install.php' ) ) . '">', '</a>'
				),
				[
					'notice_class' => 'error'
				]
			);

			$this->deactivate_plugin();

			return false;

		}

		return $ret;
	}

	/**
	 * Gets the main plugin file.
	 *
	 * return __FILE__;
	 *
	 * @return string the full path and filename of the plugin file
	 * @since 1.0.0
	 */
	public function get_plugin_file() {
		$this->plugin_file = WC_VARIATION_IMAGES_PLUGIN_FILE;

		return WC_VARIATION_IMAGES_PLUGIN_FILE;
	}

	/**
	 * Initialize the plugin.
	 *
	 * The method is automatically called as soon
	 * the class instance is created.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function init() {
		$this->register_service( LifeCycle::class, $this );
		$this->register_service( Ajax::class, $this );
		$this->register_hooks();
		if ( is_admin() ) {
			$this->register_service( Settings::class, $this );
			$this->register_service( Metabox::class, $this );
		}
	}

	/**
	 * Add necessary hooks for this plugin
	 *
	 * @since 1.0.0
	*/
	public function register_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
		add_filter( 'woocommerce_single_product_image_gallery_classes', array( __CLASS__, 'add_gallery_class' ) );
		add_action( 'wp', array( __CLASS__, 'gallery_control' ), 100 );
		add_filter( 'woocommerce_single_product_carousel_options', array( __CLASS__, 'slider_navigations' ) );
		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
			add_action( 'admin_footer', array( __CLASS__, 'admin_template_js' ) );
		}
	}

	/**
	 * Enqueue frontend scripts
	 *
	 * @since 1.0.0
	*/
	public function enqueue_frontend_scripts() {
		$this->register_style('wc-variation-images', 'css/frontend-style.css' );
		wp_enqueue_style( 'wc-variation-images' );

		$this->register_script( 'wc-variation-images', 'js/frontend-script.js', array( 'jquery' ) );
		wp_localize_script( 'wc-variation-images', 'WC_VARIATION_IMAGES', [
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'wc_variation_images' )
		] );
		wp_enqueue_script( 'wc-variation-images' );

		// add inline styles
		global $post;
		if ( ! $post ) {
			return;
		}
		$product = wc_get_product( $post->ID );

		if ( is_product() && 'variable' == $product->get_type() ) {
			$gallery_large_width       = self::get_option( 'wc_variation_images_settings[gallery_width]', 30 );
			$gallery_medium_width      = self::get_option( 'wc_variation_images_settings[gallery_medium_width]', 0 );
			$gallery_small_width       = self::get_option( 'wc_variation_images_settings[gallery_small_width]', 720 );
			$gallery_extra_small_width = self::get_option( 'wc_variation_images_settings[gallery_extra_small_width]', 320 );

			ob_start();
			?>
			<style type="text/css">
				.wc-variation-images-product-gallery {
					width: <?php echo $gallery_large_width;?>% !important;
				}

				<?php if ( $gallery_medium_width > 0 ) { ?>
				@media only screen and ( max-width: 992px ) {
					.wc-variation-images-product-gallery {
						width: <?php echo $gallery_medium_width;?>px !important;
						max-width: 100% !important;
					}
				}

				<?php } ?>

				<?php if ( $gallery_small_width > 0 ) { ?>
				@media only screen and ( max-width: 768px ) {
					.wc-variation-images-product-gallery {
						width: <?php echo $gallery_small_width;?>px !important;
						max-width: 100% !important;
					}
				}

				<?php } ?>
				<?php if ( $gallery_extra_small_width > 0 ){ ?>
				@media only screen and ( max-width: 480px ) {
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

	/**
	 * add class woocommece single product page
	 *
	 * @param array $class Class
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public static function add_gallery_class( $class ) {
		$class[] = sanitize_html_class( 'wc-variation-images-product-gallery' );

		return $class;
	}

	/**
	 * Add gallery controls for the variations
	 *
	 * @since 1.0.0
	*/
	public static function gallery_control() {
		global $post;
		if ( ! $post ) {
			return;
		}
		$product = wc_get_product( $post->ID );

		if ( is_product() && 'variable' === $product->get_type() ) {
			$hide_zoom = self::get_option( 'wc_variation_images_settings[hide_image_zoom]', 'no' );
			$hide_lightbox = self::get_option('wc_variation_images_settings[hide_image_lightbox]', 'no' );
			$hide_gallery = self::get_option('wc_variation_images_settings[hide_image_slider]', 'no' );

			if ( 'yes' == $hide_zoom ) {
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
	 * Update slider navigations for the variable products
	 *
	 * @param array $options Options
	 *
	 * @since 1..03
	 * @return array
	*/
	public static function slider_navigations( $options ) {
		$slider_navigation = self::get_option('wc_variation_images_settings[gallery_navigation]', 'no' );
		$gallery_slideshow = self::get_option('wc_variation_images_settings[gallery_slideshow]', 'no' );

		if ( 'yes' === $slider_navigation ) {
			$options['directionNav'] = true;
		}

		if( 'yes' === $gallery_slideshow ) {
			$options['slideshow'] = true;
		}

		return $options;
	}
	/**
	 * Enqueue admin scripts and styles
	 *
	 * @since 1.0.0
	 */
	public function enqueue_admin_scripts() {
		$current_screen = get_current_screen();
		if ( 'product' === $current_screen->id ) {
			$this->register_style( 'wc-variation-images', 'css/admin-style.css' );
			wp_enqueue_style( 'wc-variation-images' );
			wp_enqueue_script( 'jquery-ui-sortable' );
			$this->register_script('wc-variation-images', 'js/admin-script.js', array( 'jquery', 'jquery-ui-sortable' ));
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
}

