<?php
/**
 * @author OnTheGo Systems
 */

namespace OTGS\PhpUnit\Mocks\Functions\Transients;

interface Transient {
	public function __construct( $key );

	public function times( $value );
	public function willReturn( $value );
}
