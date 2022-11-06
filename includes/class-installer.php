<?php

namespace WC_Variation_Images;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Class Installer.
 *
 * @since 1.0.0
 * @package WC_Variation_Images
 */
class Installer extends Controller {

	/**
	 * Update callbacks.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $updates = array(
		'1.0.0' => 'update_100',
	);

	/**
	 * Set up the controller.
	 *
	 * Load files or register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function init() {
		add_action( 'wc_variation_images_activated', array( $this, 'install' ) );
		add_action( 'init', array( $this, 'check_version' ), 5 );
	}

	/**
	 * Install the plugin.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function install() {
		global $wpdb;
		$wpdb->hide_errors();
		$db_version = $this->get_plugin()->get_db_version();
		$collate    = $wpdb->has_cap( 'collation' ) ? $wpdb->get_charset_collate() : '';
		if ( ! is_blog_installed() ) {
			return;
		}

		add_option( $this->get_plugin()->get_db_version_name(), $this->get_plugin()->get_version() );
		add_option( $this->get_plugin()->get_activation_date_name(), current_time( 'mysql' ) );

		if ( ! $db_version ) {
			/**
			 * Fires after the plugin is installed for the first time.
			 *
			 * @since 1.0.0
			 */
			do_action( $this->get_plugin()->get_id() . '_newly_installed' );
			set_transient( $this->get_plugin()->get_id() . '_activation_redirect', 1, 30 );
		}
	}

	/**
	 * Check plugin version and run the updater if necessary.
	 *
	 * This check is done on all requests and runs if the versions do not match.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function check_version() {
		$db_version      = $this->get_plugin()->get_db_version();
		$current_version = $this->get_plugin()->get_version();
		$requires_update = version_compare( $db_version, $current_version, '<' );

		if ( ! defined( 'IFRAME_REQUEST' ) && $requires_update ) {
			$this->install();

			$update_versions = array_keys( $this->updates );
			usort( $update_versions, 'version_compare' );
			$needs_update = ! is_null( $db_version ) && version_compare( $db_version, end( $update_versions ), '<' );
			if ( $needs_update ) {
				$this->update();
				/**
				 * Fires after the plugin is updated.
				 *
				 * @since 1.0.0
				 */
				do_action( $this->get_plugin()->get_id() . '_updated' );
			} else {
				$this->get_plugin()->update_db_version();
			}
		}
	}

	/**
	 * Update the plugin.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function update() {
		$db_version      = $this->get_plugin()->get_db_version();
		$current_version = $this->get_plugin()->get_version();
		foreach ( $this->updates as $version => $callbacks ) {
			$callbacks = (array) $callbacks;
			if ( version_compare( $db_version, $version, '<' ) ) {
				foreach ( $callbacks as $callback ) {
					$this->get_plugin()->log( sprintf( 'Updating to %s from %s', $version, $db_version ) );
					// if the callback return false then we need to update the db version.
					$continue = call_user_func( array( $this, $callback ) );
					if ( ! $continue ) {
						$this->get_plugin()->update_db_version( $version );
						$notice = sprintf(
						/* translators: 1: plugin name 2: version number */
							__( '%1$s updated to version %2$s successfully.', 'wc-variation-images' ),
							'<strong>' . $this->get_plugin()->get_name() . '</strong>',
							'<strong>' . $version . '</strong>'
						);
						$this->add_notice( $notice );
					}
				}
			}
		}
	}

	/**
	 * Update to version 1.0.0.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function update_100() {
		$this->get_plugin()->log( 'Updating to version 1.0.0' );
	}
}
