<?php
/**
 * - Copy this file in your tests directory
 * - Change the names and the values of all constants, in order to load the product's autoloader, if needed
 * - Mind that the tests autoloader is something else and is handled in the first statement of this file
 **/

require_once __DIR__ . '/utils/functions.php';

define( 'OTGS_TESTS_MAIN_FILE', __DIR__ . '/../../plugin.php' );
define( 'OTGS_PATH', dirname( OTGS_TESTS_MAIN_FILE ) );