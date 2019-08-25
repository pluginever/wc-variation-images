<?php

class Admin {
	/**
	 * The single instance of the class.
	 *
	 * @var Admin
	 * @since 1.0.0
	 */
	protected static $init = null;

	/**
	 * Frontend Instance.
	 *
	 * @since 1.0.0
	 * @static
	 * @return Admin - Main instance.
	 */
	public static function init() {
		if ( is_null( self::$init ) ) {
			self::$init = new self();
			self::$init->setup();
		}

		return self::$init;
	}

	/**
	 * Initialize all Admin related stuff
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function setup() {
		$this->includes();
		$this->init_hooks();
		$this->instance();
	}

	/**
	 * Includes all files related to admin
	 */
	public function includes() {
		require_once dirname( __FILE__ ) . '/class-admin-menu.php';
		require_once dirname( __FILE__ ) . '/class-metabox.php';
		require_once dirname( __FILE__ ) . '/class-settings-api.php';
		require_once dirname( __FILE__ ) . '/class-settings.php';
		require_once dirname( __FILE__ ) . '/class-hooks.php';
	}

	/**
	 * Fire all hook
	 *
	 * since 1.0.0
	 */
	private function init_hooks() {
		add_action( 'admin_init', array( $this, 'buffer' ), 1 );
		add_action( 'init', array( $this, 'includes' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_footer', array( $this, 'admin_template_js' ) );
	}


	/**
	 * Fire off all the instances
	 *
	 * @since 1.0.0
	 */
	protected function instance() {
		new Admin_Menu();
		new Hooks();
		new MetaBox();
		new Settings();
	}

	/**
	 * Output buffering allows admin screens to make redirects later on.
	 *
	 * @since 1.0.0
	 */
	public function buffer() {
		ob_start();
	}

	/**
	 * load script in wordpress admin
	 *
	 * since 1.0.0
	 *
	 * @param $hook
	 *
	 * @return void
	 */
	public function enqueue_scripts( $hook ) {
		$suffix = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? '' : '.min';
		wp_register_style( 'wc-variation-images', WC_VARIATION_IMAGES_ASSETS_URL . "/css/admin{$suffix}.css", [], WC_VARIATION_IMAGES_VERSION );
		wp_register_script( 'wc-variation-images', WC_VARIATION_IMAGES_ASSETS_URL . "/js/admin/admin{$suffix}.js", [ 'jquery' ], WC_VARIATION_IMAGES_VERSION, true );
		wp_localize_script( 'wc-variation-images', 'wpwvi', [
			'ajaxurl'                    => admin_url( 'admin-ajax.php' ),
			'nonce'                      => wp_create_nonce( 'wc_variation_images' ),
			'variation_image_title'      => __( 'Variation Images', 'wc-variation-images' ),
			'add_variation_image_text'   => __( 'Add Additional Images', 'wc-variation-images' ),
			'admin_media_title'          => __( 'Variation Images', 'wc-variation-images' ),
			'admin_media_add_image_text' => __( 'Add to Variation', 'wc-variation-images' ),
			'admin_tip_message'          => __( 'Click on link below to add additional images. Click on image itself to remove the image. Click and drag image to re-order the image position.', 'wc-variation-images' ),
		] );
		wp_enqueue_style( 'wc-variation-images' );
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
		require_once trailingslashit( WC_VARIATION_IMAGES_TEMPLATES_DIR ) . "wpwvi-variation-template.php";
	}


}

Admin::init();
