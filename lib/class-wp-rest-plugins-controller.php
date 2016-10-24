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

		if ( ! current_user_can( 'manage_options' ) ) { // TODO: Something related to plugins. activate_plugin capability seems to not be available for multi-site superadmin (?)
			return new WP_Error( 'rest_forbidden', __( 'Sorry, you cannot view the list of plugins' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;

	}

	public function get_items( $request ) {

		$data = array();

		require_once ABSPATH . '/wp-admin/includes/plugin.php';
		$plugins = get_plugins();

		// Exit early if empty set.
		if ( empty( $plugins ) ) {
			return rest_ensure_response( $data );
		}

		// Store pagation values for headers.
		$total_plugins = count( $plugins );
		$per_page = (int) $request['per_page'];
		if ( ! empty( $request['offset'] ) ) {
			$offset = $request['offset'];
		} else {
			$offset = ( $request['page'] - 1 ) * $per_page;
		}
		$max_pages = ceil( $total_plugins / $per_page );
		$page = ceil( ( ( (int) $offset ) / $per_page ) + 1 );

		// Find count to display per page.
		if ( $page > 1 ) {
			$length = $total_plugins - $offset;
			if ( $length > $per_page ) {
				$length = $per_page;
			}
		} else {
			$length = $total_plugins > $per_page ? $per_page : $total_plugins;
		}

		// Split plugins array.
		$plugins = array_slice( $plugins, $offset, $length );

		foreach ( $plugins as $obj ) {
			$plugin = $this->prepare_item_for_response( $obj, $request );

			if ( is_wp_error( $plugin ) ) {
				continue;
			}

			$data[] = $this->prepare_response_for_collection( $plugin );
		}

		$response = rest_ensure_response( $data );

		// Add pagination headers to response.
		$response->header( 'X-WP-Total', (int) $total_plugins );
		$response->header( 'X-WP-TotalPages', (int) $max_pages );

		// Add pagination link headers to response.
		$base = add_query_arg( $request->get_query_params(), rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ) );
		if ( $page > 1 ) {
			$prev_page = $page - 1;
			if ( $prev_page > $max_pages ) {
				$prev_page = $max_pages;
			}
			$prev_link = add_query_arg( 'page', $prev_page, $base );
			$response->link_header( 'prev', $prev_link );
		}
		if ( $max_pages > $page ) {
			$next_page = $page + 1;
			$next_link = add_query_arg( 'page', $next_page, $base );
			$response->link_header( 'next', $next_link );
		}

		// Return requested collection.
		return $response;
	}

	/**
	 * check if a given request has access to read /plugins/{plugin-name}
	 *
	 * @param WP_REST_Request $request
	 * @return WP_Error|boolean
	 */
	public function get_item_permissions_check( $request ) {

		if ( ! current_user_can( 'manage_options' ) ) { // TODO: Something related to plugins. activate_plugin capability seems to not be available for multi-site superadmin (?)
			return new WP_Error( 'rest_forbidden', __( 'Sorry, you do not have access to this resource' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;

	}

	public function get_item( $request ) {
		$slug   = $request['slug'];
		$plugin = null;

		require_once ABSPATH . '/wp-admin/includes/plugin.php';
		$plugins = get_plugins();

		foreach ( $plugins as $active_plugin ) {
			$sanitized_title = sanitize_title( $active_plugin['Name'] );
			if ( $slug === $sanitized_title ) {
				$plugin = $active_plugin;
				break;
			}
		}

		if ( ! $plugin ) {
			return new WP_Error( 'rest_post_invalid_id', __( 'Invalid post id.' ), array( 'status' => 404 ) );
		}

		$data     = $this->prepare_item_for_response( $plugin, $request );
		$response = rest_ensure_response( $data );

		return $response;

	}

	/**
	 * check if a given request has access to delete a plugin
	 *
	 * @param WP_REST_Request $request
	 * @return WP_Error|boolean
	 */
	public function delete_item_permissions_check( $request ) {

		if ( ! current_user_can( 'delete_plugins' ) ) {
			return new WP_Error( 'rest_forbidden', __( 'Sorry, you cannot delete this plugin' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;

	}

	/**
	 * Delete a plugin.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_item( $request ) {

	}

	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'plugin',
			'type'       => 'object',
			'properties' => array(
				'name'        => array(
					'description' => __( 'The title for the resource.' ),
					'type'        => 'string',
				),
				'plugin_uri'  => array(
					'description' => __( 'The title for the resource.' ),
					'type'        => 'string',
				),
				'version'     => array(
					'description' => __( 'The title for the resource.' ),
					'type'        => 'string',
				),
				'description' => array(
					'description' => __( 'The title for the resource.' ),
					'type'        => 'string',
				),
				'author'      => array(
					'description' => __( 'The title for the resource.' ),
					'type'        => 'string',
				),
				'author_uri'  => array(
					'description' => __( 'The title for the resource.' ),
					'type'        => 'string',
				),
				'text_domain' => array(
					'description' => __( 'The title for the resource.' ),
					'type'        => 'string',
				),
				'domain_path' => array(
					'description' => __( 'The title for the resource.' ),
					'type'        => 'string',
				),
				'network'     => array(
					'description' => __( 'The title for the resource.' ),
					'type'        => 'string',
				),
				'title'       => array(
					'description' => __( 'The title for the resource.' ),
					'type'        => 'string',
				),
				'author_name' => array(
					'description' => __( 'The title for the resource.' ),
					'type'        => 'string',
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}

	public function get_collection_params() {
		$params = parent::get_collection_params();

		$params['offset'] = array(
			'description'        => __( 'Offset the result set by a specific number of items.' ),
			'type'               => 'integer',
			'sanitize_callback'  => 'absint',
			'validate_callback'  => 'rest_validate_request_arg',
		);

		return $params;
	}


	public function prepare_item_for_response( $plugin, $request ) {
		$data = array();

		$schema = $this->get_item_schema();

		if ( isset( $schema['properties']['name'] ) ) {
			$data['name'] = $plugin['Name'];
		}

		if ( isset( $schema['properties']['plugin_uri'] ) ) {
			$data['plugin_uri'] = $plugin['PluginURI'];
		}

		if ( isset( $schema['properties']['version'] ) ) {
			$data['version'] = $plugin['Version'];
		}

		if ( isset( $schema['properties']['description'] ) ) {
			$data['description'] = $plugin['Description'];
		}

		if ( isset( $schema['properties']['author'] ) ) {
			$data['author'] = $plugin['Author'];
		}

		if ( isset( $schema['properties']['author_uri'] ) ) {
			$data['author_uri'] = $plugin['AuthorURI'];
		}

		if ( isset( $schema['properties']['text_domain'] ) ) {
			$data['text_domain'] = $plugin['TextDomain'];
		}

		if ( isset( $schema['properties']['domain_path'] ) ) {
			$data['domain_path'] = $plugin['DomainPath'];
		}

		if ( isset( $schema['properties']['network'] ) ) {
			$data['network'] = $plugin['Network'];
		}

		if ( isset( $schema['properties']['title'] ) ) {
			$data['title'] = $plugin['Title'];
		}

		if ( isset( $schema['properties']['author_name'] ) ) {
			$data['author_name'] = $plugin['AuthorName'];
		}

		return $data;
	}
}
