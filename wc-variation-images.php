<?php
/**
 * Plugin Name: WooCommerce Variation Images
 * Plugin URI:  https://www.pluginever.com/plugins/woocommerce-variation-images
 * Description: Adds additional gallery images per product variation.
 * Version:     1.0.3
 * Author:      pluginever
 * Author URI:  https://pluginever.com
 * Donate link: https://www.pluginever.com
 * License:     GPLv2+
 * Text Domain: wc-variation-images
 * Domain Path: /i18n/languages/
 * Tested up to: 5.8.2
 * WC requires at least: 3.0.0
 * WC tested up to: 6.0.0
 */

/**
 * Copyright (c) 2019 pluginever (email : support@pluginever.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main initiation class
 *
 * @since 1.0.0
 */
final class WC_Variation_Images {
	/**
	 * WC_Variation_Images version.
	 *
	 * @var string
	 */
	public $version = '1.0.2';

	/**
	 * The single instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @var WC_Variation_Images
	 */
	protected static $_instance = null;

	/**
	 * Main Plugin Instance.
	 *
	 * Ensures only one instance of plugin is loaded or can be loaded.
	 *
	 * @return WC_Variation_Images - Main instance.
	 * @since 1.0.0
	 * @static
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wc-variation-images' ), '1.0.0' );
	}

	/**
	 * Universalizing instances of this class is forbidden.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wc-variation-images' ), '1.0.0' );
	}


	/**
	 * WC_Variation_Images constructor.
	 */
	protected function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
		add_action( 'admin_notices', array( $this, 'wc_missing_notice' ) );
	}

	/**
	 * Define EverProjects Constants.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	private function define_constants() {
		define( 'WC_VARIATION_IMAGES_VERSION', $this->version );
		define( 'WC_VARIATION_IMAGES_FILE', __FILE__ );
		define( 'WC_VARIATION_IMAGES_PATH', dirname( WC_VARIATION_IMAGES_FILE ) );
		define( 'WC_VARIATION_IMAGES_INCLUDES', WC_VARIATION_IMAGES_PATH . '/includes' );
		define( 'WC_VARIATION_IMAGES_URL', plugins_url( '', WC_VARIATION_IMAGES_FILE ) );
		define( 'WC_VARIATION_IMAGES_ASSETS_URL', WC_VARIATION_IMAGES_URL . '/assets' );
		define( 'WC_VARIATION_IMAGES_TEMPLATES_DIR', WC_VARIATION_IMAGES_PATH . '/templates' );
	}


	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {

		require_once( WC_VARIATION_IMAGES_INCLUDES . '/class-scripts.php' );

		require_once( WC_VARIATION_IMAGES_INCLUDES . '/action-functions.php' );
		require_once( WC_VARIATION_IMAGES_INCLUDES . '/core-functions.php' );
		require_once( WC_VARIATION_IMAGES_INCLUDES . '/functions-ajax.php' );

		if ( is_admin() ) {
			require_once( WC_VARIATION_IMAGES_INCLUDES . '/admin/functions-metabox.php' );
			require_once( WC_VARIATION_IMAGES_INCLUDES . '/admin/class-settings-api.php' );
			require_once( WC_VARIATION_IMAGES_INCLUDES . '/admin/class-settings.php' );
		}

	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 1.0.0
	 */
	private function init_hooks() {
		register_activation_hook( __FILE__, array( __CLASS__, 'install' ) );
		add_action( 'init', array( __CLASS__, 'localization_setup' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
	}

	/**
	 * setup necessary settings data
	 *
	 * @since 1.0.0
	 * @internal
	 *
	 */
	public static function install() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		return is_plugin_active( 'woocommerce/woocommerce.php' );
	}

	/**
	 * WooCommerce plugin dependency notice
	 * @since 1.0.0
	 */
	public function wc_missing_notice() {
		if ( ! $this->install() ) {
			$message = sprintf( __( '<strong>WooCommerce Variaion Images</strong> requires <strong>WooCommerce</strong> installed and activated. Please Install %s WooCommerce. %s', 'wc-variation-images' ),
				'<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">', '</a>' );
			echo sprintf( '<div class="notice notice-error"><p>%s</p></div>', $message );
		}
	}

	/**
	 * Initialize plugin for localization
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public static function localization_setup() {
		load_plugin_textdomain( 'wc-variation-images', false, plugin_basename( dirname( __FILE__ ) ) . '/i18n/languages/' );
	}

	/**
	 * Plugin action links
	 *
	 * @param array $links
	 *
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$links[] = '<a href="https://www.pluginever.com/docs/wc-variation-images/">' . __( 'Documentation', 'wc-variation-images' ) . '</a>';
		if ( ! self::is_pro_installed() ) {
			$links[] = '<a href="https://www.pluginever.com/plugins/wc-variation-images-pro/?utm_source=plugin_action_link&utm_medium=link&utm_campaign=wc-variation-images&utm_content=Upgrade%20to%20Pro" style="color: red;font-weight: bold;" target="_blank">' . __( 'Upgrade to PRO', 'wc-variation-images' ) . '</a>';
		}

		return $links;
	}


	/**
	 * Determines if the pro version installed.
	 *
	 * @return bool
	 * @since 1.0.0
	 *
	 */
	public static function is_pro_installed() {
		return is_plugin_active( 'wc-variation-images-pro/wc-variation-images-pro.php' ) == true;
	}

	/**
	 * Get the plugin url.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Plugin base path name getter.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function plugin_basename() {
		return plugin_basename( __FILE__ );
	}

	/**
	 * Get the template path.
	 *
	 * @return string
	 */
	public function template_path() {
		return WC_VARIATION_IMAGES_TEMPLATES_DIR;
	}

}

function wc_variation_images() {
	return WC_Variation_Images::instance();
}

//fire off the plugin
wc_variation_images();

