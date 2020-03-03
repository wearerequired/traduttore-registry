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

	/**
	 * Ensures good dates are unaltered.
	 *
	 * @dataProvider good_dates
	 * @param string $date Unsanitized date string.
	 */
	public function test_good_dates_are_unaltered( $date ): void {
		$this->assertEquals( sanitize_date( $date ), new DateTime( $date ) );
	}

	/**
	 * Ensures timezones are stripped.
	 *
	 * @dataProvider dates_with_timezones
	 * @param string $date Unsanitized date string.
	 * @param string $expected Expected result after sanitization.
	 */
	public function test_timezones_are_stripped( $date, $expected ): void {
		$this->assertEquals( sanitize_date( $date ), new DateTime( $expected ) );
	}

	/**
	 * Data provider for `test_good_dates_are_unaltered`.
	 *
	 * @return array Dates that should be unaltered after sanitization.
	 */
	function good_dates() {
		return [
			['2020-02-06 09:24:03+0000'],
			['2020-02-06 09:24:03'],
			['2020-02-06'],
		];
	}

	/**
	 * Data provider for `test_timezones_are_stripped`.
	 *
	 * @return array Unsanitized date and expected result pairs.
	 */
	function dates_with_timezones() {
		return [
			[
				'Tue Jan 12 2016 15:04:22 GMT+0100 (Romance Standard Time)',
				'Tue Jan 12 2016 15:04:22 GMT+0100',
			],
			[
				'Tue Dec 22 2015 12:52:19 GMT+0100 (West-Europa)',
				'Tue Dec 22 2015 12:52:19 GMT+0100',
			],
		];
	}

}
