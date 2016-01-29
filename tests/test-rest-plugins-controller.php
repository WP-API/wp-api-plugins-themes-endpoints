<?php

class WP_Test_REST_Plugins_Controller extends WP_Test_REST_Controller_TestCase {

	public function test_register_routes() {
		$routes = $this->server->get_routes();
		$this->assertArrayHasKey( '/wp/v2/plugins', $routes );
		$this->assertArrayHasKey( '/wp/v2/plugins/(?P<slug>[\w-]+)', $routes );
	}

	public function test_get_items_without_permissions() {
		wp_set_current_user( 0 );

		$request = new WP_REST_Request( 'GET', '/wp/v2/plugins' );

		$response = $this->server->dispatch($request);

		$this->assertEquals(403, $response->get_status());

	}

	public function test_context_param() {

	}

	public function test_get_items() {
		$request = new WP_REST_Request( 'GET', '/wp/v2/plugins' );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();
		$this->assertEquals( 2, count( $data ) );
	}

	public function test_get_item() {
        $request = new WP_REST_Request( 'GET', '/wp/v2/plugins/wp-api' );
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

	}

    protected function check_get_plugins_response( $response, $context = 'view' ) {
        $this->assertNotInstanceOf( 'WP_Error', $response );
        $response = rest_ensure_response( $response );
        $this->assertEquals( 200, $response->get_status() );
        $theme_data = $response->get_data();

        $plugin = []; // fixme - get theme object
        $this->check_plugin_data( $plugin );
    }

    protected function check_get_plugin_response( $response, $context = 'view' ) {
        $this->assertNotInstanceOf( 'WP_Error', $response );
        $response = rest_ensure_response( $response );
        $this->assertEquals( 200, $response->get_status() );

        $data = $response->get_data();
        $post = get_post( $data['id'] );
        $this->check_plugin_data( $post, $data, $context );
    }

    protected function check_plugin_data( $plugin ) {
        // todo: add plugin assertions
    }
}
