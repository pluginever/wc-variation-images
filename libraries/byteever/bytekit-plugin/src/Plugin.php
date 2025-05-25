<?php

namespace WooCommerceVariationImages\ByteKit;

use WooCommerceVariationImages\ByteKit\Admin\Flash;
use WooCommerceVariationImages\ByteKit\Admin\Notices;
use WooCommerceVariationImages\ByteKit\Interfaces\Pluginable;
use WooCommerceVariationImages\ByteKit\Traits\HasPlugin;

defined( 'ABSPATH' ) || exit();

/**
 * Template for encapsulating some of the most often required abilities of a plugin instance.
 *
 * @since   1.0.0
 * @version 1.0.2
 * @author  Sultan Nasir Uddin <manikdrmc@gmail.com>
 * @package ByteKit/Plugin
 * @license GPL-3.0+
 *
 * @property Flash   $flash The flash message handler.
 * @property Notices $notices The notices' handler.
 * @property Scripts $scripts The scripts' handler.
 *
 * @property-read string $name The plugin name.
 * @property-read string $plugin_uri The plugin URI.
 * @property-read string $version The plugin version.
 * @property-read string $description The plugin description.
 * @property-read string $author The plugin author.
 * @property-read string $author_uri The plugin author URI.
 * @property-read string $text_domain The plugin text domain.
 * @property-read string $domain_path The plugin domain path.
 * @property-read boolean $network The plugin network.
 * @property-read string $requires_wp The plugin requires at least.
 * @property-read string $requires_php The plugin requires PHP.
 * @property-read string $requires_plugins The plugin requires plugins.
 * @property-read string $support_url The plugin support URL.
 * @property-read string $docs_url The plugin docs URL.
 * @property-read string $review_url The plugin review URL.
 * @property-read string $settings_url The plugin settings URL.
 * @property-read string $file The plugin file.
 * @property-read string $slug The plugin slug.
 */
abstract class Plugin implements Pluginable {
	use HasPlugin;

	/**
	 * The plugin data store.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $data = array();

	/**
	 * The plugin services.
	 *
	 * @since 1.0.0
	 * @var Services
	 */
	public $services;

	/**
	 * The single instance of the class.
	 *
	 * @since 1.0.0
	 * @var self
	 */
	protected static $instances = array();

	/**
	 * Creates a new instance of the class.
	 * This method is used to create a new instance of the class.
	 *
	 * @param string|array $data The plugin data.
	 *
	 * @throws \Exception If the plugin file is not provided.
	 * @since 1.0.0
	 * @return static
	 */
	final public static function create( $data = null ) {
		$plugin = get_called_class();
		if ( ! isset( static::$instances[ $plugin ] ) ) {
			if ( is_scalar( $data ) ) {
				$file         = $data;
				$data         = array();
				$data['file'] = $file;
			}
			require_once ABSPATH . 'wp-admin/includes/plugin.php';

			if ( empty( $data['file'] ) ) {
				throw new \Exception( 'Plugin file is required.' );
			}

			$file        = $data['file'];
			$plugin_data = wp_cache_get( $file, $file );
			if ( false === $plugin_data ) {
				$headers     = array(
					'name'             => 'Plugin Name',
					'plugin_uri'       => 'Plugin URI',
					'version'          => 'Version',
					'description'      => 'Description',
					'author'           => 'Author',
					'author_uri'       => 'Author URI',
					'text_domain'      => 'Text Domain',
					'domain_path'      => 'Domain Path',
					'network'          => 'Network',
					'requires_wp'      => 'Requires at least',
					'requires_php'     => 'Requires PHP',
					'requires_plugins' => 'Requires Plugins',
					'support_url'      => 'Support URL',
					'docs_url'         => 'Docs URL',
					'api_url'          => 'API URL',
					'review_url'       => 'Review URL',
					'settings_url'     => 'Settings URL',
					'item_id'          => 'Item ID',
				);
				$plugin_data = get_file_data( $data['file'], $headers, 'plugin' );
				$plugin_data = array_change_key_case( $plugin_data );
				// if prefix is not set, set it to the plugin slug.
				if ( ! isset( $plugin_data['prefix'] ) ) {
					$plugin_data['prefix'] = str_replace( '-', '_', dirname( plugin_basename( $file ) ) );
				}
				// if version is not set, set it to 1.0.0.
				if ( ! isset( $plugin_data['version'] ) ) {
					$plugin_data['version'] = '1.0.0';
				}
				// Cache the plugin data.
				wp_cache_set( $data['file'], $plugin_data, $file );
			}

			$plugin_data                  = array_merge( $plugin_data, $data );
			static::$instances[ $plugin ] = new $plugin( $plugin_data );
		}

		return static::$instances[ $plugin ];
	}

