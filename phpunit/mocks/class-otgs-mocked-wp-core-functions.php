<?php

/**
 * @author OnTheGo Systems
 */
class OTGS_Mocked_WP_Core_Functions {
	private $posts           = array();
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

	public function post_functions() {
		$that = $this;
		\WP_Mock::wpFunction( 'get_post', array(
			'return' => /**
			 * @param int|array|stdClass|null $post
			 * @param string                  $output
			 * @param string                  $filter
			 *
			 * @return array|mixed|null|stdClass
			 */
				function ( $post = null, $output = OBJECT, $filter = 'raw' ) use ( $that ) {
					if ( ! $post && array_key_exists( 'post', $GLOBALS ) ) {
						$post = $GLOBALS['post'];
					}

					$_post = null;

					$post_id = null;
					if ( is_object( $post ) && isset( $post->ID ) ) {
						$post_id = $post->ID;
					} elseif ( $post && is_array( $post ) ) {
						$_post = new stdClass();
						/**
						 * @var array $post
						 */
						foreach ( $post as $key => $value ) {
							// Add the value to the object
							if ( in_array( $key, array( 'id', 'ID', 'post_id' ), true ) ) {
								$_post->ID = $value;
							} else {
								$_post->{$key} = $value;
							}
						}
						$post_id = $post->ID;
					} else {
						$post_id = $post;
					}

					if ( array_key_exists( $post_id, $that->posts ) ) {
						$_post = $that->posts[ $post_id ];
					}

					if ( ! $_post ) {
						return null;
					}

					if ( ARRAY_A === $output ) {
						return get_object_vars( $_post );
					} elseif ( ARRAY_N === $output ) {
						return array_values( get_object_vars( $_post ) );
					}

					return $_post;
				},
		) );

		\WP_Mock::wpFunction( 'wp_insert_post', array(
			'return' => function ( $postarr, $wp_error = false ) use ( $that ) {

				if ( ! empty( $postarr['ID'] ) ) {
					$post_ID     = $postarr['ID'];
					$post_before = get_post( $post_ID );
					if ( is_null( $post_before ) ) {
						if ( $wp_error ) {
							$error                           = new stdClass();
							$error->errors['invalid_post'][] = 'Invalid post ID.';

							return $error;
						}

						return 0;
					}
				}

				$_post = new stdClass();
				foreach ( $postarr as $key => $value ) {
					// Add the value to the object
					if ( in_array( $key, array( 'id', 'ID', 'post_id' ), true ) ) {
						$_post->ID = $value;
					} else {
						$_post->{$key} = $value;
					}
				}

				if ( ! isset( $_post->ID ) || ! $_post->ID ) {
					$new_id = 1;
					if ( $that->posts ) {
						$ids    = array_keys( $that->posts );
						$new_id = max( $ids ) + 1;
					}
					$_post->ID = $new_id;
				}

				$that->posts[ $_post->ID ] = $_post;

				return $_post->ID;
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

		\WP_Mock::wpFunction( 'sanitize_text_field', array(
			'return' => function ( $input ) {
				return $input;
			},
		) );
	}

	public function user_functions() {
		$that = $this;

		\WP_Mock::wpFunction( 'get_current_user_id', array(
			'return' => $this->current_user_id,
		) );

		\WP_Mock::wpFunction( 'wp_set_current_user', array(
			'return' => function ( $id, $name = '' ) use ( $that ) {
				$that->current_user_id = $id;
				if ( $id ) {
					$that->current_user = new WP_User( $id, $name );
				}
			},
		) );

		\WP_Mock::wpFunction( 'wp_get_current_user', array(
			'return' => function () use ( $that ) {
				return $that->current_user;
			},
		) );

		\WP_Mock::wpFunction( 'wp_send_json_success', array(
			'return' => function ( $data = null ) {
				$response = array( 'success' => true );

				if ( isset( $data ) ) {
					$response['data'] = $data;
				}

				return $response;
			},
		) );

		\WP_Mock::wpFunction( 'wp_send_json_error', array(
			'return' => function ( $data = null ) {
				$response = array( 'success' => false );

				if ( isset( $data ) ) {
					if ( is_wp_error( $data ) ) {
						/** @var WP_Error $data */
						$result = array();
						foreach ( $data->errors as $code => $messages ) {
							/** @var array $messages */
							foreach ( $messages as $message ) {
								$result[] = array( 'code' => $code, 'message' => $message );
							}
						}

						$response['data'] = $result;
					} else {
						$response['data'] = $data;
					}
				}

				return $response;
			},
		) );
	}

	public function option_functions() {
		$that = $this;

		\WP_Mock::wpFunction( 'get_option', array(
			'return' => function ( $option, $default_value = false ) use ( $that ) {
				if ( array_key_exists( $option, $that->options ) ) {
					return $that->options[ $option ];
				} else {
					return $default_value;
				}
			},
		) );

		\WP_Mock::wpFunction( 'update_option', array(
			'return' => function ( $option, $value, $autoload = null ) use ( $that ) {
				$that->options[ $option ] = $value;
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