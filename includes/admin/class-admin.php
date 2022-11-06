<?php

namespace WC_Variation_Images\Admin;

use WC_Variation_Images\Controller;
use WC_Variation_Images\Framework;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Admin class
 *
 * @package PluginEver\WC_Variation_Images\Admin
 */
class Admin extends Controller {

	/**
	 * Set up the controller.
	 *
	 * Load files or register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function init() {
		add_action( 'init', array( $this, 'add_controllers' ) );
		add_filter( 'woocommerce_screen_ids', array( $this, 'screen_ids' ) );
		add_action( 'admin_menu', array( $this, 'register_nav_items' ), 20 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Register admin controllers.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function add_controllers() {
		// Register admin controllers here.
		$this->add_controller(
			[
				'admin_settings' => Settings::class,
			]
		);
	}

	/**
	 * Add the plugin screens to the WooCommerce screens
	 *
	 * @param  array $ids Screen ids.
	 * @return array
	 */
	public function screen_ids( $ids ) {
		$ids[] = 'woocommerce_page_wc-variation-images-settings';
		return $ids;
	}

	/**
	 * Registers the navigation items in the WC Navigation Menu.
	 *
	 * @since 1.0.0
	 */
	public static function register_nav_items() {
		if ( function_exists( 'wc_admin_connect_page' ) ) {
			wc_admin_connect_page(
				array(
					'id'        => 'woocommerce_page_wc-variation-images',
					'parent'    => 'woocommerce_page_wc',
					'screen_id' => 'woocommerce_page_wc-variation-images',
					'title'     => __( 'WooCommerce Variation Images Settings', 'wc-variation-images' ),
				)
			);
		}
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_scripts() {
		$this->get_plugin()->register_style( 'wc-variation-images-admin', 'css/admin.css' );
		$this->get_plugin()->register_script( 'wc-variation-images-admin', 'js/admin.js' );
	}

}
