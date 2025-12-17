<?php

namespace WooCommerceVariationImages;

use WooCommerceVariationImages\Controllers\Helpers;

defined( 'ABSPATH' ) || exit;

/**
 * Class Plugin.
 *
 * @since 1.0.0
 *
 * @package WooCommerceVariationImages
 */
final class Plugin extends \WooCommerceVariationImages\ByteKit\Plugin {

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
		define( 'WCVI_VERSION', $this->get_version() );
		define( 'WCVI_PLUGIN_FILE', $this->get_file() );
		define( 'WCVI_PLUGIN_PATH', $this->get_dir_path() );
		define( 'WCVI_PLUGIN_URL', plugins_url( '', WCVI_PLUGIN_FILE ) );
		define( 'WCVI_PLUGIN_TEMPLATES_DIR', WCVI_PLUGIN_PATH . '/templates' );
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
		add_filter( 'plugin_action_links_' . $this->get_basename(), array( $this, 'plugin_action_links' ) );
		add_action( 'before_woocommerce_init', array( $this, 'on_before_woocommerce_init' ) );
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
		add_option( 'wcvi_installed', wp_date( 'U' ) );

		/**
		 * Migrating from old option to new option.
		 *
		 * @since 1.2.0
		 */
		$options = array(
			'wc_variation_images_installed'           => 'wcvi_installed',
			'wc_variation_images_hide_image_zoom'     => 'wcvi_disable_image_zoom',
			'wc_variation_images_hide_image_lightbox' => 'wcvi_disable_image_lightbox',
			'wc_variation_images_hide_image_slider'   => 'wcvi_disable_image_slider',
		);

		foreach ( $options as $option => $new_option ) {
			if ( get_option( $option ) ) {
				update_option( $new_option, get_option( $option ) );
				delete_option( $option );
			}
		}
	}

	/**
	 * Add plugin action links.
	 *
	 * @param array $links The plugin action links.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		if ( ! $this->is_plugin_active( 'wc-variation-images-pro/wc-variation-images-pro.php' ) ) {
			$links['go_pro'] = '<a href="https://pluginever.com/plugins/wc-variation-images-pro" target="_blank" style="color: #39b54a; font-weight: bold;">' . esc_html__( 'Go Pro', 'wc-variation-images' ) . '</a>';
		}

		return $links;
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
	 * Init the plugin after plugins_loaded so environment variables are set.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init() {
		$this->set( Actions::class );
		$this->set( Products::class );
		$this->set( Controllers\Helpers::class );

		if ( is_admin() ) {
			$this->set( Admin\Admin::class );
			$this->set( Admin\Settings::instance() );
			$this->set( Admin\Products::class );
			$this->set( Admin\Notices::class );
		}
		add_theme_support( 'wc-product-gallery-zoom' );

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
		wc_variation_images()->scripts->register_style( 'wc-variation-images-frontend', 'css/frontend.css' );
		wc_variation_images()->scripts->register_style( 'wc-variation-images-slider', 'css/slider.css' );
		wc_variation_images()->scripts->register_style( 'wc-variation-images-fancybox', 'css/fancybox.css' );
		wc_variation_images()->scripts->register_script( 'wc-variation-images-frontend', 'js/frontend.js', array( 'jquery' ), true );
		wc_variation_images()->scripts->register_script( 'wc-variation-images-slider', 'js/slider.js', array(), true );
		wc_variation_images()->scripts->register_script( 'wc-variation-images-fancybox', 'js/fancybox.js', array(), true );

		wp_localize_script(
			'wc-variation-images-frontend',
			'WC_VARIATION_IMAGES',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'wc_variation_images_ajax' ),
				'i18n'    => array(
					'hide_image_zoom' => get_option( 'wcvi_disable_image_zoom', 'no' ),
					'lightbox_data'   => Helpers::get_lightbox_data(),
					'slider_data'     => Helpers::get_slider_data(),
				),
			)
		);

		if ( is_product() ) {
			wp_enqueue_script( 'wc-variation-images-fancybox' );
			wp_enqueue_style( 'wc-variation-images-fancybox' );
			wp_enqueue_style( 'wc-variation-images-frontend' );
			wp_enqueue_style( 'wc-variation-images-slider' );
			wp_enqueue_script( 'wc-variation-images-slider' );
			wp_enqueue_script( 'wc-variation-images-frontend' );
		}
	}
}
