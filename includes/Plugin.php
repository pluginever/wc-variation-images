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
		add_action( 'before_woocommerce_init', array( $this, 'on_before_woocommerce_init' ) );
		add_action( 'admin_notices', array( $this, 'dependencies_notices' ) );
		add_action( 'woocommerce_init', array( $this, 'init' ), 0 );
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
		require_once WC_VARIATION_IMAGES_INCLUDES . '/class-scripts.php';

		require_once WC_VARIATION_IMAGES_INCLUDES . '/action-functions.php';
		require_once WC_VARIATION_IMAGES_INCLUDES . '/core-functions.php';
		require_once WC_VARIATION_IMAGES_INCLUDES . '/functions-ajax.php';

		if ( is_admin() ) {
			require_once WC_VARIATION_IMAGES_INCLUDES . '/admin/functions-metabox.php';
			require_once WC_VARIATION_IMAGES_INCLUDES . '/admin/class-settings-api.php';
			require_once WC_VARIATION_IMAGES_INCLUDES . '/admin/class-settings.php';
		}

		// Init action.
		do_action( 'wc_variation_images_init' );
	}
}
