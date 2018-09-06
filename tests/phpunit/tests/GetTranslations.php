<?php
/**
 * Class GetTranslations
 *
 * @package Traduttore\Tests
 */

namespace Required\Traduttore_Registry\Tests;

use \WP_UnitTestCase;
use function \Required\Traduttore_Registry\get_translations;

/**
 *  Test cases for the get_translations() function.
 */
class GetTranslations extends WP_UnitTestCase {
	public function test_plugin_translations() {
		$api_url  = 'https://translate.required.com/api/translations/required/foo-plugin/';
		$expected = [ 'foo' => 'bar' ];


		add_filter(
			'pre_http_request',
			function ( $result, $args, $url ) use ( $api_url, $expected ) {
				if ( $api_url === $url ) {
					return [ 'headers' => [], 'body' => json_encode( $expected ), 'response' => [] ];
				}

				return $result;
			},
			10,
			3
		);

		$actual = get_translations( 'plugin', 'fo-plugin', $api_url );

		$this->assertSame( $expected, $actual );
	}

	public function test_data_is_stored_in_transient() {
		$api_url  = 'https://translate.required.com/api/translations/required/bar-plugin/';
		$expected = [ 'foo' => 'bar' ];

		add_filter(
			'pre_http_request',
			function ( $result, $args, $url ) use ( $api_url, $expected ) {
				remove_filter( 'pre_http_request', __FUNCTION__ );

				if ( $api_url === $url ) {
					return [ 'headers' => [], 'body' => json_encode( $expected ), 'response' => [] ];
				}

				return $result;
			},
			10,
			3
		);

		get_translations( 'plugin', 'bar-plugin', $api_url );

		self::assertNotFalse( get_site_transient( 'plugin_translations_bar-plugin' ) );
	}
}
