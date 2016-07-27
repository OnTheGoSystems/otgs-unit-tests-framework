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

		$this->mock_functions();

	}

	function tearDown() {
		\WP_Mock::tearDown();
		parent::tearDown();
	}

	private function mock_functions() {
		\WP_Mock::wpFunction( 'esc_html__', array(
			'return' => function ( $input, $context ) {
				return __( $input, $context );
			},
		) );

		\WP_Mock::wpFunction( '__', array(
			'return' => function ( $input, $context ) {
				return $context . '|||' . $input;
			},
		) );

		\WP_Mock::wpFunction( 'esc_attr', array(
			'return' => function ( $input ) {
				return $input;
			},
		) );

		\WP_Mock::wpFunction( 'esc_url_raw', array(
			'return' => function ( $input ) {
				return $input;
			},
		) );

		\WP_Mock::wpFunction( 'esc_html', array(
			'return' => function ( $input ) {
				return $input;
			},
		) );

		\WP_Mock::wpFunction( 'get_current_user_id', array(
			'return' => $this->current_user_id,
		) );

		\WP_Mock::wpFunction( 'get_option', array(
			'return' => function ( $option, $default_value = false ) {
				if ( array_key_exists( $option, $this->options ) ) {
					return $this->options[ $option ];
				} else {
					return $default_value;
				}
			},
		) );

		\WP_Mock::wpFunction( 'update_option', array(
			'return' => function ( $option, $value, $autoload = null ) {
				$this->options[ $option ] = $value;
			},
		) );

		\WP_Mock::wpFunction( 'wp_json_encode', array(
			'return' => function ( $data, $options = 0, $depth = 512 ) {
				return json_encode( $data, $options, $depth );
			},
		) );

		\WP_Mock::wpFunction( 'is_wp_error', array(
			'return' => function ( $thing ) {
				return ( $thing instanceof WP_Error );
			},
		) );

		\WP_Mock::wpFunction( 'untrailingslashit', array(
			'return' => function ( $input ) {
				rtrim( $input, '/\\' );
			},
		) );

		\WP_Mock::wpFunction( 'plugins_url', array(
			'return' => function ( $path = '', $plugin = '' ) {
				return $path;
			},
		) );

		\WP_Mock::wpFunction( 'admin_url', array(
			'return' => function ( $path = '', $scheme = 'admin' ) {
				return $path . '/' . $scheme;
			},
		) );

		\WP_Mock::wpFunction( 'add_query_arg', array(
			'return' => function ( $path = '', $scheme = 'admin' ) {
				$args = func_get_args();
				if ( is_array( $args[0] ) ) {
					if ( count( $args ) < 2 || false === $args[1] ) {
						$uri = $_SERVER['REQUEST_URI'];
					} else {
						$uri = $args[1];
					}
				} else {
					if ( count( $args ) < 3 || false === $args[2] ) {
						$uri = $_SERVER['REQUEST_URI'];
					} else {
						$uri = $args[2];
					}
				}

				return $uri;
			},
		) );

		\WP_Mock::wpFunction( 'get_plugin_data', array(
			'return' => function ( $plugin_file, $markup = true, $translate = true ) {
				return array(
					'Name'        => 'Plugin Name: ' . $plugin_file,
					'PluginURI'   => 'Plugin URI: ' . $plugin_file,
					'Version'     => 'Version: ' . $plugin_file,
					'Description' => 'Description: ' . $plugin_file,
					'Author'      => 'Author: ' . $plugin_file,
					'AuthorURI'   => 'Author URI: ' . $plugin_file,
					'TextDomain'  => 'Text Domain: ' . $plugin_file,
					'DomainPath'  => 'Domain Path: ' . $plugin_file,
					'Network'     => 'Network: ' . $plugin_file,
				);
			},
		) );

		\WP_Mock::wpFunction( 'wp_get_theme', array(
			'return' => function ( $plugin_file, $markup = true, $translate = true ) {
				return new WP_Theme( $plugin_file, '' );
			},
		) );
	}
}
