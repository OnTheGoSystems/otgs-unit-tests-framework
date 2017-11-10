<?php
/**
 * @author OnTheGo Systems
 */

namespace OTGS\Mocks\Functions\Options;

use OTGS\PhpUnit\Mocks\Functions\FunctionBase;
use OTGS\PhpUnit\Mocks\Functions\Options\Option;

class Delete extends FunctionBase implements Option {
	private $arguments;

	public function __construct( $key ) {
		$this->arguments = array(
			'key' => $key,
		);
	}

	public function get_function_name() {
		return 'delete_option';
	}

	public function get_arguments() {
		return $this->arguments;
	}
}
