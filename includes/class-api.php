<?php

namespace WC_Variation_Images;

defined( 'ABSPATH' ) || exit;

/**
 * Class API
 *
 * @package WC_Variation_Images
 */
class API extends Controller {
	/**
	 * Set up the controller.
	 *
	 * Load files or register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function init() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route(
			'wc-variation-images/v1',
			'/hello-world',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'hello_world' ],
				'permission_callback' => [ $this, 'permission_callback' ],
			]
		);
	}

	/**
	 * Permission callback.
	 *
	 * @return bool
	 */
	public function permission_callback() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Hello world.
	 *
	 * @return \WP_REST_Response
	 */
	public function hello_world() {
		return rest_ensure_response( 'Hello world!' );
	}

}
