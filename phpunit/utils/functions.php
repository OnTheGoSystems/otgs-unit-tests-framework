<?php
/**
 * @author OnTheGo Systems
 */
// misc help functions and utilities

/**
 * Passing to that function an array of arrays, it will build another array with all possible combination of values.
 * This function might be useful when providing data to a test.
 *
 * # Example:
 *
 * $args[] = array( true, false );
 * $args[] = array( 5, 10 );
 * $args[] = array( 'foo', 'bar' );
 *
 * $result = combine( $args );
 *
 * # Result:
 *
 * array(
 *    array( true, 5, 'foo' ),
 *    array( true, 5, 'bar' ),
 *    array( true, 10, 'foo' ),
 *    array( true, 10, 'bar' ),
 *    array( false, 5, 'foo' ),
 *    array( false, 5, 'bar' ),
 *    array( false, 10, 'foo' ),
 *    array( false, 10, 'bar' )
 * );
 *
 * @param array $arrays
 * @param int   $i
 *
 * @return array
 */
function combinations( $arrays, $i = 0 ) {
	if ( ! array_key_exists( $i, $arrays ) ) {
		return array();
	}
	if ( ( count( $arrays ) - 1 ) === $i ) {
		return $arrays[ $i ];
	}

	// get combinations from subsequent arrays
	$tmp = combinations( $arrays, $i + 1 );

	$result = array();

	// concat each array from tmp with each element from $arrays[$i]
	foreach ( $arrays[ $i ] as $v ) {
		foreach ( $tmp as $t ) {
			$result[] = is_array( $t ) ? array_merge( array( $v ), $t ) : array( $v, $t );
		}
	}

	return $result;
}

function rand_str( $len = 32 ) {
	$non_numeric_prefix = rand_long_str( 1 );
	return $non_numeric_prefix . substr( md5( uniqid( mt_rand(), true ) ), 0, $len - 1 );
}

function rand_long_str( $length ) {
	$chars  = 'abcdefghijklmnopqrstuvwxyz';
	$string = '';

	for ( $i = 0; $i < $length; $i ++ ) {
		$rand = mt_rand( 0, strlen( $chars ) - 1 );
		$string .= $chars[ $rand ];
	}

	return $string;
}

// strip leading and trailing whitespace from each line in the string
function strip_ws( $txt ) {
	$lines  = explode( "\n", $txt );
	$result = array();
	foreach ( $lines as $line ) {
		if ( trim( $line ) ) {
			$result[] = trim( $line );
		}
	}

	return trim( join( "\n", $result ) );
}

// helper class for testing code that involves actions and filters
// typical use:
// $ma = new MockAction();
// add_action('foo', array(&$ma, 'action'));
class MockAction {
	var $events;
	var $debug;

	/**
	 * PHP5 constructor.
	 */
	function __construct( $debug = 0 ) {
		$this->reset();
		$this->debug = $debug;
	}

	function reset() {
		$this->events = array();
	}

	function current_filter() {
		if ( is_callable( 'current_filter' ) ) {
			return current_filter();
		}
		global $wp_actions;

		return end( $wp_actions );
	}

	function action( $arg ) {
		if ( $this->debug ) {
			dmp( __FUNCTION__, $this->current_filter() );
		}
		$args           = func_get_args();
		$this->events[] = array( 'action' => __FUNCTION__, 'tag' => $this->current_filter(), 'args' => $args );

		return $arg;
	}

	function action2( $arg ) {
		if ( $this->debug ) {
			dmp( __FUNCTION__, $this->current_filter() );
		}

		$args           = func_get_args();
		$this->events[] = array( 'action' => __FUNCTION__, 'tag' => $this->current_filter(), 'args' => $args );

		return $arg;
	}

	function filter( $arg ) {
		if ( $this->debug ) {
			dmp( __FUNCTION__, $this->current_filter() );
		}

		$args           = func_get_args();
		$this->events[] = array( 'filter' => __FUNCTION__, 'tag' => $this->current_filter(), 'args' => $args );

		return $arg;
	}

	function filter2( $arg ) {
		if ( $this->debug ) {
			dmp( __FUNCTION__, $this->current_filter() );
		}

		$args           = func_get_args();
		$this->events[] = array( 'filter' => __FUNCTION__, 'tag' => $this->current_filter(), 'args' => $args );

		return $arg;
	}

