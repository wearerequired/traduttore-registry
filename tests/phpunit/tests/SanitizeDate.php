<?php
/**
 * Class SanitizeDate
 *
 * @package Traduttore\Tests
 */

namespace Required\Traduttore_Registry\Tests;

use DateTime;
use \WP_UnitTestCase;
use function \Required\Traduttore_Registry\sanitize_date;

/**
 *  Tests dates by the sanitize_date() function.
 */
class SanitizeDate extends WP_UnitTestCase {

	private $result;

	/**
	 * Ensures good dates are unaltered.
	 */
	public function test_good_dates_are_unaltered(): void {
		$good_dates = [
			'2020-02-06 09:24:03+0000',
			'2020-02-06 09:24:03',
			'2020-02-06',
		];

		foreach	( $good_dates as $date ) {
			$this->assertEquals( sanitize_date( $date ), new DateTime( $date ) );
		}
	}

	/**
	 * Ensures timezones are stripped.
	 */
	public function test_timezones_are_stripped(): void {
		$dates_with_timezones = [
			[
				'input'    => 'Tue Jan 12 2016 15:04:22 GMT+0100 (Romance Standard Time)',
				'expected' => 'Tue Jan 12 2016 15:04:22 GMT+0100'
			],
			[
				'input'    => 'Tue Dec 22 2015 12:52:19 GMT+0100 (West-Europa)',
				'expected' => 'Tue Dec 22 2015 12:52:19 GMT+0100',
			],
		];

		foreach	( $dates_with_timezones as $date ) {
			$this->assertEquals(
				sanitize_date( $date['input'] ),
				new DateTime( $date['expected'] )
			);
		}
	}
}
