<?php

/**
 * @author OnTheGo Systems
 */
class WP_Error {
	private $code;
	public  $errors     = array();
	public  $error_data = array();
	private $message;

	/**
	 * WP_Error constructor.
	 */
	public function __construct( $code, $message ) {
		$this->code    = $code;
		$this->message = $message;
	}

	public function get_error_message() {
		return $this->message;
	}
}