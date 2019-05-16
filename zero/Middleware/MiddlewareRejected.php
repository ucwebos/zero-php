<?php

namespace Zero\Middleware;

class MiddlewareRejected {
	private $code;
	private $result;
	private $response;

	public function __construct($result = '', $code = 200) {
		$this->result = $result;
		$this->code   = $code;
	}

	/**
	 * @return mixed
	 */
	public function getCode() {
		return $this->code;
	}

	/**
	 * @return mixed
	 */
	public function getResult() {
		return $this->result;
	}

	/**
	 * @return mixed
	 */
	public function getResponse() {
		return $this->response;
	}

	/**
	 * @param mixed $response
	 */
	public function setResponse($response): void {
		$this->response = $response;
	}
}
