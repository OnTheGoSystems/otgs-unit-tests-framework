<?php
/**
 * @author OnTheGo Systems
 */

namespace OTGS\Mocks\Functions\Transients;

use OTGS\PhpUnit\Mocks\Functions\FunctionBase;
use OTGS\PhpUnit\Mocks\Functions\Transients\Transient;
use OTGS\PhpUnit\Mocks\Functions\Transients\WithExpiration;
use OTGS\PhpUnit\Mocks\Functions\Transients\WithValue;

class Set extends FunctionBase implements Transient, WithValue, WithExpiration {
	private $arguments;

	public function __construct( $key ) {
		$this->arguments = array(
			'key'        => $key,
			'value'      => '*',
			'expiration' => '*',
		);
	}

	public function withValue( $value ) {
		$this->arguments['value'] = $value;

		return $this;
	}

	public function withExpiration( $value ) {
		$this->arguments['expiration'] = $value;

		return $this;
	}

	public function get_function_name() {
		return 'set_transient';
	}

	public function get_arguments() {
		return $this->arguments;
	}
}
