<?php

/**
 * Manage plugins for a WordPress site
 */

class WP_REST_Plugins_Controller extends WP_REST_Controller {

	public function __construct() {
		$this->namespace = 'wp/v2';
		$this->rest_base = 'plugins';
	}

	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'            => $this->get_collection_params(),
			),
			'schema' => array( $this, 'get_item_schema' ),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<slug>[\d]+)', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
			),
			array(
				'methods'  => WP_REST_Server::DELETABLE,
				'callback' => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'delete_item_permissions_check' ),
			),
		) );
	}

	public function get_items_permissions_check( $request ) {
        return true;
	}

	public function get_items( $request ) {

	}

	public function get_item_permissions_check( $request ) {
        return true;
	}

	public function get_item( $request ) {
        $slug = $request['slug'];
        $plugin = array();
        
//        try {
//            $plugin = get_plugin_data( $slug );
//        }catch( Exception $e ) {
//            $slug = null;
//        }

        if ( empty( $slug ) ) {
            return new WP_Error( 'rest_post_invalid_id', __( 'Invalid post id.' ), array( 'status' => 404 ) );
        }

        $data = $this->prepare_item_for_response( $plugin, $request );
        $response = rest_ensure_response( $data );

        return $response;
	}

	public function delete_item_permission_check( $request ) {

	}

	public function delete_item( $request ) {

	}

	public function get_item_schema() {

	}

	public function get_collection_params() {
		return array();
	}


    public function prepare_item_for_response( $plugin, $request ) {
        return array('plugin' => 'get plugin data');
    }
}
