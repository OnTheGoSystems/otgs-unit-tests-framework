<?php

/**
 * @author OnTheGo Systems
 */
class OTGS_Mocked_WP_Core_Functions {
	private $options         = array();
	private $current_user_id = 0;
	private $current_user;

	public function functions() {
		\WP_Mock::wpFunction( 'wp_json_encode', array(
			'return' => function ( $data, $options = 0, $depth = 512 ) {
				return json_encode( $data, $options, $depth );
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
	}

	public function plugins_functions() {
		\WP_Mock::wpFunction( 'plugin_dir_url', array(
			'return' => function ( $input ) {
				return trailingslashit( plugins_url( '', $input ) );
			},
		) );

		\WP_Mock::wpFunction( 'plugins_url', array(
			'return' => function ( $path = '', $plugin = '' ) {
				return WP_PLUGIN_URL;
			},
		) );
	}

	public function i10n_functions() {
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
	}

	public function formatting_functions() {
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

		\WP_Mock::wpFunction( 'trailingslashit', array(
			'return' => function ( $input ) {
				return untrailingslashit( $input ) . '/';
			},
		) );

		\WP_Mock::wpFunction( 'untrailingslashit', array(
			'return' => function ( $input ) {
				rtrim( $input, '/\\' );
			},
		) );
	}

	public function user_functions() {
		\WP_Mock::wpFunction( 'get_current_user_id', array(
			'return' => $this->current_user_id,
		) );

		\WP_Mock::wpFunction( 'wp_set_current_user', array(
			'return' => function ( $id, $name = '' ) {
				$this->current_user_id = $id;
				if ( $id ) {
					$this->current_user = new WP_User( $id, $name );
				}
			},
		) );

		\WP_Mock::wpFunction( 'wp_get_current_user', array(
			'return' => function () {
				return $this->current_user;
			},
		) );
	}

	public function option_functions() {
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
	}

	public function wp_error() {
		\WP_Mock::wpFunction( 'is_wp_error', array(
			'return' => function ( $thing ) {
				return ( $thing instanceof WP_Error );
			},
		) );
	}

	public function link_template() {
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
	}

	public function plugin() {
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
	}

	public function theme() {
		\WP_Mock::wpFunction( 'wp_get_theme', array(
			'return' => function ( $plugin_file, $markup = true, $translate = true ) {
				return new WP_Theme( $plugin_file, '' );
			},
		) );
	}
}