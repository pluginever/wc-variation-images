<?php

namespace WC_Variation_Images;

defined( 'ABSPATH' ) || exit();

/**
 * CLI handler class.
 *
 * Handles CLI commands.
 *
 * @since 1.0.0
 * @package WC_Variation_Images
 */
class CLI extends Controller {

	/**
	 * Set up the controller.
	 *
	 * Load files or register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function init() {
		add_action( 'after_wp_load', array( __CLASS__, 'register_commands' ) );
	}

	/**
	 * Register commands.
	 *
	 * @since 1.0.0
	 */
	public static function register_commands() {
		\WP_CLI::add_command( 'wc_variation_images hello_world', __CLASS__ . '::hello_world' );
	}

	/**
	 * Run command.
	 *
	 * @since 1.0.0
	 */
	public static function hello_world() {
		\WP_CLI::line( 'Hello World!' );
	}
}
