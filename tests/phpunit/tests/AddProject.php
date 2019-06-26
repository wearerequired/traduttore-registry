<?php
/**
 * Class AddProject
 *
 * @package Traduttore\Tests
 */

namespace Required\Traduttore_Registry\Tests;

use DateTime;
use \WP_UnitTestCase;
use function \Required\Traduttore_Registry\add_project;

/**
 *  Test cases for the add_project() function.
 */
class AddProject extends WP_UnitTestCase {
	/**
	 * Verifies that calls to translation_api() are filtered.
	 */
	public function test_filter_translation_api(): void {
		require_once ABSPATH . 'wp-admin/includes/translation-install.php';

		$type     = 'plugin';
		$slug     = 'test-plugin';
		$api_url  = 'https://translate.required.com/api/translations/required/test-plugin/';
		$expected = [ 'foo' => 'bar' ];

		add_project( $type, $slug, $api_url );

		add_filter(
			'pre_http_request',
			static function ( $result, $args, $url ) use ( $api_url, $expected ) {
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

		$actual = translations_api(
			'plugins',
			[
				'slug'    => $slug,
				'version' => '1.0',
			]
		);

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Verifies that calls to translation_api() are filtered.
	 */
	public function test_do_not_filter_translation_api_for_other_projects(): void {
		require_once ABSPATH . 'wp-admin/includes/translation-install.php';

		$type     = 'plugin';
		$slug     = 'test-plugin';
		$api_url  = 'https://translate.required.com/api/translations/required/test-plugin/';
		$expected = [ 'foo' => 'bar' ];

		add_project( $type, $slug, $api_url );

		add_filter(
			'translations_api',
			static function ( $result ) use ( $expected ) {
				if ( false === $result ) {
					return $expected;
				}

				return $result;
			},
			100
		);

		$actual = translations_api(
			'plugins',
			[
				'slug'    => 'bar-plugin',
				'version' => '1.0',
			]
		);

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Verifies that calls to wp_get_translation_updates() are filtered.
	 */
	public function test_get_translation_updates(): void {
		$type    = 'plugin';
		$slug    = 'internationalized-plugin'; // Part of the WordPress test suite.
		$api_url = 'https://translate.required.com/api/translations/required/internationalized-plugin/';
		$now     = ( new DateTime() )->format( 'Y-m-d H:i:s' );
		$locale  = 'de_DE';
		$package = 'https://translate.required.com/content/traduttore/internationalized-plugin-de_DE.zip';

		$body = [
			'translations' => [
				[
					'language' => $locale,
					'updated'  => $now,
					'package'  => $package,
				],
			],
		];

		$expected = [
			'language' => $locale,
			'updated'  => $now,
			'package'  => $package,
			'type'     => $type,
			'slug'     => $slug,
		];

		add_project( $type, $slug, $api_url );

		add_filter(
			'pre_http_request',
			static function ( $result, $args, $url ) use ( $api_url, $body ) {
				if ( $api_url === $url ) {
					return [
						'headers'  => [],
						'body'     => json_encode( $body ),
						'response' => [],
					];
				}

				return $result;
			},
			10,
			3
		);

		$actual = wp_get_translation_updates();

		$this->assertArraySubset( [ (object) $expected ], $actual );
	}

	/**
	 * Verifies that calls to wp_get_translation_updates() are not filtered
	 * when the remote package is older than the local one.
	 */
	public function test_get_translation_updates_outdated_package(): void {
		$type    = 'plugin';
		$slug    = 'internationalized-plugin'; // Part of the WordPress test suite.
		$api_url = 'https://translate.required.com/api/translations/required/internationalized-plugin/';
		$now     = ( new DateTime() )->setDate( 2000, 1, 1 )->format( 'Y-m-d H:i:s' );
		$locale  = 'de_DE';
		$package = 'https://translate.required.com/content/traduttore/internationalized-plugin-de_DE.zip';

		$body = [
			'translations' => [
				[
					'language' => $locale,
					'updated'  => $now,
					'package'  => $package,
				],
			],
		];

		add_project( $type, $slug, $api_url );

		add_filter(
			'pre_http_request',
			static function ( $result, $args, $url ) use ( $api_url, $body ) {
				if ( $api_url === $url ) {
					return [
						'headers'  => [],
						'body'     => json_encode( $body ),
						'response' => [],
					];
				}

				return $result;
			},
			10,
			3
		);

		$actual = wp_get_translation_updates();

		$this->assertEmpty( $actual );
	}

	/**
	 * Verifies that calls to wp_get_translation_updates() are not filtered
	 * when the API returns no translations.
	 */
	public function test_get_translation_updates_empty_api_result(): void {
		$type    = 'plugin';
		$slug    = 'internationalized-plugin'; // Part of the WordPress test suite.
		$api_url = 'https://translate.required.com/api/translations/required/internationalized-plugin/';
		$body    = [];

		add_project( $type, $slug, $api_url );

		add_filter(
			'pre_http_request',
			static function ( $result, $args, $url ) use ( $api_url, $body ) {
				if ( $api_url === $url ) {
					return [
						'headers'  => [],
						'body'     => json_encode( $body ),
						'response' => [],
					];
				}

				return $result;
			},
			10,
			3
		);

		$actual = wp_get_translation_updates();

		$this->assertEmpty( $actual );
	}

	/**
	 * Verifies that only translation updates for actually available languages are returned.
	 *
	 * @see https://github.com/wearerequired/traduttore-registry/issues/28
	 */
	public function test_get_translation_updates_available_languages(): void {
		$type    = 'plugin';
		$slug    = 'internationalized-plugin'; // Part of the WordPress test suite.
		$api_url = 'https://translate.required.com/api/translations/required/internationalized-plugin/';
		$now     = ( new DateTime() )->format( 'Y-m-d H:i:s' );
		$locale  = 'de_DE';
		$package = 'https://translate.required.com/content/traduttore/internationalized-plugin-de_DE.zip';

		$body = [
			'translations' => [
				[
					'language' => $locale,
					'updated'  => $now,
					'package'  => $package,
				],
				[
					'language' => 'de_CH',
					'updated'  => $now,
					'package'  => $package,
				],
			],
		];

		$expected = [
			'language' => $locale,
			'updated'  => $now,
			'package'  => $package,
			'type'     => $type,
			'slug'     => $slug,
		];

		add_project( $type, $slug, $api_url );

		add_filter(
			'pre_http_request',
			static function ( $result, $args, $url ) use ( $api_url, $body ) {
				if ( $api_url === $url ) {
					return [
						'headers'  => [],
						'body'     => json_encode( $body ),
						'response' => [],
					];
				}

				return $result;
			},
			10,
			3
		);

		$actual = wp_get_translation_updates();

		$this->assertArraySubset( [ (object) $expected ], $actual );
		$this->assertCount( 1, $actual );
	}
}
