<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Required\Traduttore
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL;
	exit( 1 );
}

$_root_dir = dirname( __DIR__, 2  );

if ( ! file_exists( dirname( __DIR__, 2  ). '/vendor/autoload.php' ) ) {
	echo "Could not find $_root_dir/vendor/autoload.php, have you run composer install ?" . PHP_EOL;
	exit( 1 );
}

putenv( "WP_TESTS_DIR=$_tests_dir" );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
