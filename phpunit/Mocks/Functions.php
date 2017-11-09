<?php
/**
 * @author OnTheGo Systems
 */

namespace OTGS\Mocks;

use OTGS\PhpUnit\Mocks\Functions\Options;
use OTGS\PhpUnit\Mocks\Functions\Transients;

class Functions {
	public function Options() {
		return new Options();
	}

	public function Transients() {
		return new Transients();
	}
}
