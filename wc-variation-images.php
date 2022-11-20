<?php
/**
 * Plugin Name: Product Variation Images for WooCommerce
 * Plugin URI:  https://www.pluginever.com/plugins/woocommerce-variation-images
 * Description: Adds additional gallery images per product variation.
 * Version:     1.0.3
 * Author:      PluginEver
 * Author URI:  https://pluginever.com
 * Donate link: https://www.pluginever.com
 * License:     GPLv2+
 * Text Domain: wc-variation-images
 * Domain Path: /i18n/languages/
 * Tested up to: 6.1
 * WC requires at least: 3.0.0
 * WC tested up to: 7.1
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
	public $version = '1.0.3';

	/**
	 * Minimum PHP version required
	 *
	 * @var string
	 */
	private $min_php = '5.6.0';

	/**
	 * admin notices
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $notices = array();

	/**
	 * The single instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @var WC_Variation_Images
	 */
	protected static $instance = null;

	/**
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $plugin_name = 'Woocommerce Variation Images';

	/**
	 * WC_Variation_Images constructor.
	 */
	public function __construct() {
		register_activation_hook( __FILE__, array( $this, 'activation_check' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ), 15 );
		add_action( 'init', array( $this, 'localization_setup' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
		if ( $this->is_plugin_compatible() ) {
			$this->define_constants();
			$this->includes();
			do_action( 'wc_variation_images_loaded' );
		}
	}

	/**
	 * Checks the server environment and other factors and deactivates plugins as necessary.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 */
	public function activation_check() {

		if ( ! version_compare( PHP_VERSION, $this->min_php, '>=' ) ) {

			deactivate_plugins( plugin_basename( __FILE__ ) );

			$message = sprintf( '%s could not be activated The minimum PHP version required for this plugin is %1$s. You are running %2$s.', $this->plugin_name, $this->min_php, PHP_VERSION );
			wp_die( $message );
		}

	}

	/**
	 * Displays any admin notices added
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 */
	public function admin_notices() {
		$notices = (array) array_merge( $this->notices, get_option( sanitize_key( $this->plugin_name ), [] ) );
		foreach ( $notices as $notice_key => $notice ) :
			?>
			<div class="notice notice-<?php echo sanitize_html_class( $notice['class'] ); ?>">
				<p><?php echo wp_kses( $notice['message'], array(
						'a'      => array( 'href' => array() ),
						'strong' => array()
					) ); ?></p>
			</div>
			<?php
			update_option( sanitize_key( $this->plugin_name ), [] );
		endforeach;
	}

	/**
	 * Initialize plugin for localization
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function localization_setup() {
		load_plugin_textdomain( 'wc-variation-images', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}


	/**
	 * Determines if the pro version installed.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_pro_installed() {
		return is_plugin_active( 'wc-variation-images-pro/wc-variation-images-pro.php' ) == true;
	}

	/**
	 * Plugin action links
	 *
	 * @param  array $links
	 *
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$links[] = '<a href="https://www.pluginever.com/docs/wc-variation-images/">' . __( 'Documentation', 'wc-variation-images' ) . '</a>';
		if ( ! $this->is_pro_installed() ) {
			$links[] = '<a href="https://www.pluginever.com/plugins/wc-variation-images-pro/?utm_source=plugin_action_link&utm_medium=link&utm_campaign=wc-variation-images&utm_content=Upgrade%20to%20Pro" style="color: red;font-weight: bold;" target="_blank">' . __( 'Upgrade to PRO', 'wc-variation-images' ) . '</a>';
		}

		return $links;
	}

	/**
	 * Determines if the plugin compatible.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	protected function is_plugin_compatible() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$this->add_notice( 'error', sprintf(
				'<strong>%s</strong> requires <strong>WooCommerce</strong> installed and active.',
				$this->plugin_name
			) );

			return false;
		}

		return true;
	}

	/**
	 * Adds an admin notice to be displayed.
	 *
	 * @since 1.0.0
	 *
	 * @param string $class the notice class
	 * @param string $message the notice message body
	 */
	public function add_notice( $class, $message ) {

		$notices = get_option( sanitize_key( $this->plugin_name ), [] );
		if ( is_string( $message ) && is_string( $class ) && ! wp_list_filter( $notices, array( 'message' => $message ) ) ) {

			$notices[] = array(
				'message' => $message,
				'class'   => $class
			);

			update_option( sanitize_key( $this->plugin_name ), $notices );
		}

	}

	/**
	 * Define EverProjects Constants.
	 *
	 * @since 1.0.0
	 * @return void
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
	 * Get the plugin url.
	 *
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', WC_VARIATION_IMAGES_FILE ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( WC_VARIATION_IMAGES_FILE ) );
	}

	/**
	 * Get the template path.
	 *
	 * @return string
	 */
	public function template_path() {
		return WC_VARIATION_IMAGES_TEMPLATES_DIR;
	}

	/**
	 * Main WC_Variation_Images Instance.
	 *
	 * Ensures only one instance of WC_Variation_Images is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return WC_Variation_Images - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}

function wc_variation_images() {
	return WC_Variation_Images::instance();
}

//fire off the plugin
wc_variation_images();

