<?php
/**
 * Plugin Name: WP REST API - Plugin and Theme Endpoints
 * Description: Plugin and theme endpoints for the WP REST API
 * Author: WP REST API Team
 * Author URI: http://wp-api.org
 * Version: 0.1.0
 * Plugin URI: https://github.com/WP-API/wp-api-plugins-themes-endpoints
 * License: GPL2+
 */

function plugins_themes_rest_api_init() {
    if ( class_exists( 'WP_REST_Controller' )
        && ! class_exists( 'WP_REST_Plugins_Controller' ) ) {
    }
    require_once dirname( __FILE__ ) . '/lib/class-wp-rest-plugins-controller.php';

    if ( class_exists( 'WP_REST_Controller' )
        && ! class_exists( 'WP_REST_Themes_Controller' ) ) {
        require_once dirname( __FILE__ ) . '/lib/class-wp-rest-themes-controller.php';
    }
    
	$plugins_controller = new WP_REST_Plugins_Controller();
	$plugins_controller->register_routes();

	$themes_controller = new WP_REST_Themes_Controller();
	$themes_controller->register_routes();
}

add_action( 'rest_api_init', 'plugins_themes_rest_api_init' );
