<?php
use League\FactoryMuffin\FactoryMuffin;
use tad\FunctionMocker\FunctionMocker;

/**
 * @author OnTheGo Systems
 */
abstract class OTGS_TestCase extends PHPUnit_Framework_TestCase {
	/**
	 * @var FactoryMuffin
	 */
	protected static $fm;

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
	}

	function tearDown() {
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
			$this->mocked_wp_core_functions = new OTGS_Mocked_WP_Core_Functions();
		}

		return $this->mocked_wp_core_functions;
	}

	protected function mock_all_core_functions() {
		$functions = $this->get_mocked_wp_core_functions();

		$functions->functions();
		$functions->wp_error();
		$functions->post_functions();
		$functions->taxonomy_functions();
		$functions->meta_functions();
		$functions->link_template();
		$functions->plugin();
		$functions->theme();
		$functions->plugins_functions();
		$functions->i10n_functions();
		$functions->formatting_functions();
		$functions->user_functions();
		$functions->option_functions();
		$functions->transient_functions();
		$functions->query();
		$functions->shortcode_functions();
		$functions->nonce();
	}
}

