<?php
/**
 * The two error classes the `is_wp_error()` mock recognises: WordPress's own `WP_Error` (a product suite loads a copy
 * of it) and `OTGS\Mocks\WP_Error` (a suite may define one).
 */

namespace {
	if ( ! class_exists( 'WP_Error' ) ) {
		class WP_Error {
			public $errors = array();

			public function __construct( $code = '', $message = '', $data = '' ) {
				if ( $code ) {
					$this->errors[ $code ][] = $message;
				}
			}
		}
	}
}

namespace OTGS\Mocks {
	class WP_Error {
	}
}
