<?php
/**
 * WC Variation Images Main Plugin File
 *
 *
 * @package     PluginEver\WC_Variation_Images
 * @since     1.0.0
 */

namespace PluginEver\WC_Variation_Images;

use \ByteEver\PluginFramework\v1_0_0 as Framework;

defined( 'ABSPATH' ) || exit;

/**
 * Class Plugin
 * @package PluginEver\WC_Variation_Images
 */
class Plugin extends Framework\Plugin {
	/**
	 * Single instance of plugin.
	 *
	 * @since 1.0.0
	 * @var object
	 */
	protected static $instance;

	/**
	 * Returns the main Plugin instance.
	 *
	 * Ensures only one instance is loaded at one time.
	 *
	 * @return Plugin
	 * @since 1.0.0
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Checks the environment on loading WordPress.
	 *
	 * Check the required environment, dependencies
	 * if not met then add admin error and return false.
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function is_environment_compatible() {
		$ret =  parent::is_environment_compatible();
		if ( $ret && ! $this->is_plugin_active( 'woocommerce' ) ) {
			$this->add_admin_notice(
				sprintf(
					'%s requires WooCommerce to function. Please %sinstall WooCommerce &raquo;%s',
					'<strong>' . $this->get_plugin_name() . '</strong>',
					'<a href="' . esc_url( admin_url( 'plugin-install.php' ) ) . '">', '</a>'
				),
				[
					'notice_class' => 'error'
				]
			);

			$this->deactivate_plugin();

			return false;

		}

		return $ret;
	}

	/**
	 * Gets the main plugin file.
	 *
	 * return __FILE__;
	 *
	 * @return string the full path and filename of the plugin file
	 * @since 1.0.0
	 */
	public function get_plugin_file() {
		return WC_VARIATION_IMAGES_PLUGIN_FILE ;
	}

	/**
	 * Initialize the plugin.
	 *
	 * The method is automatically called as soon
	 * the class instance is created.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function init() {
		$this->register_service( LifeCycle::class, $this );
		$this->register_service( Insight::class, $this );
	}
}

