<?php
/**
 * @author OnTheGo Systems
 */

namespace OTGS\PhpUnit\Mocks\Functions\Options;

interface Option {
	public function __construct( $key );

	public function times( $value );
	public function willReturn( $value );
}
