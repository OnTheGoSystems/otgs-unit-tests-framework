<?php
use League\FactoryMuffin\FactoryMuffin;

/**
 * @author OnTheGo Systems
 */
class OTGS_TestCase extends PHPUnit_Framework_TestCase {
	protected $current_user_id = 0;
	/**
	 * @var FactoryMuffin
	 */
	protected static $fm;
	private          $options = array();

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

		\WP_Mock::wpFunction( 'esc_html__', array(
			'args'   => array( '*', '*' ),
			'return' => function ( $input, $context ) {
				return __( $input, $context );
			},
		) );

		\WP_Mock::wpFunction( '__', array(
			'args'   => array( '*', '*' ),
			'return' => function ( $input, $context ) {
				return $context . '|||' . $input;
			},
		) );

		\WP_Mock::wpFunction( 'esc_attr', array(
			'args'   => array( '*' ),
			'return' => function ( $input ) {
				return $input;
			},
		) );

		\WP_Mock::wpFunction( 'esc_url_raw', array(
			'args'   => array( '*' ),
			'return' => function ( $input ) {
				return $input;
			},
		) );

		\WP_Mock::wpFunction( 'esc_html', array(
			'args'   => array( '*' ),
			'return' => function ( $input ) {
				return $input;
			},
		) );

		\WP_Mock::wpFunction( 'get_current_user_id', array(
			'return' => $this->current_user_id,
		) );

		\WP_Mock::wpFunction( 'get_option', array(
			'args'   => array( '*', '*' ),
			'return' => function ( $option, $default_value = false ) {
				if ( array_key_exists( $option, $this->options ) ) {
					return $this->options[ $option ];
				} else {
					return $default_value;
				}
			},
		) );

		\WP_Mock::wpFunction( 'update_option', array(
			'args'   => array( '*', '*', '*' ),
			'return' => function ( $option, $value, $autoload = null ) {
				$this->options[ $option ] = $value;
			},
		) );

		\WP_Mock::wpFunction( 'wp_json_encode', array(
			'args'   => '*',
			'return' => function ( $data ) {
				return json_encode( $data );
			},
		) );
	}

	function tearDown() {
		\WP_Mock::tearDown();
		parent::tearDown();
	}
}