	function filter_append( $arg ) {
		if ( $this->debug ) {
			dmp( __FUNCTION__, $this->current_filter() );
		}

		$args           = func_get_args();
		$this->events[] = array( 'filter' => __FUNCTION__, 'tag' => $this->current_filter(), 'args' => $args );

		return $arg . '_append';
	}

	function filterall( $tag, $arg = null ) {
		// this one doesn't return the result, so it's safe to use with the new 'all' filter
		if ( $this->debug ) {
			dmp( __FUNCTION__, $this->current_filter() );
		}

		$args           = func_get_args();
		$this->events[] = array( 'filter' => __FUNCTION__, 'tag' => $tag, 'args' => array_slice( $args, 1 ) );
	}

	// return a list of all the actions, tags and args
	function get_events() {
		return $this->events;
	}

	// return a count of the number of times the action was called since the last reset
	function get_call_count( $tag = '' ) {
		if ( $tag ) {
			$count = 0;
			foreach ( $this->events as $e ) {
				if ( $e['action'] == $tag ) {
					++ $count;
				}
			}

			return $count;
		}

		return count( $this->events );
	}

	// return an array of the tags that triggered calls to this action
	function get_tags() {
		$out = array();
		foreach ( $this->events as $e ) {
			$out[] = $e['tag'];
		}

		return $out;
	}

	// return an array of args passed in calls to this action
	function get_args() {
		$out = array();
		foreach ( $this->events as $e ) {
			$out[] = $e['args'];
		}

		return $out;
	}
}

// convert valid xml to an array tree structure
// kinda lame but it works with a default php 4 install
class testXMLParser {
	var $xml;
	var $data = array();

	/**
	 * PHP5 constructor.
	 */
	function __construct( $in ) {
		$this->xml = xml_parser_create();
		xml_set_object( $this->xml, $this );
		xml_parser_set_option( $this->xml, XML_OPTION_CASE_FOLDING, 0 );
		xml_set_element_handler( $this->xml, array( $this, 'startHandler' ), array( $this, 'endHandler' ) );
		xml_set_character_data_handler( $this->xml, array( $this, 'dataHandler' ) );
		$this->parse( $in );
	}

	function parse( $in ) {
		$parse = xml_parse( $this->xml, $in, sizeof( $in ) );
		if ( ! $parse ) {
			trigger_error( sprintf( "XML error: %s at line %d", xml_error_string( xml_get_error_code( $this->xml ) ), xml_get_current_line_number( $this->xml ) ), E_USER_ERROR );
			xml_parser_free( $this->xml );
		}

		return true;
	}

	function startHandler( $parser, $name, $attributes ) {
		$data['name'] = $name;
		if ( $attributes ) {
			$data['attributes'] = $attributes;
		}
		$this->data[] = $data;
	}

	function dataHandler( $parser, $data ) {
		$index = count( $this->data ) - 1;
		$this->data[ $index ]['content'] .= $data;
	}

	function endHandler( $parser, $name ) {
		if ( count( $this->data ) > 1 ) {
			$data                            = array_pop( $this->data );
			$index                           = count( $this->data ) - 1;
			$this->data[ $index ]['child'][] = $data;
		}
	}
}

function xml_to_array( $in ) {
	$p = new testXMLParser( $in );

	return $p->data;
}

function xml_find( $tree /*, $el1, $el2, $el3, .. */ ) {
	$a   = func_get_args();
	$a   = array_slice( $a, 1 );
	$n   = count( $a );
	$out = array();

	if ( $n < 1 ) {
		return $out;
	}

	foreach ( $tree as $i => $tree_item ) {
		#		echo "checking '{$tree[$i][name]}' == '{$a[0]}'\n";
		#		var_dump($tree[$i]['name'], $a[0]);
		if ( $tree[ $i ]['name'] == $a[0] ) {
			#			echo "n == {$n}\n";
			if ( 1 === $n ) {
				$out[] = $tree[ $i ];
			} else {
				$subtree   =& $tree[ $i ]['child'];
				$call_args = array( $subtree );
				$call_args = array_merge( $call_args, array_slice( $a, 1 ) );
				$out       = array_merge( $out, call_user_func_array( 'xml_find', $call_args ) );
			}
		}
	}

	return $out;
}

