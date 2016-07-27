<?php
use League\FactoryMuffin\FactoryMuffin;

/**
 * @author OnTheGo Systems
 */
abstract class OTGS_TestCase extends PHPUnit_Framework_TestCase {
	protected $current_user_id = 0;
	/**
	 * @var FactoryMuffin
	 */
	protected static $fm;
	protected $options = array();

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

	protected function mock_core_functions() {
		$functions = new OTGS_Mocked_WP_Core_Functions();
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

