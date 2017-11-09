<?php
/**
 * @author OnTheGo Systems
 */

namespace OTGS\PhpUnit\Mocks\Functions;

use OTGS\Mocks\Functions\Transients\Delete;
use OTGS\Mocks\Functions\Transients\Get;
use OTGS\Mocks\Functions\Transients\Set;

class Transients {
	public function get( $key ) {
		return new Get( $key );
	}

	public function set( $key ) {
		return new Set( $key );
	}

	public function delete( $key ) {
		return new Delete( $key );
	}
}
