<?php

namespace PluginEver\VariationImages;

use PluginEver\VariationImages\Controllers\Helpers;

defined( 'ABSPATH' ) || exit;

/**
 * Class Plugin.
 *
 * @since 1.0.0
 *
 * @package PluginEver\VariationImages
 */
class Plugin extends B8\App {

	/**
	 * Components to register.
	 *
	 * @since 1.0.0
	 * @var array<int|string, class-string>
	 */
	protected array $components = array(
		Installer::class,
	);

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function bootstrap(): void {
		define( 'WCVI_VERSION', $this->version );
		define( 'WCVI_PLUGIN_FILE', $this->file );
		define( 'WCVI_PLUGIN_URL', plugins_url( '', WCVI_PLUGIN_FILE ) );

		register_activation_hook( $this->file, array( $this, 'install' ) );
		add_action( 'woocommerce_loaded', array( $this, 'woocommerce_loaded' ), 0 );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts_handler' ) );
		add_filter( 'plugin_action_links_' . $this->basename(), array( $this, 'plugin_action_links' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
	}

	/**
	 * Initialize the plugin.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function woocommerce_loaded(): void {
		$this->boot( $this->components );

		/**
		 * Fires after the plugin has booted its components.
		 *
		 * @since 1.0.0
		 */
		$this->do_action( 'loaded' );
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
	 * @since 1.0.0
	 * @param array<string, string> $links Plugin action links.
	 * @return array<string, string>
	 */
	public function plugin_action_links( array $links ): array {
		$settings = sprintf(
			'<a href="%s">%s</a>',
			esc_url( (string) $this->get( 'settings_url' ) ),
			esc_html__( 'Settings', 'wc-variation-images' )
		);

		$links = array_merge( array( 'settings' => $settings ), $links );

		if ( ! $this->is_pro_active() ) {
			$links['go_pro'] = sprintf(
				'<a href="%s" target="_blank" rel="noopener noreferrer" style="color: #39b54a; font-weight: bold;">%s</a>',
				esc_url( (string) $this->get( 'upgrade_url' ) ),
				esc_html__( 'Go Pro', 'wc-variation-images' )
			);
		}

		return $links;
	}

	/**
	 * Add the plugin row meta links.
	 *
	 * @since 1.0.0
	 * @param array<int, string> $links Plugin row meta links.
	 * @param string             $file  Plugin file path relative to the plugins directory.
	 * @return array<int, string>
	 */
	public function plugin_row_meta( array $links, string $file ): array {
		if ( $file !== $this->basename() ) {
			return $links;
		}

		$links[] = sprintf(
			'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
			esc_url( (string) $this->get( 'docs_url' ) ),
			esc_html__( 'Docs', 'wc-variation-images' )
		);

		$links[] = sprintf(
			'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
			esc_url( (string) $this->get( 'support_url' ) ),
			esc_html__( 'Support', 'wc-variation-images' )
		);

		return $links;
	}

	/**
	 * Whether the Pro add-on is active.
	 *
	 * @since 1.0.0
	 * @return bool True when the Pro add-on is active.
	 */
	public function is_pro_active(): bool {
		return ! empty( $this->pro_basename ) && $this->plugin_active( $this->pro_basename );
	}

	/**
	 * Init the plugin after plugins_loaded so environment variables are set.
	 *
	 * @since 1.0.0
	 * @return void
	 */
//	public function init() {
//		$this->set( Actions::class );
//		$this->set( Products::class );
//		$this->set( Controllers\Helpers::class );
//
//		if ( is_admin() ) {
//			$this->set( Admin\Admin::class );
//			$this->set( Admin\Settings::instance() );
//			$this->set( Admin\Products::class );
//			$this->set( Admin\Notices::class );
//		}
//		add_theme_support( 'wc-product-gallery-zoom' );
//
//		// Init action.
//		do_action( 'wc_variation_images_init' );
//	}

	/**
	 * Enqueue Scripts.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function frontend_scripts_handler() {
		wc_variation_images()->scripts->register_style( 'wc-variation-images-slider', 'css/slider.css' );
		wc_variation_images()->scripts->register_style( 'wc-variation-images-fancybox', 'css/fancybox.css' );

		wc_variation_images()->scripts->register_script( 'wc-variation-images-slider', 'js/slider.js', array(), true );
		wc_variation_images()->scripts->register_script( 'wc-variation-images-fancybox', 'js/fancybox.js', array(), true );
		wc_variation_images()->scripts->register_style( 'wc-variation-images-frontend', 'css/frontend.css' );
		wc_variation_images()->scripts->register_script( 'wc-variation-images-frontend', 'js/frontend.js', array( 'jquery', 'wc-variation-images-slider', 'wc-variation-images-fancybox' ), true );

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
