<?php

/**
 * @author OnTheGo Systems
 */
class WP_User {
	public $ID = 0;

	public function __construct( $id = 0, $name = '' ) {
		$this->ID           = $id;
		$this->display_name = $name;
		$this->nickname     = $name;
		$this->first_name   = 'First ' . $name;
		$this->last_name    = 'Last ' . $name;
	}
}