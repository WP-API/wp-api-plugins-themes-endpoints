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

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
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
		$data = array();

		foreach ( get_plugins() as $obj ) {
			$plugin = $this->prepare_item_for_response( $obj, $request );
			if ( is_wp_error( $plugin ) ) {
				continue;
			}

			$data[ $obj['Name'] ] = $this->prepare_response_for_collection( $plugin );
		}

		return rest_ensure_response( $data );
	}

	public function get_item_permissions_check( $request ) {

	}

	public function get_item( $request ) {

	}

	public function delete_item_permission_check( $request ) {

	}

	public function delete_item( $request ) {

	}

	public function prepare_item_for_response( $item, $request ) {
		return $item;
	}

	public function get_item_schema() {
		$schema = array(
			'$schema'              => 'http://json-schema.org/draft-04/schema#',
			'title'                => 'plugin',
			'type'                 => 'object',
			'properties'           => array(
				'Name'        => array(
					'description'  => __( 'The title for the resource.' ),
					'type'         => 'string',
					),
				'PluginURI'   => array(
					'description'  => __( 'The title for the resource.' ),
					'type'         => 'string',
					),
				'Version'     => array(
					'description'  => __( 'The title for the resource.' ),
					'type'         => 'string',
					),
				'Description'     => array(
					'description'  => __( 'The title for the resource.' ),
					'type'         => 'string',
					),
				'Author'     => array(
					'description'  => __( 'The title for the resource.' ),
					'type'         => 'string',
					),
				'AuthorURI'     => array(
					'description'  => __( 'The title for the resource.' ),
					'type'         => 'string',
					),
				'TextDomain'     => array(
					'description'  => __( 'The title for the resource.' ),
					'type'         => 'string',
					),
				'DomainPath'     => array(
					'description'  => __( 'The title for the resource.' ),
					'type'         => 'string',
					),
				'Network'     => array(
					'description'  => __( 'The title for the resource.' ),
					'type'         => 'string',
					),
				'Title'     => array(
					'description'  => __( 'The title for the resource.' ),
					'type'         => 'string',
					),
				'AuthorName'     => array(
					'description'  => __( 'The title for the resource.' ),
					'type'         => 'string',
					),
				),
			);

		return $this->add_additional_fields_schema( $schema );
	}

	public function get_collection_params() {
		return array();
	}

}