	/**
	 * Gets the instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return static
	 */
	final public static function instance() {
		$plugin = get_called_class();
		if ( ! isset( static::$instances[ $plugin ] ) ) {
			_doing_it_wrong( __FUNCTION__, 'Plugin instance called before initiating the instance.', '1.0.0' );
		}

		return static::$instances[ $plugin ];
	}

	/**
	 * Plugin constructor.
	 *
	 * @param array $data The plugin data.
	 *
	 * @since 1.0.0
	 */
	protected function __construct( $data ) {
		$this->data     = array_merge( $this->data, $data );
		$this->services = new Services();
		$this->services->add( 'flash', new Flash( $this ) );
		$this->services->add( 'notices', new Notices( $this ) );
		$this->services->add( 'scripts', new Scripts( $this ) );

		// Register hooks.
		add_action( 'init', array( $this, '_register_textdomain' ) );
		add_action( 'init', array( $this, '_register_scripts' ) );
		add_filter( 'plugin_row_meta', array( $this, '_plugin_row_meta' ), 10, 2 );
		add_filter( 'plugin_action_links_' . $this->get_basename(), array( $this, '_plugin_action_links' ) );
	}

	/**
	 * Magic method to get the value associated with the given key.
	 *
	 * @param string $key The key to retrieve the value for.
	 *
	 * @since 1.0.0
	 * @return mixed
	 */
	public function __get( $key ) {
		return $this->get( $key );
	}

	/**
	 * Magic method to set the value associated with the given key.
	 *
	 * @param string $key The key to set the value for.
	 * @param mixed  $value The value to set.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __set( $key, $value ) {
		$this->set( $key, $value );
	}

	/**
	 * Magic method to check if value exists for the specified key.
	 *
	 * @param string $key The key to retrieve the value for.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function __isset( $key ) {
		return ! is_null( $this->get( $key ) );
	}

	/**
	 * Register plugin textdomain.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function _register_textdomain() { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
		load_plugin_textdomain( $this->text_domain, false, $this->get_lang_path() );
	}

	/**
	 * Register plugin scripts.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function _register_scripts() { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
		// Register scripts.
		$this->scripts->register_style( 'bytekit-layout', plugin_dir_url( __FILE__ ) . 'assets/css/bytekit-layout.css' );
		$this->scripts->register_style( 'bytekit-components', plugin_dir_url( __FILE__ ) . 'assets/css/bytekit-components.css' );
		$this->scripts->register_script( 'bytekit-admin', plugin_dir_url( __FILE__ ) . 'assets/js/bytekit-admin.js', array( 'jquery' ) );
	}

	/**
	 * Add plugin meta links.
	 *
	 * @param array  $links Plugin meta links.
	 * @param string $file Plugin file.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function _plugin_row_meta( $links, $file ) { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
		if ( $file !== $this->get_basename() ) {
			return $links;
		}
		foreach ( $this->get_plugin_meta_links() as $key => $link ) {
			$links[ $key ] = sprintf( '<a href="%1$s" target="_blank">%2$s</a>', esc_url( $link['url'] ), esc_html( $link['label'] ) );
		}

		return $links;
	}

	/**
	 * Add plugin action links.
	 *
	 * @param array $links Plugin action links.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function _plugin_action_links( $links ) { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
		$actions = array();
		foreach ( $this->get_plugin_action_links() as $key => $link ) {
			$actions[ $key ] = sprintf( '<a href="%1$s">%2$s</a>', esc_url( $link['url'] ), wp_kses_post( $link['label'] ) );
		}

		// add the actions to beginning of the links.
		return array_merge( $actions, $links );
	}

	/*
	|--------------------------------------------------------------------------
	| Helper Methods
	|--------------------------------------------------------------------------
	| Helper methods to perform common tasks.
	|--------------------------------------------------------------------------
	*/
	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name  Constant name.
	 * @param string|bool $value Constant value.
	 * @since 1.0.6
	 * @return void
	 */
	protected function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Check if a plugin is installed.
	 *
	 * @param string $plugin The plugin slug or basename.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_plugin_installed( $plugin ) {
		// Check if the $plugin is a basename or a slug. If it's a slug, convert it to a basename.
		if ( ! str_contains( $plugin, '/' ) ) {
			$plugin = $plugin . '/' . $plugin . '.php';
		}

		$plugins = get_plugins();

		return array_key_exists( $plugin, $plugins );
	}

