<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2019-03-06
 * Time: 16:08
 */

namespace Zero\Business\Http;

class Request {
	protected $params = [];
	protected $get;
	protected $post;
	protected $server;
	protected $header;
	protected $files;
	protected $cookie;
	protected $raw;
	protected $method;
	protected $uri;
	protected $requestTime;
	/**
	 * @var array 附加数据
	 */
	protected $append = [];

	public function getUri() {
		return $this->uri;
	}

	public function setParams(array $params) {
		$this->params = $params;
	}

	/**
	 * 获取参数
	 * @param      $key
	 * @param null $default
	 * @return mixed|null
	 */
	public function getParam($key, $default = NULL) {
		return $this->params[$key] ?? $default;
	}

	/**
	 * 获取文件
	 * @param      $key
	 * @param null $default
	 * @return array|null
	 */
	public function getFile($key, $default = NULL) {
		return $this->files[$key] ?? $default;
	}

	/**
	 * 获取cookie
	 * @param      $key
	 * @param null $default
	 * @return mixed|null
	 */
	public function getCookie($key, $default = NULL) {
		return $this->cookie[$key] ?? $default;
	}

	/**
	 * 获取所有参数
	 * @return array
	 */
	public function params() {
		return $this->params;
	}

	/**
	 * 判断参数是否存在
	 * @param $key
	 * @return bool
	 */
	public function has($key) {
		return isset($this->params[$key]) ? TRUE : FALSE;
	}

	/**
	 * 获取请求时间
	 * @return int
	 */
	public function getRequestTime() {
		return $this->requestTime;
	}

	/**
	 * 获取头信息
	 * @return mixed
	 */
	public function getHeaders() {
		return $this->header;
	}

	/**
	 * 获取cookie
	 * @return array
	 */
	public function getCookies() {
		return $this->cookie;
	}

	/**
	 * 获取上传文件列表
	 * @return array
	 */
	public function getFiles() {
		return $this->files;
	}

	/**
	 * 获取GET参数
	 * @return array
	 */
	public function paramsGet() {
		return $this->get;
	}

	/**
	 * 获取POST参数
	 * @return array
	 */
	public function paramsPost() {
		return $this->post;
	}

	/**
	 * 获取POST原始数据
	 * @return string
	 */
	public function getRaw() {
		return $this->raw;
	}

	/**
	 * 获取请求的HTTP方法
	 * @return mixed
	 */
	public function method() {
		return $this->method;
	}


}
