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
	private $arguments = array();

	public function __construct( $key ) {
		$this->arguments['args'] = array(
			'key'        => $key,
			'value'      => '*',
			'expiration' => 0,
		);
	}

	public function times( $value ) {
		$this->arguments['times'] = $value;

		return $this;
	}

	public function willReturn( $value ) {
		$this->arguments['return'] = $value;

		return $this;
	}

	public function get_function_name() {
		return 'set_transient';
	}

	public function get_arguments() {
		return $this->arguments;
	}

	public function withValue( $value ) {
		$this->arguments['args']['value'] = $value;

		return $this;
	}

	public function withExpiration( $value ) {
		$this->arguments['args']['expiration'] = $value;

		return $this;
	}
}
