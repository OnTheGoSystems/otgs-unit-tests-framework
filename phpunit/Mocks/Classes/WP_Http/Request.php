<?php
/**
 * @author OnTheGo Systems
 */

namespace OTGS\PhpUnit\Mocks\Classes\WP_Http;

class Request {
	private $endPoint = null;
	private $body = null;
	private $headers = array();
	private $method = 'GET';

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
	 * @return null|string
	 */
	public function getEndPoint() {
		return $this->endPoint;
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
	 * @return string
	 */
	public function getMethod() {
		return $this->method;
	}

	/**
	 * @param string $method
	 */
	public function setMethod( $method ) {
		$this->method = $method;
	}

	/**
	 * @param null|string $endPoint
	 */
	public function setEndPoint( $endPoint ) {
		$this->endPoint = $endPoint;
	}
}
