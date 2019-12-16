<?php
/**
 * @author OnTheGo Systems
 */

namespace OTGS\PhpUnit\Mocks;

class Classes {
	private $testCase;

	public function __construct( \PHPUnit\Framework\TestCase $testCase ) {
		$this->testCase = $testCase;
	}

	public function WP_Http() {
		return new Classes\WP_Http( $this->testCase );
	}
}
