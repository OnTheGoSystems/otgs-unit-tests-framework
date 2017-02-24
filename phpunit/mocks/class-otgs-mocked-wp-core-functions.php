<?php

/**
 * @author OnTheGo Systems
 */
class OTGS_Mocked_WP_Core_Functions {
	private   $caller;
	protected $filter_id_count     = 0;
	public    $data_posts          = array();
	public    $data_terms          = array();
	public    $data_term_taxonomy  = array();
	public    $data_meta_cache     = array();
	public    $data_options        = array();
	public    $data_wp_filter      = array();
	public    $data_merged_filters = array();
	public    $data_cache          = array();
	public    $current_user_id     = 0;
	public    $current_user;
	public    $is_admin            = false;
	public    $is_multisite        = false;
	public    $is_network_admin    = false;
	public    $shortcode_tags      = array();

	/**
	 * OTGS_Mocked_WP_Core_Functions constructor.
	 *
	 * @param $caller
	 */
	public function __construct( OTGS_TestCase $caller ) {
		$this->caller = $caller;
	}

	public function functions() {
		$that = $this;

		\WP_Mock::wpFunction( 'add_query_arg', array(
			'return' => function () {
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

				if ( $frag = strstr( $uri, '#' ) ) {
					$uri = substr( $uri, 0, - strlen( $frag ) );
				} else {
					$frag = '';
				}

				if ( 0 === stripos( $uri, 'http://' ) ) {
					$protocol = 'http://';
					$uri      = substr( $uri, 7 );
				} elseif ( 0 === stripos( $uri, 'https://' ) ) {
					$protocol = 'https://';
					$uri      = substr( $uri, 8 );
				} else {
					$protocol = '';
				}

				if ( strpos( $uri, '?' ) !== false ) {
					list( $base, $query ) = explode( '?', $uri, 2 );
					$base .= '?';
				} elseif ( $protocol || strpos( $uri, '=' ) === false ) {
					$base  = $uri . '?';
					$query = '';
				} else {
					$base  = '';
					$query = $uri;
				}

				parse_str( $query, $qs );

				if ( is_array( $args[0] ) ) {
					foreach ( $args[0] as $k => $v ) {
						$qs[ $k ] = $v;
					}
				} else {
					$qs[ $args[0] ] = $args[1];
				}

				foreach ( (array) $qs as $k => $v ) {
					if ( $v === false ) {
						unset( $qs[ $k ] );
					}
				}

				$ret = http_build_query( $qs );
				$ret = trim( $ret, '?' );
				$ret = preg_replace( '#=(&|$)#', '$1', $ret );
				$ret = $protocol . $base . $ret . $frag;
				$ret = rtrim( $ret, '?' );

				return $ret;
			},
		) );
		\WP_Mock::wpFunction( 'maybe_unserialize', array(
			'return' => function ( $original ) {
				$unserialized = @unserialize( $original );
				if ( $unserialized ) {
					return $unserialized;
				}

				return $original;
			},
		) );
		\WP_Mock::wpFunction( 'maybe_serialize', array(
			'return' => function ( $data ) {
				if ( is_array( $data ) || is_object( $data ) ) {
					return serialize( $data );
				}

				// As per comment in WP.
				// Double serialization is required for backward compatibility.
				// See https://core.trac.wordpress.org/ticket/12930
				// Also the world will end. See WP 3.6.1.
				if ( is_serialized( $data ) ) {
					return serialize( $data );
				}

				return $data;
			},
		) );
		\WP_Mock::wpFunction( 'absint', array(
			'return' => function ( $maybeint ) {
				return abs( (int) $maybeint );
			},
		) );
		\WP_Mock::wpFunction( 'is_admin', array(
			'return' => function () use ( $that ) {
				return (bool) $that->is_admin;
			},
		) );
		\WP_Mock::wpFunction( 'is_multisite', array(
			'return' => function () use ( $that ) {
				return (bool) $that->is_multisite;
			},
		) );
		\WP_Mock::wpFunction( 'is_network_admin', array(
			'return' => function () use ( $that ) {
				return (bool) $that->is_network_admin;
			},
		) );
		\WP_Mock::wpFunction( 'is_serialized', array(
			'return' => function ( $data ) {
				$array = @unserialize( $data );

				return ! ( $array === false && $data !== 'b:0;' );
			},
		) );

		\WP_Mock::wpFunction( 'wp_json_encode', array(
			'return' => function ( $data, $options = 0, $depth = 512 ) {
				return json_encode( $data, $options, $depth );
			},
		) );

		\WP_Mock::wpFunction( 'wp_send_json', array(
			'return' => function ( $response = null ) {
				echo wp_json_encode( $response );
			},
		) );
		\WP_Mock::wpFunction( 'wp_send_json_success', array(
			'return' => function ( $data = null ) {
				$response = array( 'success' => true );

				if ( $data ) {
					$response['data'] = $data;
				}

				return $response;
			},
		) );
		\WP_Mock::wpFunction( 'wp_send_json_error', array(
			'return' => function ( $data = null ) {
				$response = array( 'success' => false );

				if ( $data ) {
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

	public function post() {
		$this->meta();

		$that = $this;
		\WP_Mock::wpFunction( 'get_post', array(
			'return' => /**
			 * @param int|array|stdClass|null $post
			 * @param string                  $output
			 * @param string                  $filter
			 *
			 * @return array|mixed|null|stdClass
			 */
				function ( $post = null, $output = OBJECT ) use ( $that ) {
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

					if ( array_key_exists( $post_id, $that->data_posts ) ) {
						$_post = $that->data_posts[ $post_id ];
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
					if ( ! $post_before ) {
						if ( $wp_error ) {
							$error                           = new stdClass();
							$error->errors['invalid_post'][] = 'Invalid post ID.';

							return $error;
						}

						return 0;
					}
				}

				$_post = new stdClass();
				foreach ( (array) $postarr as $key => $value ) {
					// Add the value to the object
					if ( in_array( $key, array( 'id', 'ID', 'post_id' ), true ) ) {
						$_post->ID = $value;
					} else {
						$_post->{$key} = $value;
					}
				}

				if ( ! isset( $_post->ID ) || ! $_post->ID ) {
					$new_id = 1;
					if ( $that->data_posts ) {
						$ids    = array_keys( $that->data_posts );
						$new_id = max( $ids ) + 1;
					}
					$_post->ID = $new_id;
				}

				$that->data_posts[ $_post->ID ] = $_post;

				return $_post->ID;
			},
		) );

		\WP_Mock::wpFunction( 'get_post_meta', array(
			'return' => function ( $post_id, $key = '', $single = false ) {
				return get_metadata( 'post', $post_id, $key, $single );
			},
		) );
		\WP_Mock::wpFunction( 'update_post_meta', array(
			'return' => function ( $post_id, $meta_key, $meta_value, $prev_value = '' ) {
				return update_metadata( 'post', $post_id, $meta_key, $meta_value, $prev_value );
			},
		) );

		\WP_Mock::wpFunction( 'add_post_meta', array(
			'return' => function ( $post_id, $meta_key, $meta_value ) {
				return add_metadata( 'post', $post_id, $meta_key, $meta_value, $unique = false );
			},
		) );

		\WP_Mock::wpFunction( 'delete_post_meta', array(
			'return' => function ( $post_id, $meta_key, $meta_value ) {
				return delete_metadata( 'post', $post_id, $meta_key, $meta_value );
			},
		) );
	}

	public function taxonomy() {
		$that = $this;
		\WP_Mock::wpFunction( 'get_term', array(
			'return' => function ( $term, $taxonomy = '', $output = OBJECT, $filter = 'raw' ) use ( $that ) {
				$_term_id = null;

				if ( $term ) {
					if ( is_numeric( $term ) ) {
						$_term_id = $term;
					}
					if ( is_object( $term ) ) {
						$_term_id = $term->term_id;
					}
				}

				if ( ! $_term_id ) {
					return null;
				}

				$_term = null;
				if ( array_key_exists( $_term_id, $that->data_terms ) ) {
					$_term = $that->data_terms[ $_term_id ];
				}

				if ( ARRAY_A === $output ) {
					return get_object_vars( $_term );
				} elseif ( ARRAY_N === $output ) {
					return array_values( get_object_vars( $_term->to_array() ) );
				}

				return $_term;
			},
		) );

		\WP_Mock::wpFunction( 'wp_insert_term', array(
			'return' => function ( $term, $taxonomy, $args = array() ) use ( $that ) {
				$defaults = array( 'alias_of' => '', 'description' => '', 'parent' => 0, 'slug' => '', 'term_group' => null );

				$args = array_merge( $defaults, $args );

				$args['name']        = $term;
				$args['taxonomy']    = $taxonomy;
				$args['description'] = (string) $args['description'];

				$new_term_id = count( $that->data_terms ) + 1;
				$new_term             = new stdClass();
				$new_term->term_id    = $new_term_id;
				$new_term->name       = $args['name'];
				$new_term->slug       = $args['slug'];
				$new_term->term_group = $args['term_group'];

				if ( ! array_key_exists( $taxonomy, $that->data_term_taxonomy ) ) {
					$that->data_term_taxonomy[ $taxonomy ] = array();
				}

				$new_term_taxonomy_id = count( $that->data_term_taxonomy[ $taxonomy ] ) + 1;

				$term_taxonomy              = new stdClass();
				$term_taxonomy->term_id     = $new_term_id;
				$term_taxonomy->taxonomy    = $taxonomy;
				$term_taxonomy->description = $args['description'];
				$term_taxonomy->parent      = (int) $args['parent'];

				$that->data_term_taxonomy[ $taxonomy ][ $new_term_taxonomy_id ] = $term_taxonomy;

				$new_term->term_taxonomy_id  = $new_term_taxonomy_id;
				$new_term->taxonomy          = $taxonomy;
				$that->data_terms[ $new_term_id ]                               = $new_term;

				return array( $new_term_id, $new_term_taxonomy_id );
			},
		) );
	}

	public function meta() {
		$this->functions();

		$that = $this;

		\WP_Mock::wpFunction( 'get_metadata', array(
			'return' => function ( $meta_type, $object_id, $meta_key = '', $single = false ) use ( $that ) {
				if ( ! $meta_type || ! is_numeric( $object_id ) ) {
					return false;
				}

				$object_id = absint( $object_id );
				if ( ! $object_id ) {
					return false;
				}

				$meta_cache = null;
				if ( array_key_exists( $meta_type, $that->data_meta_cache ) ) {
					$meta_cache = $that->data_meta_cache[ $meta_type ];
				}

				if ( isset( $meta_cache[ $object_id ][ $meta_key ] ) ) {
					if ( $single ) {
						return maybe_unserialize( array_pop( $meta_cache[ $object_id ][ $meta_key ] ) );
					} else {
						return array_map( 'maybe_unserialize', $meta_cache[ $object_id ][ $meta_key ] );
					}
				}

				if ( $single ) {
					return '';
				} else {
					return array();
				}
			},
		) );
		\WP_Mock::wpFunction( 'update_metadata', array(
			'return' => function ( $meta_type, $object_id, $meta_key, $meta_value ) use ( $that ) {
				if ( ! $meta_type || ! $meta_key || ! is_numeric( $object_id ) ) {
					return false;
				}

				$object_id = absint( $object_id );
				if ( ! $object_id ) {
					return false;
				}
				$meta_value = maybe_serialize( $meta_value );

				$that->data_meta_cache[ $meta_type ][ $object_id ][ $meta_key ][] = $meta_value;

				return true;
			},
		) );


		\WP_Mock::wpFunction( 'add_metadata', array(
			'return' => function ( $meta_type, $object_id, $meta_key, $meta_value, $unique = false ) use ( $that ) {
				if ( ! $meta_type || ! $meta_key || ! is_numeric( $object_id ) ) {
					return false;
				}

				$object_id = absint( $object_id );
				if ( ! $object_id ) {
					return false;
				}

				$meta_value = maybe_serialize( $meta_value );

				if ( $unique ) {
					$that->data_meta_cache[ $meta_type ][ $object_id ][ $meta_key ] = array( $meta_value );
				} else {
					$that->data_meta_cache[ $meta_type ][ $object_id ][ $meta_key ][] = $meta_value;
				}

				return true;
			},
		) );

		\WP_Mock::wpFunction( 'delete_metadata', array(
			'return' => function ( $meta_type, $object_id, $meta_key, $meta_value ) use ( $that ) {
				if ( ! $meta_type || ! $meta_key || ! is_numeric( $object_id ) ) {
					return false;
				}

				$object_id = absint( $object_id );
				if ( ! $object_id ) {
					return false;
				}

				$meta_value = maybe_serialize( $meta_value );

				$index = array_search( $meta_value, $that->data_meta_cache[ $meta_type ][ $object_id ][ $meta_key ], true );
				unset( $that->data_meta_cache[ $meta_type ][ $object_id ][ $meta_key ][ $index ] );

				return true;
			},
		) );
	}

	public function i10n() {
		\WP_Mock::wpPassthruFunction( 'esc_html__' );
		\WP_Mock::wpPassthruFunction( 'esc_attr__' );
		\WP_Mock::wpPassthruFunction( 'esc_html_x' );
		\WP_Mock::wpPassthruFunction( 'esc_attr_x' );
		\WP_Mock::wpPassthruFunction( 'esc_html_e' );
		\WP_Mock::wpPassthruFunction( 'esc_attr_e' );

		\WP_Mock::wpPassthruFunction( '_c' );
		\WP_Mock::wpPassthruFunction( '__' );
		\WP_Mock::wpPassthruFunction( '_x' );
		\WP_Mock::wpPassthruFunction( '_n' );
	}

	public function formatting() {
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		\WP_Mock::wpPassthruFunction( 'esc_url_raw' );
		\WP_Mock::wpPassthruFunction( 'esc_html' );
		\WP_Mock::wpPassthruFunction( 'esc_sql' );
		\WP_Mock::wpPassthruFunction( 'sanitize_text_field' );

		\WP_Mock::wpFunction( 'trailingslashit', array(
			'return' => function ( $input ) {
				return untrailingslashit( $input ) . '/';
			},
		) );

		\WP_Mock::wpFunction( 'untrailingslashit', array(
			'return' => function ( $input ) {
				return rtrim( $input, '/\\' );
			},
		) );
	}

	public function user() {
		$that = $this;

		$this->functions();
		$this->wp_error();

		\WP_Mock::wpFunction( 'get_current_user_id', array(
			'return' => function () use ( $that ) {
				return $that->current_user_id;
			},
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

		//@todo This has been added for backward compatibility: remove after the next major version
		$this->functions();
	}

	public function option() {
		$that = $this;

		\WP_Mock::wpFunction( 'get_option', array(
			'return' => function ( $option, $default_value = false ) use ( $that ) {
				if ( array_key_exists( $option, $that->data_options ) ) {
					return $that->data_options[ $option ];
				} else {
					return $default_value;
				}
			},
		) );

		\WP_Mock::wpFunction( 'update_option', array(
			'return' => function ( $option, $value ) use ( $that ) {
				$that->data_options[ $option ] = $value;
			},
		) );

		\WP_Mock::wpFunction( 'delete_option', array(
			'return' => function ( $option ) use ( $that ) {
				if ( array_key_exists( $option, $that->data_options ) ) {
					unset( $that->data_options[ $option ] );
					return true;
				}
				return false;
			},
		) );

		\WP_Mock::wpFunction(
			'set_transient',
			array(
				'return' => function ( $key, $value ) use ( $that ) {
					update_option( $key, $value );
				},
			)
		);

		\WP_Mock::wpFunction(
			'get_transient',
			array(
				'return' => function ( $key ) use ( $that ) {
					return get_option( $key );
				},
			)
		);

		\WP_Mock::wpFunction(
			'delete_transient',
			array(
				'return' => function ( $key ) use ( $that ) {
					delete_option( $key );
				},
			)
		);
	}

	public function wp_error() {
		\WP_Mock::wpFunction( 'is_wp_error', array(
			'return' => function ( $thing ) {
				return ( $thing instanceof WP_Error );
			},
		) );
	}

	public function link_template() {
		$this->post();

		\WP_Mock::wpFunction( 'plugins_url', array(
			'return' => function ( $path = '' ) {
				return $path;
			},
		) );

		\WP_Mock::wpFunction( 'admin_url', array(
			'return' => function ( $path = '', $scheme = 'admin' ) {
				return $path . '/' . $scheme;
			},
		) );

		\WP_Mock::wpFunction( 'get_permalink', array(
			'return' => function ( $id = 0 ) {
				$post = get_post( $id );

				return WPML_TESTS_SITE_URL . '/' . $post->post_title;
			},
		) );
	}

	public function plugin() {
		\WP_Mock::wpFunction( 'get_plugin_data', array(
			'return' => function ( $plugin_file ) {
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

		$that = $this;

		if ( ! defined( 'WP_PLUGIN_URL' ) ) {
			define( 'WP_PLUGIN_URL', '' );
		}

		\WP_Mock::wpFunction(
			'plugin_dir_url',
			array(
				'return' => function ( $input ) {
					return trailingslashit( plugins_url( '', $input ) );
				},
			)
		);

		\WP_Mock::wpFunction(
			'plugins_url',
			array(
				'return' => function () {
					return WP_PLUGIN_URL;
				},
			)
		);

		\WP_Mock::wpFunction(
			'add_action',
			array(
				'return' => function ( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
					return add_filter( $tag, $function_to_add, $priority, $accepted_args );
				},
			)
		);

		\WP_Mock::wpFunction(
			'add_filter',
			array(
				'return' => function ( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) use ( $that ) {
					$idx                                               = _wp_filter_build_unique_id( $tag, $function_to_add, $priority );
					$that->data_wp_filter[ $tag ][ $priority ][ $idx ] = array( 'function' => $function_to_add, 'accepted_args' => $accepted_args );
					unset( $that->data_merged_filters[ $tag ] );

					return true;
				},
			)
		);

		\WP_Mock::wpFunction(
			'remove_action',
			array(
				'return' => function ( $tag, $function_to_remove, $priority = 10 ) {
					return remove_filter( $tag, $function_to_remove, $priority );
				},
			)
		);

		\WP_Mock::wpFunction(
			'remove_filter',
			array(
				'return' => function ( $tag, $function_to_remove, $priority = 10 ) use ( $that ) {
					$function_to_remove = _wp_filter_build_unique_id( $tag, $function_to_remove, $priority );

					$r = isset( $that->data_wp_filter[ $tag ][ $priority ][ $function_to_remove ] );

					if ( true === $r ) {
						unset( $that->data_wp_filter[ $tag ][ $priority ][ $function_to_remove ] );
						if ( empty( $that->data_wp_filter[ $tag ][ $priority ] ) ) {
							unset( $that->data_wp_filter[ $tag ][ $priority ] );
						}
						if ( empty( $that->data_wp_filter[ $tag ] ) ) {
							$that->data_wp_filter[ $tag ] = array();
						}
						unset( $that->data_merged_filters[ $tag ] );
					}

					return $r;
				},
			)
		);

		\WP_Mock::wpFunction(
			'_wp_filter_build_unique_id',
			array(
				'return' => function ( $tag, $function, $priority ) use ( $that ) {
					if ( is_string( $function ) ) {
						return $function;
					}

					$unique_id = null;

					if ( is_object( $function ) ) {
						// Closures are currently implemented as objects
						$function = array( $function, '' );
					} else {
						$function = (array) $function;
					}

					if ( is_object( $function[0] ) ) {
						// Object Class Calling
						if ( function_exists( 'spl_object_hash' ) ) {
							$unique_id = spl_object_hash( $function[0] ) . $function[1];
						} else {
							$obj_idx = get_class( $function[0] ) . $function[1];
							if ( ! isset( $function[0]->wp_filter_id ) ) {
								if ( false === $priority ) {
									return false;
								}
								$obj_idx .= isset( $wp_filter[ $tag ][ $priority ] ) ? count( (array) $wp_filter[ $tag ][ $priority ] ) : $that->filter_id_count;
								$function[0]->wp_filter_id = $that->filter_id_count;
								++ $that->filter_id_count;
							} else {
								$obj_idx .= $function[0]->wp_filter_id;
							}

							$unique_id = $obj_idx;
						}
					} elseif ( is_string( $function[0] ) ) {
						// Static Calling
						$unique_id = $function[0] . '::' . $function[1];
					}

					return $unique_id;
				},
			)
		);
	}

	public function theme() {
		$caller = $this->caller;

		\WP_Mock::wpFunction( 'wp_get_theme', array(
			'return' => function ( $plugin_file ) use ( $caller ) {
				$wp_theme = $caller->get_wp_theme_stub();
				$wp_theme->method( 'get' )->with( 'Name' )->willReturn( $plugin_file );

				return $wp_theme;
			}
		) );
	}

	public function query() {
		\WP_Mock::wpFunction( 'is_attachment', array(
			'return' => function() {
				return isset( $_SERVER['is_attachment'] ) ? (bool) $_SERVER['is_attachment'] : false;
			},
		) );
	}

	public function shortcodes() {
		$that = $this;
		\WP_Mock::wpFunction( 'add_shortcode', array(
			'return' => function ( $tag, $function ) use ( $that ) {
				$that->shortcode_tags[ $tag ] = $function;
			},
		) );
	}

	public function pluggable() {
		\WP_Mock::wpFunction( 'wp_create_nonce', array(
			'return' => function ( $action ) {
				return md5( 'nonce' . $action );
			},
		) );

		\WP_Mock::wpFunction( 'wp_verify_nonce', array(
			'return' => function ( $nonce, $action ) {
				return $nonce === wp_create_nonce( $action );
			},
		) );
	}

	/**
	 * @deprecated Use `\OTGS_Mocked_WP_Core_Functions::post` instead
	 */
	public function post_functions() {
		$this->post();
	}

	/**
	 * @deprecated Use `\OTGS_Mocked_WP_Core_Functions::taxonomu` instead
	 */
	public function taxonomy_functions() {
		$this->taxonomy();
	}

	/**
	 * @deprecated Use `\OTGS_Mocked_WP_Core_Functions::meta` instead
	 */
	public function meta_functions() {
		$this->meta();
	}

	/**
	 * @deprecated Use `\OTGS_Mocked_WP_Core_Functions::i10n` instead
	 */
	public function i10n_functions() {
		$this->i10n();
	}

	/**
	 * @deprecated Use `\OTGS_Mocked_WP_Core_Functions::formatting` instead
	 */
	public function formatting_functions() {
		$this->formatting();
	}

	/**
	 * @deprecated Use `\OTGS_Mocked_WP_Core_Functions::user` instead
	 */
	public function user_functions() {
		$this->user();
	}

	/**
	 * @deprecated Use `\OTGS_Mocked_WP_Core_Functions::option` instead
	 */
	public function option_functions() {
		$this->option();
	}

	/**
	 * @deprecated Use `\OTGS_Mocked_WP_Core_Functions::options` instead
	 */
	public function transient_functions() {
		$this->option();
	}

	/**
	 * @deprecated Use `\OTGS_Mocked_WP_Core_Functions::plugin` instead
	 */
	public function plugins_functions() {
		$this->plugin();
	}

	/**
	 * @deprecated Use `\OTGS_Mocked_WP_Core_Functions::shortcodes` instead
	 */
	public function shortcode_functions() {
		$this->shortcodes();
	}

	/**
	 * @deprecated Use `\OTGS_Mocked_WP_Core_Functions::pluggable` instead
	 */
	public function nonce() {
		$this->pluggable();
	}
}
