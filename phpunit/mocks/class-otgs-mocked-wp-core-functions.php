<?php

/**
 * @author OnTheGo Systems
 */
class OTGS_Mocked_WP_Core_Functions {
	public    $posts           = array();
	public    $terms           = array();
	public    $term_taxonomy   = array();
	public    $meta_cache      = array();
	public    $options         = array();
	public    $wp_filter       = array();
	public    $merged_filters  = array();
	public    $cache           = array();
	public    $current_user_id = 0;
	public    $current_user;
	protected $filter_id_count = 0;

	public function functions() {
		\WP_Mock::wpFunction( 'wp_json_encode', array(
			'return' => function ( $data, $options = 0, $depth = 512 ) {
				return json_encode( $data, $options, $depth );
			},
		) );

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

				foreach ( $qs as $k => $v ) {
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

				return $data;
			},
		) );
		\WP_Mock::wpFunction( 'absint', array(
			'return' => function ( $maybeint ) {
				return abs( (int) $maybeint );
			},
		) );
	}

	public function post_functions() {
		$this->meta_functions();

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
	}

	public function taxonomy_functions() {
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
				if ( array_key_exists( $_term_id, $that->terms ) ) {
					$_term = $that->terms[ $_term_id ];
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

				$new_term_id          = count( $that->terms ) + 1;
				$new_term             = new stdClass();
				$new_term->term_id    = $new_term_id;
				$new_term->name       = $args['name'];
				$new_term->slug       = $args['slug'];
				$new_term->term_group = $args['term_group'];

				if ( ! array_key_exists( $taxonomy, $that->term_taxonomy ) ) {
					$that->term_taxonomy[ $taxonomy ] = array();
				}

				$new_term_taxonomy_id = count( $that->term_taxonomy[ $taxonomy ] ) + 1;

				$term_taxonomy              = new stdClass();
				$term_taxonomy->term_id     = $new_term_id;
				$term_taxonomy->taxonomy    = $taxonomy;
				$term_taxonomy->description = $args['description'];
				$term_taxonomy->parent      = (int) $args['parent'];

				$that->term_taxonomy[ $taxonomy ][ $new_term_taxonomy_id ] = $term_taxonomy;

				$new_term->term_taxonomy_id  = $new_term_taxonomy_id;
				$new_term->taxonomy          = $taxonomy;
				$that->terms[ $new_term_id ] = $new_term;

				return array( $new_term_id, $new_term_taxonomy_id );
			},
		) );
	}

	public function meta_functions() {
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
				if ( array_key_exists( $meta_type, $that->meta_cache ) ) {
					$meta_cache = $that->meta_cache[ $meta_type ];
				}

				if ( isset( $meta_cache[ $object_id ][ $meta_key ] ) ) {
					if ( $single ) {
						return maybe_unserialize( $meta_cache[ $object_id ][ $meta_key ][0] );
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
			'return' => function ( $meta_type, $object_id, $meta_key, $meta_value, $prev_value = '' ) use ( $that ) {
				if ( ! $meta_type || ! $meta_key || ! is_numeric( $object_id ) ) {
					return false;
				}

				$object_id = absint( $object_id );
				if ( ! $object_id ) {
					return false;
				}
				$meta_value = maybe_serialize( $meta_value );

				$that->meta_cache[ $meta_type ][ $object_id ][ $meta_key ][] = $meta_value;

				return true;
			},
		) );
	}

	public function plugins_functions() {
		$that = $this;

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

		\WP_Mock::wpFunction( 'add_action', array(
			'return' => function ( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
				return add_filter( $tag, $function_to_add, $priority, $accepted_args );
			},
		) );

		\WP_Mock::wpFunction( 'add_filter', array(
			'return' => function ( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) use ( $that ) {
				$idx                                          = _wp_filter_build_unique_id( $tag, $function_to_add, $priority );
				$that->wp_filter[ $tag ][ $priority ][ $idx ] = array( 'function' => $function_to_add, 'accepted_args' => $accepted_args );
				unset( $that->merged_filters[ $tag ] );

				return true;
			},
		) );

		\WP_Mock::wpFunction( 'remove_action', array(
			'return' => function ( $tag, $function_to_remove, $priority = 10 ) {
				return remove_filter( $tag, $function_to_remove, $priority );
			},
		) );

		\WP_Mock::wpFunction( 'remove_filter', array(
			'return' => function ( $tag, $function_to_remove, $priority = 10 ) use ( $that ) {
				$function_to_remove = _wp_filter_build_unique_id( $tag, $function_to_remove, $priority );

				$r = isset( $that->wp_filter[ $tag ][ $priority ][ $function_to_remove ] );

				if ( true === $r ) {
					unset( $that->wp_filter[ $tag ][ $priority ][ $function_to_remove ] );
					if ( empty( $that->wp_filter[ $tag ][ $priority ] ) ) {
						unset( $that->wp_filter[ $tag ][ $priority ] );
					}
					if ( empty( $that->wp_filter[ $tag ] ) ) {
						$that->wp_filter[ $tag ] = array();
					}
					unset( $that->merged_filters[ $tag ] );
				}

				return $r;
			},
		) );

		\WP_Mock::wpFunction( '_wp_filter_build_unique_id', array(
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
				return rtrim( $input, '/\\' );
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

		\WP_Mock::wpFunction( 'get_permalink', array(
			'return' => function ( $id = 0, $leavename = false ) {
				$post = get_post( $id );

				return WPML_TESTS_SITE_URL . '/' . $post->post_title;
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

	public function query() {
		\WP_Mock::wpFunction( 'is_attachment', array(
			'return' => function() {
				return isset( $_SERVER['is_attachment'] ) ? (bool) $_SERVER['is_attachment'] : false;
			},
		) );
	}
}