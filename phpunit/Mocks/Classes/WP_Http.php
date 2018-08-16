<?php
/**
 * @author OnTheGo Systems
 */

namespace OTGS\PhpUnit\Mocks\Classes;

use OTGS\PhpUnit\Mocks\Classes\WP_Http\Request;
use OTGS\PhpUnit\Mocks\Classes\WP_Http\Response;

class WP_Http {
	/** @var \PHPUnit\Framework\MockObject\MockObject|WP_Http */
	private $http;
	private $methodName;
	/** @var Request */
	private $request;
	/** @var Response */
	private $response;
	private $testCase;
	private $times;

	public function __construct( \PHPUnit\Framework\TestCase $testCase ) {
		$this->testCase = $testCase;
		$this->http     = $this->testCase->getMockBuilder( 'WP_Http' )
		                                 ->disableOriginalConstructor()
		                                 ->setMethods( array( 'get', 'post','head','request' ) )
		                                 ->getMock();
		$this->times    = $this->testCase->any();
	}

	public function withMethod( $name ) {
		$this->methodName = $name;

		return $this;
	}

	public function expect( \PHPUnit\Framework\MockObject\Matcher\InvokedCount $value ) {
		$this->times = $value;

		return $this;
	}

	public function withRequest( Request $request ) {
		$this->request = $request;

		return $this;
	}

	public function willRespondWith( Response $response ) {
		$this->response = $response;

		return $this;
	}

	public function getMock() {
		$this->setupMock();

		return $this->http;
	}

	/**
	 * @return array
	 */
	private function getRequestArguments() {
		$request_arguments = array(
			'method'  => $this->request->getMethod(),
			'headers' => $this->request->getHeaders(),
		);
		if ( null !== $this->request->getBody() ) {
			$request_arguments['body'] = $this->request->getBody();
		}

		return $request_arguments;
	}

	/**
	 * @return array
	 */
	private function getResponseArguments() {
		$response_arguments = array();
		$response_status    = array();

		if ( null !== $this->response->getCode() ) {
			$response_status['code'] = $this->response->getCode();
		}
		if ( null !== $this->response->getMessage() ) {
			$response_status['message'] = $this->response->getMessage();
		}

		if ( $response_status ) {
			$response_arguments['response'] = $response_status;
		}

		if ( null !== $this->response->getBody() ) {
			$response_arguments['body'] = $this->response->getBody();
		}

		return $response_arguments;
	}

	private function setupMock() {
		$this->http->expects( $this->times )
		           ->method( $this->methodName )
		           ->with( $this->request->getEndPoint() ?: $this->testCase->anything(), $this->getRequestArguments() ?: $this->testCase->anything() )
		           ->willReturn( $this->getResponseArguments() );
	}
}
