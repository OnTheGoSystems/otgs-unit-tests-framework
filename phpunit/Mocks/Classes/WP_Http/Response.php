<?php
/**
 * @author OnTheGo Systems
 */

namespace OTGS\PhpUnit\Mocks\Classes\WP_Http;

class Response {
	private $code = null;
	private $message = null;
	private $body     = null;
	private $cookies  = array();
	private $filename = null;
	private $headers = array();
	private $response = array();

	/**
	 * @return null|string
	 */
	public function getBody() {
		return $this->body;
	}

	/**
	 * @param null|string $body
	 */
	public function setBody( $body ) {
		$this->body = $body;
	}

	/**
	 * @return null|int
	 */
	public function getCode() {
		return $this->code;
	}

	/**
	 * @return array
	 */
	public function getCookies() {
		return $this->cookies;
	}

	/**
	 * @param array $cookies
	 */
	public function setCookies( $cookies ) {
		$this->cookies = $cookies;
	}

	/**
	 * @return null|string
	 */
	public function getFilename() {
		return $this->filename;
	}

	/**
	 * @param null|string $filename
	 */
	public function setFilename( $filename ) {
		$this->filename = $filename;
	}

	/**
	 * @return array
	 */
	public function getHeaders() {
		return $this->headers;
	}

	/**
	 * @param array $headers
	 */
	public function setHeaders( $headers ) {
		$this->headers = $headers;
	}

	/**
	 * @return null|string
	 */
	public function getMessage() {
		return $this->message;
	}

	/**
	 * @return array
	 */
	public function getResponse() {
		return $this->response;
	}

	/**
	 * @param array $response
	 */
	public function setResponse( $response ) {
		$this->response = $response;
	}

	/**
	 * @param null|int $code
	 */
	public function setCode( $code ) {
		$this->code = $code;
	}

	/**
	 * @param null|string $message
	 */
	public function setMessage( $message ) {
		$this->message = $message;
	}
}
