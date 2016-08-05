<?php

/**
 * Class WP_Query
 */
class WP_Query {

	public $suppress_filters = true;

	public function set( $key, $value ) {
		$this->{ $key } = $value;
	}
}
