<?php

namespace Zero\Client;

use Zero\Co\HttpCS;

class Http {

	/**
	 * @param              $url
	 * @param int|float    $timeout // s 秒
	 * @param array        $headers
	 * @return string
	 */
	public static function get($url, $timeout = 1, $headers = []) {
		if(isCo()){
			return HttpCS::get($url,$timeout,$headers);
		}
		return Curl::get($url,$timeout,$headers);
	}

	/**
	 * @param              $url
	 * @param array|string $params
	 * @param int|float    $timeout // s 秒
	 * @param array        $headers
	 * @return string
	 */
	public static function post($url, $params = [], $timeout = 1, $headers = []) {
		if(isCo()){
			return HttpCS::post($url,$params,$timeout,$headers);
		}
		return Curl::post($url,$params,$timeout,$headers);
	}
}
