<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2019-03-18
 * Time: 19:07
 */

namespace Zero\Co;

use Swoole\Coroutine\Http\Client;

class HttpClient {
	/**
	 * @var Client
	 */
	protected $cli;
	protected $setting;

	/**
	 * HttpClient constructor.
	 * @param        $host
	 * @param        $port
	 * @param bool   $ssl
	 * @param int    $timeout //s
	 */
	public function __construct($host, $port, $ssl = FALSE, $timeout = 1) {
		$this->cli     = new Client($host, $port, $ssl);
		$this->setting = ['timeout' => $timeout];
		$this->cli->set($this->setting);
	}

	/**
	 * @param array $headers
	 */
	public function setReqHeaders($headers = []) {
		$this->cli->setHeaders($headers);
	}

	/**
	 * @param array $setting
	 */
	public function setting($setting = []) {
		if ($setting) {
			$this->cli->set(array_merge($this->setting, $setting));
		}
	}

	/**
	 * @param $path
	 * @return string
	 */
	public function get($path) {
		$this->cli->get($path);
		$body = $this->cli->body;
		return $body;
	}

	/**
	 * @return int
	 */
	public function errCode() {
		return $this->cli->errCode;
	}

	/**
	 * @return mixed
	 */
	public function statusCode() {
		return $this->cli->statusCode;
	}

	/**
	 * @return mixed
	 */
	public function headers() {
		return $this->cli->headers;
	}

	/**
	 * @return bool
	 */
	public function isConnected() {
		return $this->cli->connected;
	}

	/**
	 * @return mixed
	 */
	public function cookies() {
		return $this->cli->cookies;
	}

	/**
	 * @param       $path
	 * @param array $params
	 * @return string
	 */
	public function post($path, $params = []) {
		$this->cli->post($path, $params);
		$body = $this->cli->body;
		return $body;
	}

	/**
	 * @param      $file
	 * @param      $name
	 * @param null $type
	 * @param null $filename
	 * @param null $offset
	 * @param null $length
	 */
	public function addFile($file, $name, $type = NULL, $filename = NULL, $offset = NULL, $length = NULL) {
		$this->cli->addFile($file, $name, $type, $filename, $offset, $length);
	}

	public function exec($path) {
		$this->cli->execute($path);
		$body = $this->cli->body;
		return $body;
	}

	public function __destruct() {
		$this->cli->close();
	}
}
