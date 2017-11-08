<?php
/**
 * @author OnTheGo Systems
 */

namespace OTGS\PhpUnit\Mocks\Functions;

use OTGS\Mocks\Functions\Options\Add;
use OTGS\Mocks\Functions\Options\Delete;
use OTGS\Mocks\Functions\Options\Get;
use OTGS\Mocks\Functions\Options\Update;

class Options {
	public function get( $key ) {
		return new Get( $key );
	}

	public function add( $key ) {
		return new Add( $key );
	}

	public function delete( $key ) {
		return new Delete( $key );
	}

	public function update( $key ) {
		return new Update( $key );
	}
}
