<?php
/**
 * @author OnTheGo Systems
 */

/* This is required in order to load all the needed dependencies */
require_once __DIR__ . '/vendor/otgs/unit-tests-framework/phpunit/bootstrap.php';

/* This is needed to autoload classes, based on your own composer.json (see `samples` directory) */
require_once __DIR__ . '/vendor/autoload.php';

/**
 * You can either extend your test classes from `OTGS_TestCase` or create your own parent class which extend `OTGS_TestCase`.
 * In the first case you must include here your own base class,
 * otherwise you can remove that line, as `OTGS_TestCase` is auto loaded.
*/
//require_once __DIR__ . '/my-testcase.php';

/**
 * If your product (plugin, theme, etc.) has his own autoloader, you must include this file as well,
 * otherwise you will need to include all the files you want to test
 */
define( 'WPML_TESTS_MAIN_FILE', __DIR__ . '/../../plugin.php' );
define( 'WPML_PATH', dirname( WPML_TESTS_MAIN_FILE ) );

$autoloader_dir = WPML_PATH . '/embedded';
if ( version_compare( PHP_VERSION, '5.3.0' ) >= 0 ) {
	$autoloader = $autoloader_dir . '/autoload.php';
} else {
	$autoloader = $autoloader_dir . '/autoload_52.php';
}
require_once $autoloader;
