<?php
use League\FactoryMuffin\FactoryMuffin;
use tad\FunctionMocker\FunctionMocker;

/**
 * @author OnTheGo Systems
 */
abstract class OTGS_TestCase extends PHPUnit_Framework_TestCase {
	/** @var FactoryMuffin */
	protected static $fm;
	/** @var OTGS_Stubs */
	protected $stubs;

	/** @var OTGS_Mocked_WP_Core_Functions */
	protected $mocked_wp_core_functions;

	public static function setupBeforeClass() {
		$_GET    = array();
		$_POST   = array();

		static::$fm = new FactoryMuffin();
	}

	public static function tearDownAfterClass() {
		static::$fm->deleteSaved();
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

	/**
	 * @return OTGS_Mocked_WP_Core_Functions
	 */
	protected function get_mocked_wp_core_functions() {
		if ( ! $this->mocked_wp_core_functions ) {
			$this->mocked_wp_core_functions = new OTGS_Mocked_WP_Core_Functions( $this );
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
}
