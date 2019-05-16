<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2019-03-18
 * Time: 19:07
 */

namespace Zero\Co;

class HttpCS {
	/**
	 * @param       $url
	 * @param int   $timeout
	 * @param array $headers
	 * @return string
	 */
	public static function get($url, $timeout = 1, $headers = []) {
		$info  = parse_url($url);
		$host  = $info['host'] ?? '';
		$dPort = 80;
		$ssl   = FALSE;
		if ($info['scheme'] == 'https') {
			$dPort = 443;
			$ssl   = TRUE;
		}
		$port = $info['port'] ?? $dPort;
		$path = $info['path'] ?: '/';
		if ($info['query']) {
			$path = $info['path'] . '?' . $info['query'];
		}
		$cli = new HttpClient($host, $port, $ssl, $timeout);
		if ($headers) {
			$cli->setReqHeaders($headers);
		}
		$body = $cli->get($path);
		return $body;
	}

	/**
	 * @param       $url
	 * @param array $params
	 * @param int   $timeout
	 * @param array $headers
	 * @return string
	 */
	public static function post($url, $params = [], $timeout = 1, $headers = []) {
		$info  = parse_url($url);
		$host  = $info['host'] ?? '';
		$dPort = 80;
		$ssl   = FALSE;
		if ($info['scheme'] == 'https') {
			$dPort = 443;
			$ssl   = TRUE;
		}
		$port = $info['port'] ?? $dPort;
		$path = $info['path'] ?? '/';
		$cli  = new HttpClient($host, $port, $ssl, $timeout);
		if ($headers) {
			$cli->setReqHeaders($headers);
		}
		return $cli->post($path, $params);
	}

	public static function postFile($url, $params = [], $files = [], $timeout = 1, $headers = []) {
		$info  = parse_url($url);
		$host  = $info['host'] ?? '';
		$dPort = 80;
		$ssl   = FALSE;
		if ($info['scheme'] == 'https') {
			$dPort = 443;
			$ssl   = TRUE;
		}
		$port = $info['port'] ?? $dPort;
		$path = $info['path'] ?? '/';
		echo $host . PHP_EOL;
		echo $path . PHP_EOL;
		$cli = new HttpClient($host, $port, $ssl, $timeout);
		if ($headers) {
			$cli->setReqHeaders($headers);
		}
		if ($files) {
			foreach ($files as $file) {
				$path = $file['path'];
				$name = $file['name'];
				$cli->addFile($path, $name);
			}
		}
		return $cli->post($path, $params);
	}
}
