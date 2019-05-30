<?php

namespace Zero\Client;

class Curl {
	private static $option       = [
		CURLOPT_RETURNTRANSFER       => TRUE,
		CURLOPT_ENCODING             => '',
		CURLOPT_DNS_USE_GLOBAL_CACHE => TRUE,
		CURLOPT_DNS_CACHE_TIMEOUT    => 60,
	];
	private static $lastCurlInfo = [];

	/**
	 * @param              $url
	 * @param int|float    $timeout // s 秒
	 * @param array        $headers
	 * @return string
	 */
	public static function get($url, $timeout = 1, $headers = []) {
		$timeoutMs = $timeout * 1000;
		$_option   = [
			CURLOPT_URL               => $url,
			CURLOPT_TIMEOUT_MS        => $timeoutMs,
			CURLOPT_CONNECTTIMEOUT_MS => $timeoutMs,
		];
		if ($headers) {
			$_option[CURLOPT_HTTPHEADER] = $headers;
		}
	}

	/**
	 * @param              $url
	 * @param array|string $params
	 * @param int|float    $timeout // s 秒
	 * @param array        $headers
	 * @return string
	 */
	public static function post($url, $params = [], $timeout = 1, $headers = []) {
		$timeoutMs = $timeout * 1000;
		$_option   = [
			CURLOPT_URL               => $url,
			CURLOPT_POST              => TRUE,
			CURLOPT_POSTFIELDS        => $params,
			CURLOPT_TIMEOUT_MS        => $timeoutMs,
			CURLOPT_CONNECTTIMEOUT_MS => $timeoutMs,
		];
		if ($headers) {
			$_option[CURLOPT_HTTPHEADER] = $headers;
		}
		return self::curlDo($_option);
	}

	private static function curlDo($option) {
		$option = $option + self::$option;
		$ch     = curl_init();
		curl_setopt_array($ch, $option);
		$ret                = curl_exec($ch) ?: '';
		self::$lastCurlInfo = [
			'errno' => curl_errno($ch),
			'error' => curl_error($ch),
			'info'  => curl_getinfo($ch),
		];
		curl_close($ch);

		return $ret;
	}

	public static function lastCurlInfo() {
		return self::$lastCurlInfo;
	}
}
