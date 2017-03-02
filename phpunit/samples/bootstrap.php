<?php
/**
 * @author OnTheGo Systems
 */

/**
 * You can define and customize shared constants here
 * It's a good practice, for instance, to define here the constants your plugin uses to find itself (path, URL, etc.)
 */
//define( 'WPML_TESTS_MAIN_FILE', __DIR__ . '/../../plugin.php' );
//define( 'WPML_PATH', dirname( WPML_TESTS_MAIN_FILE ) );

/**
 * This is required in order to load all the dependencies needed to extend `OTGS_TestCase`
 *
 * Make sure to adjust the path according to the location of your vendor directory
 * The following example assumes that your bootstrap.php is in `{project_root}/tests/phpunit` and that
 * your vendor directory is called `{project_root}/vendor`
 */
require_once __DIR__ . '/../../vendor/otgs/unit-tests-framework/phpunit/bootstrap.php';

/**
 * This is required to autoload your project's classes (including this tests framework), based on your own composer.json
 *
 * Make sure to adjust the path according to the location of your vendor directory
 * The following example assumes that your bootstrap.php is in `{project_root}/tests/phpunit` and that
 * your vendor directory is called `{project_root}/vendor`
 */
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * You can either extend your test classes from `OTGS_TestCase` or create your own parent class which extend `OTGS_TestCase`.
 * In the first case you must include here your own base class,
 * otherwise you can remove that line, as `OTGS_TestCase` is auto loaded.
*/
//require_once __DIR__ . '/my-testcase.php';
