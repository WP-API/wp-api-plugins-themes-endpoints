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

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<slug>[\w-]+)', array(
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
			'schema' => array( $this, 'get_item_schema' ),
		) );
	}

	/**
	 * Check if a given request has access to read /plugins.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {

		return current_user_can( 'activate_plugins' );

	}

	public function get_items( $request ) {
		$data = array();

		require_once ABSPATH . '/wp-admin/includes/plugin.php';
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
		return true;
	}

	public function get_item( $request ) {
		$slug = $request['slug'];
		$plugin = null;

		require_once ABSPATH . '/wp-admin/includes/plugin.php';
		$plugins = get_plugins();
		foreach ( $plugins as $name => $active_plugin ) {
			if ( array_values( preg_split( '/\//', $name ) )[0] === $slug ) {
				$plugin = $active_plugin;
				break;
			}
		}

		if ( ! $plugin ) {
			return new WP_Error( 'rest_post_invalid_id', __( 'Invalid post id.' ), array( 'status' => 404 ) );
		}

		$data = $this->prepare_item_for_response( $plugin, $request );
		$response = rest_ensure_response( $data );

		return $response;
	}

	public function delete_item_permission_check( $request ) {

	}

	public function get_item_schema() {
		$schema = array(
			'$schema'              => 'http://json-schema.org/draft-04/schema#',
			'title'                => 'plugin',
			'type'                 => 'object',
			'properties'           => array(
				'name'        => array(
					'description'  => __( 'The title for the resource.' ),
					'type'         => 'string',
					),
				'plugin_uri'   => array(
					'description'  => __( 'The title for the resource.' ),
					'type'         => 'string',
					),
				'version'     => array(
					'description'  => __( 'The title for the resource.' ),
					'type'         => 'string',
					),
				'description'     => array(
					'description'  => __( 'The title for the resource.' ),
					'type'         => 'string',
					),
				'author'     => array(
					'description'  => __( 'The title for the resource.' ),
					'type'         => 'string',
					),
				'author_uri'     => array(
					'description'  => __( 'The title for the resource.' ),
					'type'         => 'string',
					),
				'text_domain'     => array(
					'description'  => __( 'The title for the resource.' ),
					'type'         => 'string',
					),
				'domain_path'     => array(
					'description'  => __( 'The title for the resource.' ),
					'type'         => 'string',
					),
				'network'     => array(
					'description'  => __( 'The title for the resource.' ),
					'type'         => 'string',
					),
				'title'     => array(
					'description'  => __( 'The title for the resource.' ),
					'type'         => 'string',
					),
				'author_name'     => array(
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


	public function prepare_item_for_response( $plugin, $request ) {
		$data = array(
			'name' => $plugin['Name'],
			'plugin_uri' => $plugin['PluginURI'],
			'version' => $plugin['Version'],
			'description' => $plugin['Description'],
			'author' => $plugin['Author'],
			'author_uri' => $plugin['AuthorURI'],
			'text_domain' => $plugin['TextDomain'],
			'domain_path' => $plugin['DomainPath'],
			'network' => $plugin['Network'],
			'title' => $plugin['Title'],
			'author_name' => $plugin['AuthorName']
		);

			return $data;
	}
}
