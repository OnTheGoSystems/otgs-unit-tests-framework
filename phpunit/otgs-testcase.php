<?php

use tad\FunctionMocker\FunctionMocker;

/**
 * @author OnTheGo Systems
 */
abstract class OTGS_TestCase extends \PHPUnit\Framework\TestCase {
	/** @var FactoryMuffin */
	protected static $fm;
	/** @var OTGS_Stubs */
	protected $stubs;

	/** @var \OTGS\PhpUnit\Mocks\Classes */
	protected $mocked_classes;

	/** @var OTGS_Mocked_WP_Core_Functions */
	protected $mocked_wp_core_functions;

	public static function setupBeforeClass() {
		$_GET  = array();
		$_POST = array();
	}

	function setUp() {
		parent::setUp();
		FunctionMocker::setUp();
		WP_Mock::setUp();
		$this->stubs = new OTGS_Stubs( $this );
	}

	function tearDown() {
		unset( $this->stubs );
		WP_Mock::tearDown();
		FunctionMocker::tearDown();
		Mockery::close();
		parent::tearDown();
	}

	protected function mockedWPClasses() {
		if ( ! $this->mocked_classes ) {
			$this->mocked_classes = new OTGS\PhpUnit\Mocks\Classes( $this );
		}

		return $this->mocked_classes;
	}

	/**
	 * @return \OTGS\Mocks\LegacyWPCore
	 */
	protected function get_mocked_wp_core_functions() {
		if ( ! $this->mocked_wp_core_functions ) {
			$this->mocked_wp_core_functions = new \OTGS\Mocks\LegacyWPCore( $this );
		}

		return $this->mocked_wp_core_functions;
	}

	protected function mock_all_core_functions() {
		$functions = $this->get_mocked_wp_core_functions();

		$functions->functions();
		$functions->wp_error();
		$functions->post();
		$functions->taxonomy();
		$functions->meta();
		$functions->link_template();
		$functions->plugin();
		$functions->theme();
		$functions->i10n();
		$functions->formatting();
		$functions->user();
		$functions->option();
		$functions->query();
		$functions->shortcodes();
		$functions->pluggable();
	}

	/**
	 * @deprecated Use `$this->stubs->wpdb()`
	 * @return wpdb|PHPUnit_Framework_MockObject_MockObject
	 */
	protected function get_wpdb_stub() {
		return $this->stubs->wpdb();
	}

	/**
	 * @deprecated Use `$this->stubs->WP_Query()`
	 * @return WP_Query|PHPUnit_Framework_MockObject_MockObject
	 */
	protected function get_wp_query_stub() {
		return $this->stubs->WP_Query();
	}

	/**
	 * @deprecated Use `$this->stubs->WP_Filesystem_Direct()`
	 * @return WP_Filesystem_Direct|PHPUnit_Framework_MockObject_MockObject
	 */
	function get_wp_filesystem_direct_stub() {
		return $this->stubs->WP_Filesystem_Direct();
	}

	/**
	 * @deprecated Use `$this->stubs->WP_Theme()`
	 * @return WP_Theme|PHPUnit_Framework_MockObject_MockObject
	 */
	function get_wp_theme_stub() {
		return $this->stubs->WP_Theme();
	}

	/**
	 * @deprecated Use `$this->stubs->WP_Widget()`
	 * @return WP_Widget|PHPUnit_Framework_MockObject_MockObject
	 */
	function get_wp_widget_stub() {
		return $this->stubs->WP_Widget();
	}

	/**
	 * @param string $action_name
	 * @param array  $action_args
	 * @param int    $times
	 */
	function expectAction( $action_name, array $action_args = array(), $times = null ) {
		$intercept = \Mockery::mock( 'intercept' );

		if ( null !== $times ) {
			$intercept->shouldReceive( 'intercepted' )->times( $times );
		} else {
			$intercept->shouldReceive( 'intercepted' )->atLeast()->once();
		}

		$action    = \WP_Mock::onAction( $action_name );
		$responder = call_user_func_array( array( $action, 'with' ), $action_args );
		$responder->perform( array( $intercept, 'intercepted' ) );
	}

	/**
	 * @param string $action   The action name
	 * @param string $callback The callback that should be registered
	 * @param int    $priority The priority it should be registered at
	 * @param int    $args     The number of arguments that should be allowed
	 * @param int    $times
	 */
	function expectActionAdded( $action, $callback, $priority, $args = 1, $times = null ) {
		$this->expectHookAdded( $action, $callback, $priority, $args, $times, 'action' );
	}

	/**
	 * @param string $filter   The filter name
	 * @param string $callback The callback that should be registered
	 * @param int    $priority The priority it should be registered at
	 * @param int    $args     The number of arguments that should be allowed
	 * @param int    $times
	 */
	function expectFilterAdded( $filter, $callback, $priority, $args = 1, $times = null ) {
		$this->expectHookAdded( $filter, $callback, $priority, $args, $times );
	}

	private function expectHookAdded( $action, $callback, $priority, $args = 1, $times = null, $type = 'filter' ) {
		$intercept = \Mockery::mock( 'intercept' );

		if ( null !== $times ) {
			$intercept->shouldReceive( 'intercepted' )->times( $times );
		} else {
			$intercept->shouldReceive( 'intercepted' )->atLeast()->once();
		}
		/** @var WP_Mock\HookedCallbackResponder $responder */
		$responder = \WP_Mock::onHookAdded( $action, $type )->with( $callback, $priority, $args );
		$responder->perform( array( $intercept, 'intercepted' ) );
	}
}
