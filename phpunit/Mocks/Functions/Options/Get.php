<?php
/**
 * @author OnTheGo Systems
 */

namespace OTGS\Mocks\Functions\Options;

use OTGS\PhpUnit\Mocks\Functions\FunctionBase;
use OTGS\PhpUnit\Mocks\Functions\Options\Option;
use OTGS\PhpUnit\Mocks\Functions\Options\WithDefault;

class Get extends FunctionBase implements Option, WithDefault {
	private $arguments;

	public function __construct( $key ) {
		$this->arguments = array(
			'key'     => $key,
			'default' => '*',
		);
	}

	public function withDefault( $value ) {
		$this->arguments['default'] = $value;

		return $this;
	}

	public function get_function_name() {
		return 'get_option';
	}

	public function get_arguments() {
		return $this->arguments;
	}
}
