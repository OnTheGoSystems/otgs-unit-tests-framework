<?php
/**
 * @author OnTheGo Systems
 */

namespace OTGS\PhpUnit\Mocks\Functions;

abstract class FunctionBase {
	abstract public function get_function_name();

	abstract public function get_arguments();

	public function mock() {
		\WP_Mock::userFunction( $this->get_function_name(), $this->get_arguments() );
	}
}
