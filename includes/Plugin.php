<?php

namespace WooCommerceVariationImages;

defined( 'ABSPATH' ) || exit;

/**
 * Class Plugin.
 *
 * @since 1.0.0
 *
 * @package WooCommerceVariationImages
 */
final class Plugin extends ByteKit\Plugin {

	/**
	 * Plugin constructor.
	 *
	 * @param array $data The plugin data.
	 *
	 * @since 1.0.0
	 */
	protected function __construct( $data ) {
		parent::__construct( $data );
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Define constants.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function define_constants() {
		define( 'WC_VARIATION_IMAGES_VERSION', $this->get_version() );
		define( 'WC_VARIATION_IMAGES_FILE', $this->get_file() );
		define( 'WC_VARIATION_IMAGES_PATH', $this->get_dir_path() );
		define( 'WC_VARIATION_IMAGES_INCLUDES', WC_VARIATION_IMAGES_PATH . '/includes' );
		define( 'WC_VARIATION_IMAGES_URL', plugins_url( '', WC_VARIATION_IMAGES_FILE ) );
		define( 'WC_VARIATION_IMAGES_ASSETS_URL', $this->get_assets_url() );
		define( 'WC_VARIATION_IMAGES_TEMPLATES_DIR', WC_VARIATION_IMAGES_PATH . '/templates' );
	}

	/**
	 * Include required files.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function includes() {
		require_once __DIR__ . '/functions.php';
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init_hooks() {
		register_activation_hook( $this->get_file(), array( $this, 'install' ) );
		add_action( 'before_woocommerce_init', array( $this, 'on_before_woocommerce_init' ) );
		add_action( 'admin_notices', array( $this, 'dependencies_notices' ) );
		add_action( 'woocommerce_init', array( $this, 'init' ), 0 );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts_handler' ) );
	}

	/**
	 * Run on plugin activation.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function install() {
		// Add option for installed time.
		add_option( 'wc_variation_images_installed', wp_date( 'U' ) );
	}

	/**
	 * Run on before WooCommerce init.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function on_before_woocommerce_init() {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', $this->get_file(), true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', $this->get_file(), true );
		}
	}

	/**
	 * Missing dependencies notice.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function dependencies_notices() {
		if ( $this->is_plugin_active( 'woocommerce' ) ) {
			return;
		}
		$notice = sprintf(
		/* translators: 1: plugin name 2: WooCommerce */
			__( '%1$s requires %2$s to be installed and active.', 'wc-variation-images' ),
			'<strong>' . esc_html( $this->get_name() ) . '</strong>',
			'<strong>' . esc_html__( 'WooCommerce', 'wc-variation-images' ) . '</strong>'
		);

		echo '<div class="notice notice-error"><p>' . wp_kses_post( $notice ) . '</p></div>';
	}

	/**
	 * Init the plugin after plugins_loaded so environment variables are set.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init() {
		$this->set( Actions::class );
		if ( is_admin() ) {
			$this->set( Admin\Admin::class );
			$this->set( Admin\SettingsAPI::class );
			$this->set( Admin\Notices::class );
		}

		// Init action.
		do_action( 'wc_variation_images_init' );
	}

	/**
	 * Enqueue Scripts.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function frontend_scripts_handler() {
		wp_enqueue_style( 'wc-variation-images-frontend', WC_VARIATION_IMAGES_ASSETS_URL . 'css/frontend.css', array(), WC_VARIATION_IMAGES_VERSION );

		wp_register_script( 'wc-variation-images-frontend', WC_VARIATION_IMAGES_ASSETS_URL . 'js/frontend.js', array( 'jquery' ), WC_VARIATION_IMAGES_VERSION, true );
		wp_localize_script(
			'wc-variation-images-frontend',
			'WC_VARIATION_IMAGES',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'wc_variation_images' ),
			)
		);

		wp_enqueue_script( 'wc-variation-images-frontend' );
	}
}
