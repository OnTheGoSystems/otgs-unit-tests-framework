<?php
use League\FactoryMuffin\FactoryMuffin;

/**
 * @author OnTheGo Systems
 */
abstract class OTGS_TestCase extends PHPUnit_Framework_TestCase {
	/**
	 * @var FactoryMuffin
	 */
	protected static $fm;

	protected $mocked_wp_core_functions;

	public static function setupBeforeClass() {
		// create a new factory muffin instance
		static::$fm = new FactoryMuffin();

		// you can customize the save/delete methods
		// new FactoryMuffin(new ModelStore('save', 'delete'));

		// load your model definitions
		//		static::$fm->loadFactories( __DIR__ . '/factories/' );
	}

	public static function tearDownAfterClass() {
		static::$fm->deleteSaved();
	}

	function setUp() {
		parent::setUp();
		\WP_Mock::setUp();
	}

	function tearDown() {
		\WP_Mock::tearDown();
		parent::tearDown();
	}

	protected function get_mocked_wp_core_functions() {
		if ( ! $this->mocked_wp_core_functions ) {
			$this->mocked_wp_core_functions = new OTGS_Mocked_WP_Core_Functions();
		}

		return $this->mocked_wp_core_functions;
	}

	protected function mock_all_core_functions() {
		$functions = $this->get_mocked_wp_core_functions();
		$functions->wp_error();
		$functions->link_template();
		$functions->plugin();
		$functions->theme();
		$functions->plugins_functions();
		$functions->i10n_functions();
		$functions->formatting_functions();
		$functions->user_functions();
		$functions->option_functions();
	}
}

