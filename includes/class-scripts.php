<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Variation_Images_Scripts {

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
	 * Script constructor.
	 */

	public function __construct() {
		$this->version = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : '20181011';
		$this->suffix  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts_handler' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts_handler' ) );
		add_action( 'admin_footer', array( $this, 'admin_template_js' ) );
	}

	public function admin_scripts_handler() {
		wp_enqueue_style( 'wc-variation-images', WC_VARIATION_IMAGES_ASSETS_URL . "/css/admin.css", [], WC_VARIATION_IMAGES_VERSION );
//		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_register_script( 'wc-variation-images', WC_VARIATION_IMAGES_ASSETS_URL . "/js/admin{$this->suffix}.js", [
			'jquery',
			'jquery-ui-sortable'
		], WC_VARIATION_IMAGES_VERSION, true );
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

	public function frontend_scripts_handler() {
		wp_enqueue_style( 'wc-variation-images', WC_VARIATION_IMAGES_ASSETS_URL . "/css/frontend.css", [], WC_VARIATION_IMAGES_VERSION );
		$this->add_inline_style();

		wp_register_script( 'wc-variation-images', WC_VARIATION_IMAGES_ASSETS_URL . "/js/frontend{$this->suffix}.js", [ 'jquery' ], WC_VARIATION_IMAGES_VERSION, true );
		wp_localize_script( 'wc-variation-images', 'WC_VARIATION_IMAGES', [
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'wc_variation_images' )
		] );

		wp_enqueue_script( 'wc-variation-images' );
	}

	/**
	 * load html in admin footer
	 *
	 * since 1.0.0
	 *
	 * @return void
	 */
	public function admin_template_js() {
		require_once trailingslashit( WC_VARIATION_IMAGES_TEMPLATES_DIR ) . 'wc-variation-images-variation-template.php';
	}

	public function add_inline_style() {
		global $post;
		if ( ! $post ) {
			return;
		}
		$product = wc_get_product( $post->ID );
		if ( is_product() && 'variable' == $product->get_type() ) {
			$gallery_large_width       = wc_variation_images_get_settings( 'wc_variation_images_gallery_width', 30, 'wc_variation_images_gallery_settings' );
			$gallery_medium_width      = wc_variation_images_get_settings( 'wc_variation_images_gallery_medium_width', 0, 'wc_variation_images_gallery_settings' );
			$gallery_small_width       = wc_variation_images_get_settings( 'wc_variation_images_gallery_small_width', 720, 'wc_variation_images_gallery_settings' );
			$gallery_extra_small_width = wc_variation_images_get_settings( 'wc_variation_images_gallery_extra_small_width', 320, 'wc_variation_images_gallery_settings' );

			ob_start();
			?>
            <style type="text/css">
                .wc-variation-images-product-gallery {
                    max-width: <?php echo $gallery_large_width;?>% !important;
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

new WC_Variation_Images_Scripts();
