<?php

namespace WC_Variation_Images\API;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Class Entities_Controller.
 *
 * Handles REST API requests.
 *
 * @since 1.0.0
 * @package WC_Variation_Images
 */
class Entities_Controller  extends \WP_REST_Controller {
	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc/v1';
	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'entities';
	/**
	 * Register the routes for the objects of the controller.
	 *
	 * @since 1.0.0
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::CREATABLE ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => array(
						'context' => $this->get_context_param( array( 'default' => 'view' ) ),
					),
				),
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::EDITABLE ),
				),
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'delete_item_permissions_check' ),
					'args'                => array(
						'force' => array(
							'default'     => false,
							'description' => __( 'Whether to bypass trash and force deletion.', 'wc-variation-images' ),
						),
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Get a collection of items.
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request Full data about the request.
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_items( $request ) {
		$items = array();
		foreach ( $this->get_items_permissions_check( $request ) as $item ) {
			$items[] = $this->prepare_item_for_response( $item, $request );
		}
		$response = rest_ensure_response( $items );
		$response->header( 'X-WP-Total', count( $items ) );
		$response->header( 'X-WP-TotalPages', 1 );
		return $response;
	}

	/**
	 * Get one item from the collection.
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request Full data about the request.
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_item( $request ) {
		$item = $this->get_item_permissions_check( $request );
		if ( is_wp_error( $item ) ) {
			return $item;
		}
		$response = rest_ensure_response( $this->prepare_item_for_response( $item, $request ) );
		return $response;
	}

	/**
	 * Create one item from the collection.
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request Full data about the request.
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function create_item( $request ) {
		$item = $this->create_item_permissions_check( $request );
		if ( is_wp_error( $item ) ) {
			return $item;
		}
		$item = $this->prepare_item_for_database( $request );
		if ( is_wp_error( $item ) ) {
			return $item;
		}
		$id = $this->insert_item( $item );
		if ( is_wp_error( $id ) ) {
			return $id;
		}
		$item = $this->get_item(
			array(
				'id' => $id,
			)
		);
		if ( is_wp_error( $item ) ) {
			return $item;
		}
		$response = rest_ensure_response( $this->prepare_item_for_response( $item, $request ) );
		$response->set_status( 201 );
		$response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $id ) ) );
		return $response;
	}

	/**
	 * Update one item from the collection.
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request Full data about the request.
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function update_item( $request ) {
		$item = $this->update_item_permissions_check( $request );
		if ( is_wp_error( $item ) ) {
			return $item;
		}
		$item = $this->prepare_item_for_database( $request );
		if ( is_wp_error( $item ) ) {
			return $item;
		}
		$id = $this->update_item( $item );
		if ( is_wp_error( $id ) ) {
			return $id;
		}
		$item = $this->get_item(
			array(
				'id' => $id,
			)
		);
		if ( is_wp_error( $item ) ) {
			return $item;
		}
		$response = rest_ensure_response( $this->prepare_item_for_response( $item, $request ) );
		return $response;
	}

	/**
	 * Delete one item from the collection.
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request Full data about the request.
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function delete_item( $request ) {
		$item = $this->delete_item_permissions_check( $request );
		if ( is_wp_error( $item ) ) {
			return $item;
		}
		$id = $this->delete_item( $request['id'] );
		if ( is_wp_error( $id ) ) {
			return $id;
		}
		$response = new \WP_REST_Response();
		$response->set_status( 204 );
		return $response;
	}

	/**
	 * Check if a given request has access to get items.
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request Full data about the request.
	 * @return \WP_Error|bool
	 */
	public function get_items_permissions_check( $request ) {
		return true;
	}

	/**
	 * Check if a given request has access to get a specific item.
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request Full data about the request.
	 * @return \WP_Error|bool
	 */
	public function get_item_permissions_check( $request ) {
		return true;
	}

	/**
	 * Check if a given request has access to create items.
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request Full data about the request.
	 * @return \WP_Error|bool
	 */
	public function create_item_permissions_check( $request ) {
		return true;
	}

	/**
	 * Check if a given request has access to update a specific item.
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request Full data about the request.
	 * @return \WP_Error|bool
	 */
	public function update_item_permissions_check( $request ) {
		return true;
	}

	/**
	 * Check if a given request has access to delete a specific item.
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request Full data about the request.
	 * @return \WP_Error|bool
	 */
	public function delete_item_permissions_check( $request ) {
		return true;
	}

	/**
	 * Prepare the item for the REST response.
	 *
	 * @since 1.0.0
	 * @param mixed            $item    WordPress representation of the item.
	 * @param \WP_REST_Request $request Request object.
	 * @return mixed
	 */
	public function prepare_item_for_response( $item, $request ) {
		return $item;
	}

	/**
	 * Prepare the item for create or update operation.
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request Request object.
	 * @return mixed
	 */
	public function prepare_item_for_database( $request ) {
		return $request->get_params();
	}

	/**
	 * Get the query params for collections of attachments.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_collection_params() {
		return array(
			'context' => $this->get_context_param(
				array(
					'default' => 'view',
				)
			),
		);
	}

	/**
	 * Get the query params for collections of attachments.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_item_params() {
		return array(
			'context' => $this->get_context_param(
				array(
					'default' => 'view',
				)
			),
		);
	}

	/**
	 * Get the schema for a collection of attachments.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_public_item_schema() {
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'attachment',
			'type'       => 'object',
			'properties' => array(
				'id'                => array(
					'description' => __( 'Unique identifier for the object.', 'wc-variation-images' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'date'              => array(
					'description' => __( 'Date the object was published.', 'wc-variation-images' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'modified'          => array(
					'description' => __( 'Date the object was last modified.', 'wc-variation-images' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'title'             => array(
					'description' => __( 'Title of the object.', 'wc-variation-images' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'caption'           => array(
					'description' => __( 'Caption of the object.', 'wc-variation-images' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'arg_options' => array(
						'sanitize_callback' => 'wp_filter_post_kses',
					),
				),
				'description'       => array(
					'description' => __( 'Description of the object.', 'wc-variation-images' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'arg_options' => array(
						'sanitize_callback' => 'wp_filter_post_kses',
					),
				),
				'alt_text'          => array(
					'description' => __( 'Alternative text for the image.', 'wc-variation-images' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'author'            => array(
					'description' => __( 'Author of the object.', 'wc-variation-images' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'author_name'       => array(
					'description' => __( 'Display name of the author.', 'wc-variation-images' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'author_first_name' => array(
					'description' => __( 'First name of the author.', 'wc-variation-images' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
			),
		);
	}
}
