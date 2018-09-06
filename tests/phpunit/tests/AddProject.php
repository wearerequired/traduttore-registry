<?php
/**
 * Class AddProject
 *
 * @package Traduttore\Tests
 */

namespace Required\Traduttore_Registry\Tests;

use \WP_UnitTestCase;
use function \Required\Traduttore_Registry\add_project;

/**
 *  Test cases for the add_project() function.
 */
class AddProject extends WP_UnitTestCase {
	/**
	 * Verifies that calls to translation_api() are filtered.
	 */
	public function test_filter_translation_api() {
		require_once ABSPATH . 'wp-admin/includes/translation-install.php';

		$type     = 'plugin';
		$slug     = 'test-plugin';
		$api_url  = 'https://translate.required.com/api/translations/required/test-plugin/';
		$expected = [ 'foo' => 'bar' ];

		add_project( $type, $slug, $api_url );

		add_filter(
			'pre_http_request',
			function ( $result, $args, $url ) use ( $api_url, $expected ) {
				if ( $api_url === $url ) {
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

		$actual = translations_api( 'plugins', [ 'slug' => $slug ] );

		$this->assertSame( $expected, $actual );
	}
}
