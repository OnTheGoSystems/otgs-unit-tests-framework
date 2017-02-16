<?php

/**
 * Class WP_Query
 */
class WP_Query {

	public $suppress_filters = true;

	public $found_posts = 0;

	public function set( $key, $value ) {
		$this->{ $key } = $value;
	}
}
