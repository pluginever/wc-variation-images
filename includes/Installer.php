<?php

namespace PluginEver\VariationImages;

use PluginEver\VariationImages\B8\Component;

defined( 'ABSPATH' ) || exit;

/**
 * Handles plugin installation.
 *
 * @since   1.0.0
 * @package PluginEver\VariationImages
 */
class Installer extends Component {

	/**
	 * Update hook name.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const UPDATE_HOOK = 'wc_variation_images_run_update';

	/**
	 * Upgrade routines keyed by the target version.
	 *
	 * @since 1.0.0
	 * @var array<string, callable>
	 */
	protected array $updates = array();

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register(): void {
		add_action( 'init', array( $this, 'maybe_update' ) );
		add_action( self::UPDATE_HOOK, array( $this, 'run_update' ) );
	}

	/**
	 * Run a pending upgrade.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function maybe_update(): void {
		if ( version_compare( $this->app->version, $this->app->options->get_db_version(), '>' ) ) {
			$this->install();
			$this->app->queue->add( self::UPDATE_HOOK );
		}
	}

	/**
	 * Run the pending upgrades.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function run_update(): void {
		$installed = $this->app->options->get_db_version();

		uksort( $this->updates, 'version_compare' );

		foreach ( $this->updates as $version => $callback ) {
			if ( version_compare( $installed, $version, '<' ) ) {
				call_user_func( $callback );
				$this->app->options->update_db_version( $version, true );
			}
		}

		$this->app->options->update_db_version( $this->app->version, true );
	}

	/**
	 * Install the plugin.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function install(): void {
		$this->create_tables();
		$this->create_defaults();

		$this->app->options->add( 'installed_on', gmdate( 'Y-m-d H:i:s' ) );
		$this->app->options->update_db_version( $this->app->version );

		flush_rewrite_rules();
	}

	/**
	 * Clean up the plugin's runtime state.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function deactivate(): void {
		$this->app->queue->clear();

		flush_rewrite_rules();
	}

	/**
	 * Create the database tables.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function create_tables(): void {
	}

	/**
	 * Seed the default options.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function create_defaults(): void {
	}
}
