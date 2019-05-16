<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2019-03-06
 * Time: 16:25
 */

namespace Zero\Business\Http;

class Response {
	private $headers = [];
	private $cookies = [];
	private $status  = 200;
	private $result;

	public function setResult($result) {
		$this->result = $result;
		return $this;
	}

	public function setHeaders(array $headers) {
		$this->headers = $headers;
		return $this;
	}

	public function addHeader(string $name, $value) {
		$this->headers[$name] = $value;
		return $this;
	}

	public function setCookies(array $cookies) {
		$this->cookies = $cookies;
		return $this;
	}

	public function addCookie(string $name, $value, $expire = 0, $path = NULL, $domain = NULL, $secure = NULL) {
		$this->cookies[$name] = [
			'value'  => $value,
			'expire' => $expire,
			'path'   => $path,
			'domain' => $domain,
			'secure' => $secure
		];
		return $this;
	}

	public function setStatus($status = 200) {
		$this->status = $status;
		return $this;
	}

	public function getResult() {
		return $this->result;
	}

	public function getStatus() {
		return $this->status;
	}

	public function getHeaders() {
		return $this->headers;
	}

	public function getCookies() {
		return $this->cookies;
	}
}