	/**
	 * Check if a plugin is active.
	 *
	 * @param string $plugin The plugin slug or basename.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_plugin_active( $plugin ) {
		// Check if the $plugin is a basename or a slug. If it's a slug, convert it to a basename.
		if ( false === strpos( $plugin, '/' ) ) {
			$plugin = $plugin . '/' . $plugin . '.php';
		}

		$active_plugins = (array) get_option( 'active_plugins', array() );
		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}

		return in_array( $plugin, $active_plugins, true ) || array_key_exists( $plugin, $active_plugins );
	}

	/**
	 * Get plugin install url.
	 *
	 * @param string $plugin The plugin slug or basename.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_installation_url( $plugin ) {
		if ( false !== strpos( $plugin, '/' ) ) {
			// get only first part of the plugin name.
			$plugin = explode( '/', $plugin );
			$plugin = $plugin[0];
		}

		return wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . $plugin ), 'install-plugin_' . $plugin );
	}

	/**
	 * Get plugin activate url.
	 *
	 * @param string $plugin The plugin slug or basename.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_activation_url( $plugin ) {
		if ( false === strpos( $plugin, '/' ) ) {
			$plugin = $plugin . '/' . $plugin . '.php';
		}
		$url = wp_nonce_url( self_admin_url( 'plugins.php?action=activate&plugin=' . $plugin ), 'activate-plugin_' . $plugin );

		return $url;
	}

	/**
	 * Log an error.
	 *
	 * Description of levels:
	 * 'emergency': System is unusable.
	 * 'alert': Action must be taken immediately.
	 * 'critical': Critical conditions.
	 * 'error': Error conditions.
	 * 'warning': Warning conditions.
	 * 'notice': Normal but significant condition.
	 * 'info': Informational messages.
	 * 'debug': Debug-level messages.
	 *
	 * @param mixed  $message The error message.
	 * @param string $level The error level.
	 * @param array  $data Optional. Data to log.
	 *
	 * @return void
	 */
	public function log( $message, $level = 'debug', $data = array() ) {

		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return;
		}

		if ( is_object( $message ) || is_array( $message ) ) {
			$message = print_r( $message, true ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		} elseif ( is_bool( $message ) ) {
			$message = $message ? 'true' : 'false';
		} elseif ( is_null( $message ) ) {
			$message = 'null';
		} else {
			$message = (string) $message;
		}

		$line = sprintf(
			'[%s] %s',
			strtoupper( $level ),
			$message
		);

		if ( ! empty( $data ) ) {
			$line .= ' ' . wp_json_encode( $data );
		}

		error_log( $line ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}
}
