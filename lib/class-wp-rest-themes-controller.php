<?php

/**
 * Manage themes for a WordPress site
 */
class WP_REST_Themes_Controller extends WP_REST_Controller {

	public function __construct() {
		$this->namespace = 'wp/v2';
		$this->rest_base = 'themes';
	}

	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => $this->get_collection_params(),
			),
			'schema' => array( $this, 'get_item_schema' ),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<slug>[\w-]+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'delete_item_permissions_check' ),
			),
		) );
	}

	/**
	 * Check if a given request has access to read /themes.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {

		if ( ! current_user_can( 'switch_themes' ) ) {
			return new WP_Error( 'rest_forbidden', __( 'Sorry, you cannot view the list of themes' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;

	}

	public function get_items( $request ) {

	}

	/**
	 * Check if a given request has access to read /theme/{theme-name}
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_item_permissions_check( $request ) {

		if ( ! current_user_can( 'switch_themes' ) ) {
			return new WP_Error( 'rest_forbidden', __( 'Sorry, you do not have access to this resource' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;

	}

	public function get_item( $request ) {

	}

	/**
	 * check if a request can delete a theme
	 *
	 * @param WP_REST_Request $request
	 * @return WP_Error|boolean
	 */
	public function delete_item_permissions_check( $request ) {

		if ( ! current_user_can( 'delete_themes' ) ) {
			return new WP_Error( 'rest_forbidden', __( 'Sorry, you cannot delete themes' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;

	}

	public function delete_item( $request ) {

	}

	public function prepare_item_for_response( $item, $request ) {

	}

	public function get_item_schema() {

	}

	public function get_collection_params() {
		return array();
	}

}
