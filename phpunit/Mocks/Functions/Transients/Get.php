<?php
/**
 * @author OnTheGo Systems
 */

namespace OTGS\Mocks\Functions\Transients;

use OTGS\PhpUnit\Mocks\Functions\FunctionBase;
use OTGS\PhpUnit\Mocks\Functions\Transients\Transient;

class Get extends FunctionBase implements Transient {
	private $arguments;

	public function __construct( $key ) {
		$this->arguments = array(
			'key' => $key,
		);
	}

	public function get_function_name() {
		return 'get_transient';
	}

	public function get_arguments() {
		return $this->arguments;
	}
}
