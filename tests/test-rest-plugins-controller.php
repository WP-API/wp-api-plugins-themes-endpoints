<?php

class WP_Test_REST_Plugins_Controller extends WP_Test_REST_Controller_TestCase {
	protected $admin_id;

	public function setUp() {
		parent::setUp();

		$this->admin_id = $this->factory->user->create( array(
			'role' => 'administrator',
		) );
	}

	public function test_register_routes() {
		$routes = $this->server->get_routes();
		$this->assertArrayHasKey( '/wp/v2/plugins', $routes );
		$this->assertArrayHasKey( '/wp/v2/plugins/(?P<slug>[\w-]+)', $routes );
	}

	public function test_delete_item_without_permission() {

		wp_set_current_user( 0 );

		$request = new WP_REST_Request( WP_REST_Server::DELETABLE, '/wp/v2/plugins/hello-dolly' );

		$response = $this->server->dispatch( $request );

		$this->assertErrorResponse( 'rest_forbidden', $response, 401 );

	}

	public function test_context_param() {

	}

	public function test_get_items() {
		wp_set_current_user( $this->admin_id );
		$request = new WP_REST_Request( 'GET', '/wp/v2/plugins' );
		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertEquals( 2, count( $data ) );

		$this->check_get_plugins_response( $response, 'view' );
	}

	public function test_get_item() {

		wp_set_current_user( $this->admin_id );

		$request = new WP_REST_Request( 'GET', '/wp/v2/plugins/hello-dolly' );
		$response = $this->server->dispatch( $request );

		$this->check_get_plugin_response( $response, 'view' );
	}

	public function test_create_item() {

	}

	public function test_update_item() {

	}

	public function test_delete_item() {

	}

	public function test_prepare_item() {

	}

	public function test_get_item_schema() {
		$request = new WP_REST_Request( 'OPTIONS', '/wp/v2/plugins' );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();
		$properties = $data['schema']['properties'];
		$this->assertEquals( 11, count( $properties ) );
		$this->assertArrayHasKey( 'name', $properties );
		$this->assertArrayHasKey( 'plugin_uri', $properties );
		$this->assertArrayHasKey( 'version', $properties );
		$this->assertArrayHasKey( 'description', $properties );
		$this->assertArrayHasKey( 'author', $properties );
		$this->assertArrayHasKey( 'author_uri', $properties );
		$this->assertArrayHasKey( 'text_domain', $properties );
		$this->assertArrayHasKey( 'domain_path', $properties );
		$this->assertArrayHasKey( 'network', $properties );
		$this->assertArrayHasKey( 'title', $properties );
		$this->assertArrayHasKey( 'author_name', $properties );
	}

	public function test_get_items_without_permissions() {
		wp_set_current_user( 0 );

		$request = new WP_REST_Request( 'GET', '/wp/v2/plugins' );

		$response = $this->server->dispatch( $request );

		$this->assertErrorResponse( 'rest_forbidden', $response, 401 );
	}

	/**
	 * Check response of collections.
	 */
	protected function check_get_plugins_response( $response, $context = 'view' ) {
		$this->assertNotInstanceOf( 'WP_Error', $response );
		$response = rest_ensure_response( $response );
		$this->assertEquals( 200, $response->get_status() );
		$plugin_data = $response->get_data();

		// For collections loop through each item.
		foreach ( $plugin_data as $plugin ) {
			$this->check_plugin_data( $plugin, $context, $response->get_links() );
		}
	}

	/**
	 * Check response of single resources.
	 */
	protected function check_get_plugin_response( $response, $context = 'view' ) {
		$this->assertNotInstanceOf( 'WP_Error', $response );
		$response = rest_ensure_response( $response );
		$this->assertEquals( 200, $response->get_status() );
		$plugin_data = $response->get_data();

		// For single responses.
		$this->check_plugin_data( $plugin_data, $context, $response->get_links() );
	}

	protected function check_plugin_data( $data, $context, $links ) {
		// @TODO Eventually replace with plugin collection.
		$plugins = get_plugins();
		$plugin = null;
		// Match plugin to one from collection and verify data.
		foreach ( $plugins as $path => $plugin_data ) {
			if ( $data['name'] === $plugin_data['Name'] ) {
				$plugin         = $plugin_data;
				$plugin['path'] = $path;
				break;
			}
		}

		// Make sure plugin exists.
		$this->assertTrue( ! is_null( $plugin ) );

		$this->assertEquals( $plugin['Name'], $data['name'] );
		$this->assertEquals( $plugin['PluginURI'], $data['plugin_uri'] );
		$this->assertEquals( $plugin['Version'], $data['version'] );
		// Very strange things happen with plugin description values.
		$this->assertEquals( strip_tags( $plugin['Description'] ), strip_tags( $data['description'] ) );
		$this->assertEquals( $plugin['Author'], $data['author'] );
		$this->assertEquals( $plugin['AuthorURI'], $data['author_uri'] );
		$this->assertEquals( $plugin['TextDomain'], $data['text_domain'] );
		$this->assertEquals( $plugin['DomainPath'], $data['domain_path'] );
		$this->assertEquals( $plugin['Title'], $data['title'] );
		$this->assertEquals( $plugin['AuthorName'], $data['author_name'] );

		// @TODO Handle active, parent, update, autoupdate.
		// @TODO Handle context params.
	}
}
