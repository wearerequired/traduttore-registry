<?php
/**
 * Class GetTranslations
 *
 * @package Traduttore\Tests
 */

namespace Required\Traduttore_Registry\Tests;

use \WP_UnitTestCase;
use const Required\Traduttore_Registry\TRANSIENT_KEY_PLUGIN;
use function \Required\Traduttore_Registry\get_translations;

/**
 *  Test cases for the get_translations() function.
 */
class GetTranslations extends WP_UnitTestCase {
	/**
	 * Verifies that translation information is loaded from GlotPress.
	 */
	public function test_plugin_translations(): void {
		$api_url  = 'https://translate.required.com/api/translations/required/foo-plugin/';
		$expected = [ 'foo' => 'bar' ];

		add_filter(
			'pre_http_request',
			static function ( $result, $args, $url ) use ( $api_url, $expected ) {
				if ( $api_url === $url ) {
					remove_filter( 'pre_http_request', __FUNCTION__ );

					return [
						'headers'  => [],
						'body'     => json_encode( $expected ),
						'response' => [],
					];
				}

				return $result;
			},
			10,
			3
		);

		$actual = get_translations( 'plugin', 'foo-plugin', $api_url );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Verifies that API results are cached in a transient.
	 */
	public function test_data_is_stored_in_transient(): void {
		$api_url  = 'https://translate.required.com/api/translations/required/bar-plugin/';
		$expected = [ 'foo' => 'bar' ];

		add_filter(
			'pre_http_request',
			static function ( $result, $args, $url ) use ( $api_url, $expected ) {
				if ( $api_url === $url ) {
					remove_filter( 'pre_http_request', __FUNCTION__ );

					return [
						'headers'  => [],
						'body'     => json_encode( $expected ),
						'response' => [],
					];
				}

				return $result;
			},
			10,
			3
		);

		get_translations( 'plugin', 'bar-plugin', $api_url );

		$transient = get_site_transient( TRANSIENT_KEY_PLUGIN );
		self::assertNotFalse( $transient->{'bar-plugin'} );
	}

	/**
	 * Verifies that subsequent requests are served from cache.
	 */
	public function test_return_cached_data_on_subsequent_requests(): void {
		$api_url       = 'https://translate.required.com/api/translations/required/bar-plugin/';
		$expected      = [ 'foo' => 'bar' ];
		$request_count = 0;

		$request_counter = function( $result, $args, $url ) use ( $api_url, &$request_count ) {
			if ( $api_url === $url ) {
				$request_count++;
			}

			return $result;
		};

		add_filter( 'pre_http_request', $request_counter, 10, 3 );

		add_filter(
			'pre_http_request',
			static function ( $result, $args, $url ) use ( $api_url, $expected ) {
				if ( $api_url === $url ) {
					remove_filter( 'pre_http_request', __FUNCTION__ );

					return [
						'headers'  => [],
						'body'     => json_encode( $expected ),
						'response' => [],
					];
				}

				return $result;
			},
			10,
			3
		);

		get_translations( 'plugin', 'bar-plugin', $api_url );
		$actual = get_translations( 'plugin', 'bar-plugin', $api_url );

		remove_filter( 'pre_http_request', $request_counter );

		$this->assertSame( 1, $request_count );
		$this->assertSame( $expected, $actual );
	}

	/**
	 * Verifies that failed requests are served from cache.
	 */
	public function test_return_cached_data_on_request_after_previous_failure(): void {
		$api_url       = 'https://translate.required.com/api/translations/required/bar-plugin/';
		$expected      = [];
		$request_count = 0;

		$request_counter = function( $result, $args, $url ) use ( $api_url, &$request_count ) {
			if ( $api_url === $url ) {
				$request_count++;
			}

			return $result;
		};

		add_filter( 'pre_http_request', $request_counter, 10, 3 );

		add_filter(
			'pre_http_request',
			static function ( $result, $args, $url ) use ( $api_url ) {
				if ( $api_url === $url ) {
					remove_filter( 'pre_http_request', __FUNCTION__ );

					return new \WP_Error();
				}

				return $result;
			},
			10,
			3
		);

		get_translations( 'plugin', 'bar-plugin', $api_url );
		$actual = get_translations( 'plugin', 'bar-plugin', $api_url );

		remove_filter( 'pre_http_request', $request_counter );

		$this->assertSame( 1, $request_count );
		$this->assertSame( $expected, $actual );
	}
}
