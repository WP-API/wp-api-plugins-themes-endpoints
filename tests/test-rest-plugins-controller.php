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
		// TODO: Check values.
		$this->assertEquals( 'Akismet', $data[0]['name'] );
	}

	/**
	 * Test pagination args and headers.
	 */
	public function test_get_items_pagination_headers() {
		// Skipped until more plugins are added into wordpress-develop repo.

		wp_set_current_user( $this->admin_id );
		// One plugin installed by default.
		$request  = new WP_REST_Request( 'GET', '/wp/v2/plugins' );
		$request->set_param( 'per_page', 1 );
		$response = $this->server->dispatch( $request );
		$headers  = $response->get_headers();
		$this->assertEquals( 2, $headers['X-WP-Total'] );
		$this->assertEquals( 2, $headers['X-WP-TotalPages'] );
		$base = add_query_arg( $request->get_query_params(), rest_url( '/wp/v2/plugins' ) );
		$next_link = add_query_arg( array(
			'page'    => 2,
		), $base );
		$this->assertFalse( stripos( $headers['Link'], 'rel="prev"' ) );
		$this->assertContains( '<' . $next_link . '>; rel="next"', $headers['Link'] );
		// Middle page doesn't exist because only hello dolly and akismet.
		/* @TODO Get extra plugins.
		$request = new WP_REST_Request( 'GET', '/wp/v2/plugins' );
		$request->set_param( 'page', 2 );
		$request->set_param( 'per_page', 1 );
		$response = $this->server->dispatch( $request );
		$headers = $response->get_headers();
		$this->assertEquals( 6, $headers['X-WP-Total'] );
		$this->assertEquals( 6, $headers['X-WP-TotalPages'] );
		$base = add_query_arg( $request->get_query_params(), rest_url( '/wp/v2/plugins' ) );
		$prev_link = add_query_arg( array(
			'page'    => 2,
		), $base );
		$this->assertContains( '<' . $prev_link . '>; rel="prev"', $headers['Link'] );
		$next_link = add_query_arg( array(
			'page'    => 4,
		), $base );
		$this->assertContains( '<' . $next_link . '>; rel="next"', $headers['Link'] );
		*/
		// Last page
		$request = new WP_REST_Request( 'GET', '/wp/v2/plugins' );
		$request->set_param( 'page', 2 );
		$request->set_param( 'per_page', 1 );
		$response = $this->server->dispatch( $request );
		$headers = $response->get_headers();
		$this->assertEquals( 2, $headers['X-WP-Total'] );
		$this->assertEquals( 2, $headers['X-WP-TotalPages'] );
		$base = add_query_arg( $request->get_query_params(), rest_url( '/wp/v2/plugins' ) );
		$prev_link = add_query_arg( array(
			'page'    => 1,
		), $base );
		$this->assertContains( '<' . $prev_link . '>; rel="prev"', $headers['Link'] );
		$this->assertFalse( stripos( $headers['Link'], 'rel="next"' ) );
		// Out of bounds
		$request = new WP_REST_Request( 'GET', '/wp/v2/plugins' );
		$request->set_param( 'page', 8 );
		$request->set_param( 'per_page', 1 );
		$response = $this->server->dispatch( $request );
		$headers = $response->get_headers();
		$this->assertEquals( 2, $headers['X-WP-Total'] );
		$this->assertEquals( 2, $headers['X-WP-TotalPages'] );
		$base = add_query_arg( $request->get_query_params(), rest_url( '/wp/v2/plugins' ) );
		$prev_link = add_query_arg( array(
			'page'    => 2,
		), $base );
		$this->assertContains( '<' . $prev_link . '>; rel="prev"', $headers['Link'] );
		$this->assertFalse( stripos( $headers['Link'], 'rel="next"' ) );
	}

	public function test_get_items_per_page() {
		wp_set_current_user( $this->admin_id );

		$request = new WP_REST_Request( 'GET', '/wp/v2/plugins' );
		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( 2, count( $response->get_data() ) );
		$request = new WP_REST_Request( 'GET', '/wp/v2/plugins' );
		$request->set_param( 'per_page', 1 );
		$response = $this->server->dispatch( $request );
		$this->assertEquals( 1, count( $response->get_data() ) );
	}

	public function test_get_items_page() {
		wp_set_current_user( $this->admin_id );

		$request = new WP_REST_Request( 'GET', '/wp/v2/plugins' );
		$request->set_param( 'per_page', 1 );
		$request->set_param( 'page', 2 );
		$response = $this->server->dispatch( $request );
		$this->assertEquals( 1, count( $response->get_data() ) );
		$base = add_query_arg( $request->get_query_params(), rest_url( '/wp/v2/plugins' ) );
		$prev_link = add_query_arg( array(
			'per_page'  => 1,
			'page'      => 1,
		), $base );
		$headers = $response->get_headers();
		$this->assertContains( '<' . $prev_link . '>; rel="prev"', $headers['Link'] );
	}

	public function test_get_items_offset() {
		wp_set_current_user( $this->admin_id );

		// 2 Plugins installed by default.
		$request = new WP_REST_Request( 'GET', '/wp/v2/plugins' );
		$request->set_param( 'offset', 1 );
		$response = $this->server->dispatch( $request );
		$this->assertCount( 1, $response->get_data() );
		// 'offset' works with 'per_page'
		$request->set_param( 'per_page', 2 );
		$request->set_param( 'offset', 0 );
		$response = $this->server->dispatch( $request );
		$this->assertCount( 2, $response->get_data() );
		// 'offset' takes priority over 'page'
		$request->set_param( 'page', 2 );
		$request->set_param( 'per_page', 2 );
		$request->set_param( 'offset', 1 );
		$response = $this->server->dispatch( $request );
		$this->assertCount( 1, $response->get_data() );
		// Out of bounds.
		$request->set_param( 'per_page', 1 );
		$request->set_param( 'offset', 2 );
		$response = $this->server->dispatch( $request );
		$this->assertCount( 0, $response->get_data() );
	}

	public function test_get_item() {

		wp_set_current_user( $this->admin_id );

		$request = new WP_REST_Request( 'GET', '/wp/v2/plugins/hello-dolly' );
		$response = $this->server->dispatch( $request );

		$this->check_get_plugins_response( $response, 'view' );
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

	protected function check_get_plugins_response( $response, $context = 'view' ) {
		$this->assertNotInstanceOf( 'WP_Error', $response );
		$response = rest_ensure_response( $response );
		$this->assertEquals( 200, $response->get_status() );
		$theme_data = $response->get_data();

		$plugin = array(); // fixme - get theme object
		$this->check_plugin_data( $plugin );
	}

	protected function check_plugin_data( $plugin ) {
		// todo: add plugin assertions
	}
}
