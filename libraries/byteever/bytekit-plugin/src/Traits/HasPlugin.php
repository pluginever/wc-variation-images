<?php

namespace WooCommerceVariationImages\ByteKit\Traits;

defined( 'ABSPATH' ) || exit();

/**
 * Implements common methods for plugins.
 *
 * @since   1.0.0
 * @version 1.0.2
 * @author  Sultan Nasir Uddin <manikdrmc@gmail.com>
 * @package ByteKit/Plugin
 * @license GPL-3.0+
 */
trait HasPlugin {

	/**
	 * Get the value of a property.
	 *
	 * @param string $key The name of the property.
	 *
	 * @since 1.0.0
	 * @return mixed
	 */
	public function get( $key ) {
		if ( is_callable( array( $this, "get_{$key}" ) ) ) {
			return $this->{"get_{$key}"}();
		} elseif ( isset( $this->services[ $key ] ) ) {
			return $this->services[ $key ];
		} elseif ( isset( $this->data[ $key ] ) ) {
			return $this->data[ $key ];
		}

		return null;
	}

	/**
	 * Set the value of a property.
	 *
	 * @param string|array|object $key The name of the property.
	 * @param mixed               $value The value of the property.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function set( $key, $value = null ) {
		// Allow setting multiple properties at once.
		if ( is_array( $key ) ) {
			foreach ( $key as $k => $v ) {
				$this->set( $k, $v );
			}

			return;
		}

		$value = is_null( $value ) ? $key : $value;
		if ( is_callable( array( $this, $key ) ) ) {
			return $this->$key( $value );
		} elseif ( is_object( $value ) || ( is_string( $value ) && class_exists( $value ) ) ) {
			$this->services->add( $key, $value );
		} elseif ( is_string( $key ) && ! isset( $this->data[ $key ] ) ) {
			$this->data[ $key ] = $value;
		}
	}

	/**
	 * Gets the plugin name.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_name() {
		return $this->data['name'];
	}

	/**
	 * Gets the plugin file.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_file() {
		return $this->data['file'];
	}

	/**
	 * Gets the plugin version.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_version() {
		return $this->data['version'];
	}

	/**
	 * Get the plugin prefix.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_prefix() {
		return $this->data['prefix'];
	}

	/**
	 * Get the 'basename' for the plugin (e.g. my-plugin/my-plugin.php).
	 *
	 * @since  1.0.0
	 * @return string The plugin basename.
	 */
	public function get_basename() {
		return plugin_basename( $this->get_file() );
	}

