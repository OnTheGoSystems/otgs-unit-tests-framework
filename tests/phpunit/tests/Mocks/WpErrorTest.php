<?php

namespace OTGS\Tests\Mocks;

/**
 * @group wp-error
 */
class WpErrorTest extends \OTGS_TestCase {

	/**
	 * @test
	 */
	public function it_recognises_the_wordpress_wp_error() {
		$this->get_mocked_wp_core_functions()->wp_error();

		$this->assertTrue( is_wp_error( new \WP_Error( 'invalid_taxonomy', 'Invalid taxonomy.' ) ) );
	}

	/**
	 * @test
	 */
	public function it_recognises_the_otgs_mocks_wp_error() {
		$this->get_mocked_wp_core_functions()->wp_error();

		$this->assertTrue( is_wp_error( new \OTGS\Mocks\WP_Error() ) );
	}

	/**
	 * @test
	 */
	public function it_recognises_the_wordpress_wp_error_when_every_core_function_is_mocked() {
		$this->mock_all_core_functions();

		$this->assertTrue( is_wp_error( new \WP_Error() ) );
	}

	/**
	 * @test
	 * @dataProvider not_errors
	 */
	public function it_does_not_recognise_anything_else( $thing ) {
		$this->get_mocked_wp_core_functions()->wp_error();

		$this->assertFalse( is_wp_error( $thing ) );
	}

	public function not_errors() {
		return array(
			'null'         => array( null ),
			'false'        => array( false ),
			'string'       => array( 'error' ),
			'array'        => array( array( 'errors' => array() ) ),
			'other object' => array( new \stdClass() ),
		);
	}
}
