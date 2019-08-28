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

	public function admin_scripts_handler(){
		wp_enqueue_style( 'wc-variation-images', WC_VARIATION_IMAGES_ASSETS_URL . "/css/admin.css", [], WC_VARIATION_IMAGES_VERSION );
		wp_register_script( 'wc-variation-images', WC_VARIATION_IMAGES_ASSETS_URL . "/js/admin{$this->suffix}.js", [ 'jquery' ], WC_VARIATION_IMAGES_VERSION, true );
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

	public function frontend_scripts_handler(){
		wp_enqueue_style( 'wc-variation-images', WC_VARIATION_IMAGES_ASSETS_URL . "/css/frontend.css", [], WC_VARIATION_IMAGES_VERSION );

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
}

new WC_Variation_Images_Scripts();