function xml_join_atts( $atts ) {
	$a = array();
	foreach ( $atts as $k => $v ) {
		$a[] = $k . '="' . $v . '"';
	}

	return implode( ' ', $a );
}

function xml_array_dumbdown( &$data ) {
	$out = array();

	foreach ( array_keys( $data ) as $i ) {
		$name = $data[ $i ]['name'];
		if ( ! empty( $data[ $i ]['attributes'] ) ) {
			$name .= ' ' . xml_join_atts( $data[ $i ]['attributes'] );
		}

		if ( ! empty( $data[ $i ]['child'] ) ) {
			$out[ $name ][] = xml_array_dumbdown( $data[ $i ]['child'] );
		} else {
			$out[ $name ] = $data[ $i ]['content'];
		}
	}

	return $out;
}

function dmp() {
	$args = func_get_args();

	foreach ( $args as $thing ) {
		echo( is_scalar( $thing ) ? (string) $thing : var_export( $thing, true ) ), "\n";
	}
}

function dmp_filter( $a ) {
	dmp( $a );

	return $a;
}

function get_echo( $callable, $args = array() ) {
	ob_start();
	call_user_func_array( $callable, $args );

	return ob_get_clean();
}

// recursively generate some quick assertEquals tests based on an array
function gen_tests_array( $name, $array ) {
	$out = array();
	foreach ( $array as $k => $v ) {
		if ( is_numeric( $k ) ) {
			$index = (string) $k;
		} else {
			$index = "'" . addcslashes( $k, "\n\r\t'\\" ) . "'";
		}

		if ( is_string( $v ) ) {
			$out[] = '$this->assertEquals( \'' . addcslashes( $v, "\n\r\t'\\" ) . '\', $' . $name . '[' . $index . '] );';
		} elseif ( is_numeric( $v ) ) {
			$out[] = '$this->assertEquals( ' . $v . ', $' . $name . '[' . $index . '] );';
		} elseif ( is_array( $v ) ) {
			$out[] = gen_tests_array( "{$name}[{$index}]", $v );
		}
	}

	return implode( "\n", $out ) . "\n";
}

function print_backtrace() {
	$bt = debug_backtrace();
	echo "Backtrace:\n";
	$i = 0;
	foreach ( $bt as $stack ) {
		echo ++ $i, ": ";
		if ( isset( $stack['class'] ) ) {
			echo $stack['class'] . '::';
		}
		if ( isset( $stack['function'] ) ) {
			echo $stack['function'] . '() ';
		}
		echo 'line ' . $stack['line'] . ' in ' . $stack['file'] . PHP_EOL;
	}
	echo "\n";
}

// mask out any input fields matching the given name
function mask_input_value( $in, $name = '_wpnonce' ) {
	return preg_replace( '@<input([^>]*) name="' . preg_quote( $name ) . '"([^>]*) value="[^>]*" />@', '<input$1 name="' . preg_quote( $name ) . '"$2 value="***" />', $in );
}

if ( ! function_exists( 'str_getcsv' ) ) {
	function str_getcsv( $input, $delimiter = ',', $enclosure = '"', $escape = "\\" ) {
		$fp = fopen( 'php://temp/', 'r+' );
		fwrite( $fp, $input );
		rewind( $fp );
		$data = fgetcsv( $fp, strlen( $input ), $delimiter, $enclosure );
		fclose( $fp );

		return $data;
	}
}

/**
 * Determine approximate backtrack count when running PCRE.
 * @return int The backtrack count.
 */
function benchmark_pcre_backtracking( $pattern, $subject, $strategy ) {
	$saved_config = ini_get( 'pcre.backtrack_limit' );

	// Attempt to prevent PHP crashes.  Adjust these lower when needed.
	if ( version_compare( phpversion(), '5.4.8', '>' ) ) {
		$limit = 1000000;
	} else {
		$limit = 20000;  // 20,000 is a reasonable upper limit, but see also https://core.trac.wordpress.org/ticket/29557#comment:10
	}

	// Start with small numbers, so if a crash is encountered at higher numbers we can still debug the problem.
	for ( $i = 4; $i <= $limit; $i *= 2 ) {

		ini_set( 'pcre.backtrack_limit', $i );

		switch ( $strategy ) {
			case 'split':
				preg_split( $pattern, $subject );
				break;
			case 'match':
				preg_match( $pattern, $subject );
				break;
			case 'match_all':
				$matches = array();
				preg_match_all( $pattern, $subject, $matches );
				break;
		}

		ini_set( 'pcre.backtrack_limit', $saved_config );

		switch ( preg_last_error() ) {
			case PREG_NO_ERROR:
				return $i;
			case PREG_BACKTRACK_LIMIT_ERROR:
				break;
			case PREG_RECURSION_LIMIT_ERROR:
				trigger_error( 'PCRE recursion limit encountered before backtrack limit.' );

				return null;
			case PREG_BAD_UTF8_ERROR:
				trigger_error( 'UTF-8 error during PCRE benchmark.' );

				return null;
			case PREG_INTERNAL_ERROR:
				trigger_error( 'Internal error during PCRE benchmark.' );

				return null;
			default:
				trigger_error( 'Unexpected error during PCRE benchmark.' );

				return null;
		}
	}

	return $i;
}

