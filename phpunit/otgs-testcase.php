<?php

use OTGS\Mocks\LegacyWPCore;
use OTGS\PhpUnit\Mocks\Classes;
use League\FactoryMuffin\FactoryMuffin;
use tad\FunctionMocker\FunctionMocker;

/**
 * Class OTGS_TestCase
 *
 * @version 2.0.0 for phpunit-7.5
 * @author OnTheGo Systems
 */
abstract class OTGS_TestCase extends PHPUnit\Framework\TestCase {
	/** @var FactoryMuffin */
	protected static $fm;

	/** @var OTGS_Stubs */
	protected $stubs;

	/** @var Classes */
	protected $mocked_classes;

	/** @var LegacyWPCore */
	protected $mocked_wp_core_functions;

	public static function setupBeforeClass() {
		$_GET  = array();
		$_POST = array();
	}

	public function setUp() {
		FunctionMocker::setUp();
		parent::setUp();
		WP_Mock::setUp();
		$this->stubs = new OTGS_Stubs( $this );
	}

	public function tearDown() {
		unset( $this->stubs );
		WP_Mock::tearDown();
		Mockery::close();
		parent::tearDown();
		FunctionMocker::tearDown();
	}

	protected function mockedWPClasses() {
		if ( ! $this->mocked_classes ) {
			$this->mocked_classes = new OTGS\PhpUnit\Mocks\Classes( $this );
		}

		return $this->mocked_classes;
	}

	/**
	 * @return LegacyWPCore
	 */
	protected function get_mocked_wp_core_functions() {
		if ( ! $this->mocked_wp_core_functions ) {
			$this->mocked_wp_core_functions = new LegacyWPCore( $this );
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
	 * @return wpdb|PHPUnit\Framework\MockObject\MockObject
	 */
	protected function get_wpdb_stub() {
		return $this->stubs->wpdb();
	}

	/**
	 * @deprecated Use `$this->stubs->WP_Query()`
	 * @return WP_Query|PHPUnit\Framework\MockObject\MockObject
	 */
	protected function get_wp_query_stub() {
		return $this->stubs->WP_Query();
	}

	/**
	 * @deprecated Use `$this->stubs->WP_Filesystem_Direct()`
	 * @return WP_Filesystem_Direct|PHPUnit\Framework\MockObject\MockObject
	 */
	public function get_wp_filesystem_direct_stub() {
		return $this->stubs->WP_Filesystem_Direct();
	}

	/**
	 * @deprecated Use `$this->stubs->WP_Theme()`
	 * @return WP_Theme|PHPUnit\Framework\MockObject\MockObject
	 */
	public function get_wp_theme_stub() {
		return $this->stubs->WP_Theme();
	}

	/**
	 * @deprecated Use `$this->stubs->WP_Widget()`
	 * @return WP_Widget|PHPUnit\Framework\MockObject\MockObject
	 */
	public function get_wp_widget_stub() {
		return $this->stubs->WP_Widget();
	}

	/**
	 * @param string $action_name
	 * @param array  $action_args
	 * @param int    $times
	 */
	public function expectAction( $action_name, array $action_args = array(), $times = null ) {
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
	 * @param string $action   The action name.
	 * @param string $callback The callback that should be registered.
	 * @param int    $priority The priority it should be registered at.
	 * @param int    $args     The number of arguments that should be allowed.
	 * @param int    $times    Number of times.
	 */
	public function expectActionAdded( $action, $callback, $priority, $args = 1, $times = null ) {
		$this->expectHookAdded( $action, $callback, $priority, $args, $times, 'action' );
	}

	/**
	 * @param string $filter   The filter name.
	 * @param string $callback The callback that should be registered.
	 * @param int    $priority The priority it should be registered at.
	 * @param int    $args     The number of arguments that should be allowed.
	 * @param int    $times    Number of times.
	 */
	public function expectFilterAdded( $filter, $callback, $priority, $args = 1, $times = null ) {
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

	/**
	 * Get an object protected property.
	 *
	 * @param object $object        Object.
	 * @param string $property_name Property name.
	 *
	 * @return mixed
	 *
	 * @throws ReflectionException Reflection exception.
	 */
	protected function get_protected_property( $object, $property_name ) {
		$reflection_class = new \ReflectionClass( $object );

		$property = $reflection_class->getProperty( $property_name );
		$property->setAccessible( true );
		$value = $property->getValue( $object );
		$property->setAccessible( false );

		return $value;
	}

	/**
	 * Set an object protected property.
	 *
	 * @param object $object        Object.
	 * @param string $property_name Property name.
	 * @param mixed  $value         Property vale.
	 *
	 * @throws ReflectionException Reflection exception.
	 */
	protected function set_protected_property( $object, $property_name, $value ) {
		$reflection_class = new \ReflectionClass( $object );

		$property = $reflection_class->getProperty( $property_name );
		$property->setAccessible( true );
		$property->setValue( $object, $value );
		$property->setAccessible( false );
	}

	/**
	 * Set an object protected method accessibility.
	 *
	 * @param object $object      Object.
	 * @param string $method_name Property name.
	 * @param bool   $accessible  Property vale.
	 *
	 * @return ReflectionMethod
	 *
	 * @throws ReflectionException Reflection exception.
	 */
	protected function set_method_accessibility( $object, $method_name, $accessible = true ) {
		$reflection_class = new \ReflectionClass( $object );

		$method = $reflection_class->getMethod( $method_name );
		$method->setAccessible( $accessible );

		return $method;
	}
}
