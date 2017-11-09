<?php
/**
 * @author OnTheGo Systems
 */

namespace OTGS\Mocks\Functions\Transients;

use OTGS\PhpUnit\Mocks\Functions\FunctionBase;
use OTGS\PhpUnit\Mocks\Functions\Transients\Transient;

class Delete extends FunctionBase implements Transient {
	private $arguments = array();

	public function __construct( $key ) {
		$this->arguments['args'] = array(
			'key' => $key,
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
		return 'delete_transient';
	}

	public function get_arguments() {
		return $this->arguments;
	}
}