/**
 * @return array
 */
function get_dummy_ls_languages() {
	return array(
		'en' => array(
			'code'             => 'en',
			'id'               => '1',
			'native_name'      => 'English',
			'major'            => '1',
			'active'           => '1',
			'default_locale'   => 'en_US',
			'encode_url'       => '0',
			'tag'              => 'en',
			'translated_name'  => 'English',
			'display_name'     => 'English',
			'url'              => 'http://example.org',
			'country_flag_url' => 'http://example.org/wp-content/plugins/sitepress-multilingual-cms/res/flags/en.png',
			'language_code'    => 'en',
		),
		'fr' => array(
			'code'             => 'fr',
			'id'               => '4',
			'native_name'      => 'Français',
			'major'            => '1',
			'active'           => 0,
			'default_locale'   => 'fr_FR',
			'encode_url'       => '0',
			'tag'              => 'fr',
			'translated_name'  => 'French',
			'display_name'     => 'French',
			'url'              => 'http://example.org?lang=fr',
			'country_flag_url' => 'http://example.org/wp-content/plugins/sitepress-multilingual-cms/res/flags/fr.png',
			'language_code'    => 'fr',
		),
		'de' => array(
			'code'             => 'de',
			'id'               => '3',
			'native_name'      => 'Deutsch',
			'major'            => '1',
			'active'           => 0,
			'default_locale'   => 'de_DE',
			'encode_url'       => '0',
			'tag'              => 'de',
			'translated_name'  => 'German',
			'display_name'     => 'German',
			'url'              => 'http://example.org?lang=de',
			'country_flag_url' => 'http://example.org/wp-content/plugins/sitepress-multilingual-cms/res/flags/de.png',
			'language_code'    => 'de',
		),
		'it' => array(
			'code'             => 'it',
			'id'               => '27',
			'native_name'      => 'Italiano',
			'major'            => '1',
			'active'           => 0,
			'default_locale'   => 'it_IT',
			'encode_url'       => '0',
			'tag'              => 'it',
			'translated_name'  => 'Italian',
			'display_name'     => 'Italian',
			'url'              => 'http://example.org?lang=it',
			'country_flag_url' => 'http://example.org/wp-content/plugins/sitepress-multilingual-cms/res/flags/it.png',
			'language_code'    => 'it',
		),
		'ru' => array(
			'code'             => 'ru',
			'id'               => '46',
			'native_name'      => 'Русский',
			'major'            => '1',
			'active'           => 0,
			'default_locale'   => 'ru_RU',
			'encode_url'       => '0',
			'tag'              => 'ru',
			'translated_name'  => 'Russian',
			'display_name'     => 'Russian',
			'url'              => 'http://example.org?lang=ru',
			'country_flag_url' => 'http://example.org/wp-content/plugins/sitepress-multilingual-cms/res/flags/ru.png',
			'language_code'    => 'ru',
		),
		'es' => array(
			'code'             => 'es',
			'id'               => '2',
			'native_name'      => 'Español',
			'major'            => '1',
			'active'           => 0,
			'default_locale'   => 'es_ES',
			'encode_url'       => '0',
			'tag'              => 'es',
			'translated_name'  => 'Spanish',
			'display_name'     => 'Spanish',
			'url'              => 'http://example.org?lang=es',
			'country_flag_url' => 'http://example.org/wp-content/plugins/sitepress-multilingual-cms/res/flags/es.png',
			'language_code'    => 'es',
		),
	);
}
