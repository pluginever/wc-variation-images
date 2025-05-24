<?php

namespace WooCommerceVariationImages\ByteKit\Interfaces;

defined( 'ABSPATH' ) || exit();

/**
 * Describes a class that can be used as a plugin main class.
 *
 * @since 1.0.0
 * @version 1.0.2
 * @author  Sultan Nasir Uddin <sultan@byteever.com>
 * @package WooCommerceVariationImages\ByteKit\Plugin
 * @license GPL-3.0+
 */
interface Pluginable {

	/**
	 * Gets the plugin name.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_name();

	/**
	 * Gets the plugin file.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_file();

	/**
	 * Gets the plugin version.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_version();

	/**
	 * Get the plugin prefix.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_prefix();

	/**
	 * Get the 'basename' for the plugin (e.g. my-plugin/my-plugin.php).
	 *
	 * @since  1.0.0
	 * @return string The plugin basename.
	 */
	public function get_basename();

	/**
	 * Gets the plugin slug.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_slug();

	/**
	 * Gets the plugin language directory.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_lang_path();

	/**
	 *
	 * Get the plugin dir path.
	 *
	 * @param string $path Optional. Path relative to the plugin dir path.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_dir_path( $path = '' );

	/**
	 * Get the plugin dir url.
	 *
	 * @param string $path Optional. Path relative to the plugin dir url.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_dir_url( $path = '' );

	/**
	 * Get template path.
	 *
	 * @param string $file Optional. File name.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_template_path( $file = '' );

	/**
	 * Get assets path.
	 *
	 * @param string $file Optional. File name.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_assets_path( $file = '' );

	/**
	 * Get assets url.
	 *
	 * @param string $file Optional. File name.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_assets_url( $file = '' );

	/**
	 * Get meta links.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_plugin_meta_links();

	/**
	 * Get action links.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_plugin_action_links();

	/**
	 * Get Settings URL.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_settings_url();

	/**
	 * Get the plugin URI.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_plugin_uri();

	/**
	 * Get the author URI.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_author_uri();

	/**
	 * Get the support URI for this plugin.
	 *
	 * @since  1.0.0
	 * @return string (URI)
	 */
	public function get_support_url();

	/**
	 * Get the documentation URI for this plugin.
	 *
	 * @since  1.0.0
	 * @return string (URI)
	 */
	public function get_docs_url();

	/**
	 * Get the review URI for this plugin.
	 *
	 * @since  1.0.0
	 * @return string (URI)
	 */
	public function get_review_url();

	/**
	 * Get plugin database version.
	 *
	 * @since 1.0.0
	 * @return string (version)
	 */
	public function get_db_version();

	/**
	 * Add plugin database version.
	 *
	 * @param string $version Version.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function add_db_version( $version = null );

	/**
	 * Update plugin database version.
	 *
	 * @param string $version Version.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function update_db_version( $version = null );
}