	/**
	 * Gets the plugin slug.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_slug() {
		return dirname( $this->get_basename() );
	}

	/**
	 * Gets the plugin language directory.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_lang_path() {
		return $this->get_slug() . rtrim( $this->domain_path, '/' );
	}

	/**
	 *
	 * Get the plugin dir path.
	 *
	 * @param string $path Optional. Path relative to the plugin dir path.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_dir_path( $path = '' ) {
		$dir  = rtrim( str_replace( '\\', '/', plugin_dir_path( $this->get_file() ) ), '/' );
		$path = ltrim( $path, '/' );
		$full = wp_normalize_path( $dir . '/' . $path );

		return is_dir( $full ) ? trailingslashit( $full ) : $full;
	}

	/**
	 * Get the plugin dir url.
	 *
	 * @param string $path Optional. Path relative to the plugin dir url.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_dir_url( $path = '' ) {
		$dir  = rtrim( str_replace( '\\', '/', plugin_dir_url( $this->get_file() ) ), '/' );
		$path = ltrim( $path, '/' );
		return wp_normalize_path( $dir . '/' . $path );
	}

	/**
	 * Get template path.
	 *
	 * @param string $file Optional. File name.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_template_path( $file = '' ) {
		return $this->get_dir_path( 'templates/' . $file );
	}

	/**
	 * Get assets path.
	 *
	 * @param string $file Optional. File name.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_assets_path( $file = '' ) {
		return $this->get_dir_path( 'build/' . $file );
	}

	/**
	 * Get assets url.
	 *
	 * @param string $file Optional. File name.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_assets_url( $file = '' ) {
		return $this->get_dir_url( 'build/' . $file );
	}

	/**
	 * Get meta links.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_plugin_meta_links() {
		$links = array();
		if ( ! empty( $this->get_docs_url() ) ) {
			$links['docs'] = array(
				'label' => __( 'Documentation', 'wc-variation-images' ),
				'url'   => $this->get_docs_url(),
			);
		}

		if ( ! empty( $this->get_support_url() ) ) {
			$links['support'] = array(
				'label' => __( 'Support', 'wc-variation-images' ),
				'url'   => $this->get_support_url(),
			);
		}

		if ( ! empty( $this->get_review_url() ) ) {
			$links['review'] = array(
				'label' => __( 'Review', 'wc-variation-images' ),
				'url'   => $this->get_review_url(),
			);
		}

		/**
		 * Filter the plugin meta links.
		 *
		 * @param array $links The plugin meta links.
		 *
		 * @since 1.0.0
		 */
		return apply_filters( $this->get_prefix() . '_plugin_meta_links', $links );
	}

	/**
	 * Get action links.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_plugin_action_links() {
		$links = array();
		if ( ! empty( $this->get_settings_url() ) ) {
			$links['settings'] = array(
				'label' => __( 'Settings', 'wc-variation-images' ),
				'url'   => $this->get_settings_url(),
			);
		}

		/**
		 * Filter the plugin action links.
		 *
		 * @param array $links The plugin action links.
		 *
		 * @since 1.0.0
		 */
		return apply_filters( $this->get_prefix() . '_plugin_action_links', $links );
	}

	/**
	 * Get Settings URL.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_settings_url() {
		$settings_url = isset( $this->data['settings_url'] ) ? $this->data['settings_url'] : '';
		// If relative URL, make it absolute.
		if ( ! empty( $settings_url ) && false === strpos( $settings_url, 'http' ) ) {
			$settings_url = admin_url( $settings_url );
		}

		return $settings_url;
	}

	/**
	 * Get the plugin URI.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_plugin_uri() {
		return $this->data['plugin_uri'];
	}

	/**
	 * Get the author URI.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_author_uri() {
		return $this->data['author_uri'];
	}

	/**
	 * Get the support URI for this plugin.
	 *
	 * @since  1.0.0
	 * @return string (URI)
	 */
	public function get_support_url() {
		return isset( $this->data['support_url'] ) ? $this->data['support_url'] : '';
	}

	/**
	 * Get the documentation URI for this plugin.
	 *
	 * @since  1.0.0
	 * @return string (URI)
	 */
	public function get_docs_url() {
		return isset( $this->data['docs_url'] ) ? $this->data['docs_url'] : '';
	}

	/**
	 * Get the review URI for this plugin.
	 *
	 * @since  1.0.0
	 * @return string (URI)
	 */
	public function get_review_url() {
		return isset( $this->data['review_url'] ) ? $this->data['review_url'] : '';
	}

	/**
	 * Get plugin database version.
	 *
	 * @since 1.0.0
	 * @return string (version)
	 */
	public function get_db_version() {
		return get_option( $this->get_prefix() . '_version', '1.0.0' );
	}

	/**
	 * Add plugin database version.
	 *
	 * @param string $version Version.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function add_db_version( $version = null ) {
		if ( empty( $version ) ) {
			$version = $this->get_version();
		}

		add_option( $this->get_prefix() . '_version', $version );
	}

	/**
	 * Update plugin database version.
	 *
	 * @param string $version Version.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function update_db_version( $version = null ) {
		if ( empty( $version ) ) {
			$version = $this->get_version();
		}

		update_option( $this->get_prefix() . '_version', $version );
	}
}
