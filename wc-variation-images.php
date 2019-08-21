<?php
/**
 * Plugin Name: WC Variation Images
 * Plugin URI:  https://www.pluginever.com
 * Description: The Best WordPress Plugin ever made!
 * Version:     1.0.0
 * Author:      pluginever
 * Author URI:  https://www.pluginever.com
 * Donate link: https://www.pluginever.com
 * License:     GPLv2+
 * Text Domain: wc-variation-images
 * Domain Path: /i18n/languages/
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

/**
 * Main WCVariationImages Class.
 *
 * @class WCVariationImages
 */
final class WCVariationImages {
	/**
	 * WCVariationImages version.
	 *
	 * @var string
	 */
	public $version = '1.0.0';

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
	 * @var WCVariationImages
	 * @since 1.0.0
	 */
	protected static $instance = null;

	/**
	 * @var \Ever_Elements
	 */
	public $elements;

	/**
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $api_url;

	/**
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $plugin_name = 'Woocommerce Variation Images';


	/**
	 * Main WCVariationImages Instance.
	 *
	 * Ensures only one instance of WCVariationImages is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return WCVariationImages - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->setup();
		}

		return self::$instance;
	}

	/**
	 * EverProjects Constructor.
	 */
	public function setup() {
		$this->check_environment();
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
		$this->plugin_init();
		do_action( 'wc_variation_images_loaded' );
	}

	/**
	 * Ensure theme and server variable compatibility
	 */
	public function check_environment() {
		if ( version_compare( PHP_VERSION, $this->min_php, '<=' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );

			wp_die( "Unsupported PHP version Min required PHP Version:{$this->min_php}" );
		}
	}

	/**
	 * Define EverProjects Constants.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function define_constants() {

		define( 'WPWVI_VERSION', $this->version );
		define( 'WPWVI_FILE', __FILE__ );
		define( 'WPWVI_PATH', dirname( WPWVI_FILE ) );
		define( 'WPWVI_INCLUDES', WPWVI_PATH . '/includes' );
		define( 'WPWVI_URL', plugins_url( '', WPWVI_FILE ) );
		define( 'WPWVI_ASSETS_URL', WPWVI_URL . '/assets' );
		define( 'WPWVI_TEMPLATES_DIR', WPWVI_PATH . '/templates' );
	}


	/**
	 * What type of request is this?
	 *
	 * @param  string $type admin, ajax, cron or frontend.
	 *
	 * @return bool
	 */
	private function is_request( $type ) {
		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' ) && ! defined( 'REST_REQUEST' );
		}
	}


	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {

		//admin includes
		if ( $this->is_request( 'admin' ) ) {
			include_once WPWVI_INCLUDES . '/admin/class-admin.php';
		}

		//frontend includes
		if ( $this->is_request( 'frontend' ) ) {
			include_once WPWVI_INCLUDES . '/class-frontend.php';
		}

		//if ajax
		if ( $this->is_request( 'ajax' ) ) {
			include_once WPWVI_INCLUDES . '/class-ajax.php';
		}

	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 2.3
	 */
	private function init_hooks() {

		add_action( 'admin_notices', array( $this, 'admin_notices' ), 15 );
		add_action( 'init', array( $this, 'localization_setup' ) );



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
				<p><?php echo wp_kses( $notice['message'], array( 'a' => array( 'href' => array() ), 'strong' => array() ) ); ?></p>
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
	 * Plugin action links
	 *
	 * @param  array $links
	 *
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		//$links[] = '<a href="' . admin_url( 'admin.php?page=' ) . '">' . __( 'Settings', '' ) . '</a>';
		return $links;
	}

	/**
	 * since 1.0.0
	 */
	public function plugin_init() {

	}

	/**
	 * Get the plugin url.
	 *
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', WPWVI_FILE ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( WPWVI_FILE ) );
	}

	/**
	 * Get the template path.
	 *
	 * @return string
	 */
	public function template_path() {
		return WPWVI_TEMPLATES_DIR;
	}

}

function wc_variation_images() {
	return WCVariationImages::instance();
}

//fire off the plugin
wc_variation_images();
