<?php
/**
 * Plugin Name: WP REST Cache
 * Description: This plugin enables transient based caching for WP-API v2 utilising the WP-TLC-Transients library
 * Plugin URI: https://github.com/breams/wp-rest-cache/
 * Version: 0.1.0
 * Author: <a href="https://github.com/Shelob9/">Josh Pollock</a>, <a href="http://markjaquith.com">Mark Jaquith</a>, Jeremy Tweddle
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Load TLC Transients library
 *
 * @since 0.1.0
 */
if (  ! function_exists( 'tlc_transient' ) ) :

	$include_file = __DIR__ . '/WP-TLC-Transients/tlc-transients.php';
	if ( ! file_exists( $include_file ) ) {
		return;
	}

	require_once( $include_file );

endif;


/**
 * Default Cache Length
 *
 * @since 0.1.0
 */
if ( ! defined( 'WP_REST_CACHE_DEFAULT_CACHE_TIME' ) ) {
	define( 'WP_REST_CACHE_DEFAULT_CACHE_TIME', 360 );
}

if ( ! function_exists( 'wp_rest_cache_get' ) ) :
	/**
	 * Run the API query or get from cache
	 *
	 * @since 0.1.0
	 *
	 * @uses 'rest_pre_dispatch' filter
	 *
	 * @param null $result
	 * @param WP_REST_Server $server
	 * @param WP_REST_Request $request
	 */

	add_filter( 'rest_pre_dispatch', 'wp_rest_cache_get', 10, 3 );

	function wp_rest_cache_get( $result, $server, $request ) {
		if ( ! function_exists( 'wp_rest_cache_rebuild') ) {
			return $result;
		}


		$endpoint = $request->get_route();
		$method = $request->get_method();
		$request_uri = $_SERVER[ 'REQUEST_URI' ];

		rbc_wp_log( esc_url($request_uri) );

		/**
		 * Cache override.
		 *
		 * @since 0.1.0
		 *
		 * @param bool $no_cache If true, cache is skipped. If false, there will be caching.
		 * @param string $endpoint The endpoint for the current request.
		 * @param string $method The HTTP method being used to make current request.
		 *
		 * @return bool
		 */

		$skip_cache = apply_filters( 'wp_rest_cache_skip_cache', false, $endpoint, $method );
		if ( $skip_cache )  {
			$server->send_header( 'X-WP-API-Cache', 'skipped' );
			return $result;
		}

		if ( $request->get_param('refresh-cache') === true ){
			$server->send_header( 'X-WP-API-Cache', 'refreshed' );
			return $result;
		}


		/**
		 * Set cache time
		 *
		 * @since 0.1.0
		 *
		 * @param int $cache_time Time in seconds to cache for. Defaults to value of WP_REST_CACHE_DEFAULT_CACHE_TIME.
		 * @param string $endpoint The endpoint for the current request.
		 * @param string $method The HTTP method being used to make current request.
		 *
		 * @return int
		 */

		$cache_time = apply_filters( 'wp_rest_cache_cache_time', WP_REST_CACHE_DEFAULT_CACHE_TIME, $endpoint, $method );

		rbc_wp_log( $cache_time );

		$result =  tlc_transient( __FUNCTION__ . $request_uri )
			->updates_with( 'wp_rest_cache_rebuild', array( $server, $request ) )
			->expires_in( $cache_time )
			->get();

		$result->header( 'X-WP-API-Cache', 'cached' );

		return $result;
	}

endif;


if ( ! function_exists( 'wp_rest_cache_rebuild' ) ) :
	/**
	 * Rebuild the cache if needed.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Server $server
	 * @param WP_REST_Request $request
	 *
	 * @return mixed
	 */

	function wp_rest_cache_rebuild( $server, $request ) {

		$request->set_param('refresh-cache', true);
		return $server->dispatch($request);

	}

endif;
