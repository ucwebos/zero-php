<?php

namespace Zero\Fpm;

class Request extends \Zero\Business\Http\Request {
	public function __construct() {
		$this->server = $_SERVER;
		$this->header = $this->parseHeaders();
		$this->get    = $_GET ?: [];
		$this->post   = $_POST ?: [];
		$this->files  = $_FILES ?: [];
		$this->cookie = $_COOKIE ?: [];
		$this->raw    = file_get_contents('php://input');
		$this->params = array_merge($_GET, $_POST);
		$this->method = $_SERVER['REQUEST_METHOD'];
		$uri          = rawurldecode($_SERVER['REQUEST_URI'] ?? '');
		if (FALSE !== $pos = strpos($uri, '?')) {
			$uri = substr($uri, 0, $pos);
		}
		$this->uri         = $uri;
		$this->requestTime = $_SERVER['REQUEST_TIME_FLOAT'] ?? 0;
	}

	private function parseHeaders() {
		$headers = [];
		foreach ($_SERVER as $key => $value) {
			if ('HTTP_' == substr($key, 0, 5)) {
				$headers[str_replace('_', '-', substr($key, 5))] = $value;
			}
		}
		if (isset($_SERVER['PHP_AUTH_DIGEST'])) {
			$headers['authorization'] = $_SERVER['PHP_AUTH_DIGEST'];
		} elseif (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
			$headers['authorization'] = base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $_SERVER['PHP_AUTH_PW']);
		}
		if (isset($_SERVER['CONTENT_LENGTH'])) {
			$headers['content-length'] = $_SERVER['CONTENT_LENGTH'];
		}
		if (isset($_SERVER['CONTENT_TYPE'])) {
			$headers['content-type'] = $_SERVER['CONTENT_TYPE'];
		}
		return $headers;
	}
}
