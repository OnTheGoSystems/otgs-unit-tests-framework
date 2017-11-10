<?php
/**
 * @author OnTheGo Systems
 */

namespace OTGS\PhpUnit\Mocks\Functions;

abstract class FunctionBase {
	private $definition = array();

	abstract public function get_function_name();

	abstract public function get_arguments();

	public function mock() {
		$this->definition['args'] = $this->get_arguments();
		return \WP_Mock::userFunction( $this->get_function_name(), $this->definition );
	}

	public function times( $value ) {
		$this->definition['times'] = $value;

		return $this;
	}

	public function willReturn( $value ) {
		$this->definition['return'] = $value;

		return $this->mock();
	}
}
