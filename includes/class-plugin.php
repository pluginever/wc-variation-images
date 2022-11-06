<?php

namespace WC_Variation_Images;

// don't call the file directly.

defined( 'ABSPATH' ) || exit();

/**
 * Main plugin class.
 *
 * @since 1.0.0
 * @package WC_Variation_Images
 */
final class Plugin extends Framework\Premium_Plugin {
	/**
	 * Setup plugin constants.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function define_constants() {
		define( 'WC_VARIATION_IMAGES_VERSION', $this->get_version() );
		define( 'WC_VARIATION_IMAGES_FILE', $this->get_file() );
		define( 'WC_VARIATION_IMAGES_PATH', $this->get_plugin_path() );
		define( 'WC_VARIATION_IMAGES_URL', $this->get_plugin_url() );
		define( 'WC_VARIATION_IMAGES_ASSETS', $this->get_assets_url() );
	}

	/**
	 * Setup plugin hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init_hooks() {
		add_action( 'plugins_loaded', array( $this, 'add_controllers' ) );
	}

	/**
	 * Initialize controllers.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function add_controllers() {
		$this->add_controller(
			[
				'installer' => Installer::class,
				'frontend'  => Frontend::class,
				'admin'     => Admin\Admin::class,
			]
		);
	}
}
