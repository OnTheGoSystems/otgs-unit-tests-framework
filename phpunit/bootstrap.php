<?php
/**
 * - Copy this file in your tests directory
 * - Change the names and the values of all constants, in order to load the product's autoloader, if needed
 * - Mind that the tests autoloader is something else and is handled in the first statement of this file
 **/

require_once __DIR__ . '/utils/functions.php';

define( 'OTGS_TESTS_MAIN_FILE', __DIR__ . '/../../plugin.php' );
define( 'OTGS_PATH', dirname( OTGS_TESTS_MAIN_FILE ) );

define( 'MINUTE_IN_SECONDS', 60 );
define( 'HOUR_IN_SECONDS',   60 * MINUTE_IN_SECONDS );
define( 'DAY_IN_SECONDS',    24 * HOUR_IN_SECONDS   );
define( 'WEEK_IN_SECONDS',    7 * DAY_IN_SECONDS    );
define( 'MONTH_IN_SECONDS',  30 * DAY_IN_SECONDS    );
define( 'YEAR_IN_SECONDS',  365 * DAY_IN_SECONDS    );

FunctionMocker::init();
