<?php

namespace PluginEver\VariationImages\Admin;

use PluginEver\VariationImages\B8\Component;

defined( 'ABSPATH' ) || exit;

/**
 * Handles the admin menu.
 *
 * @since   1.0.0
 * @package PluginEver\VariationImages\Admin
 */
class Menu extends Component {

	/**
	 * Parent menu slug.
	 *
	 * Defaults to the WooCommerce menu; set to a dedicated slug to register a
	 * top-level menu instead.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected string $parent = 'woocommerce';

	/**
	 * Registered screen IDs.
	 *
	 * @since 1.0.0
	 * @var array<int, string>
	 */
	protected array $screen_ids = array();

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
	}

	/**
	 * Register the admin menu.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_menu(): void {
		/**
		 * Filters the admin pages configuration.
		 *
		 * @since 1.0.0
		 * @param array<int, array<string, mixed>> $pages Admin page configurations.
		 */
		$pages = $this->app->apply_filters( 'admin_pages', array() );

		usort( $pages, static fn( $a, $b ) => ( $a['position'] ?? 10 ) <=> ( $b['position'] ?? 10 ) );

		foreach ( $pages as $page ) {
			$page = wp_parse_args(
				$page,
				array(
					'title'      => '',
					'slug'       => '',
					'path'       => '',
					'capability' => 'manage_woocommerce',
					'position'   => 10,
					'handle'     => null,
					'callback'   => array( $this, 'render' ),
				)
			);

			if ( ! empty( $page['path'] ) ) {
				$page['slug'] = $this->parent . '#' . ltrim( $page['path'], '/' );
			}

			if ( ! $page['title'] || ! $page['slug'] ) {
				continue;
			}

			$hook = add_submenu_page(
				$this->parent,
				$page['title'],
				$page['title'],
				$page['capability'],
				$page['slug'],
				$this->app->callback( $page['callback'] ),
				$page['position']
			);

			if ( ! $hook ) {
				continue;
			}

			$this->screen_ids[] = $hook;

			if ( is_callable( $page['handle'] ) ) {
				add_action( "load-{$hook}", $this->app->callback( $page['handle'] ) );
			}
		}
	}

	/**
	 * Render the mount node.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render(): void {
		echo '<div id="app"></div>';
	}
}
