<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2019-03-06
 * Time: 16:08
 */

namespace Zero\Server;

class Request extends \Zero\Business\Http\Request {
	/**
	 * Request constructor.
	 * @param \Swoole\Http\Request $request
	 */
	public function __construct($request) {
		$this->server = $request->server;
		$this->header = $request->header;
		$this->get    = $request->get ?: [];
		$this->post   = $request->post ?: [];
		$this->files  = $request->files ?: [];
		$this->cookie = $request->cookie ?: [];
		$this->raw    = $request->rawContent();
		$this->params = array_merge($this->get, $this->post);
		$this->method = $this->server['request_method'];
		$uri          = rawurldecode($this->server['request_uri'] ?? '');
		if (FALSE !== $pos = strpos($uri, '?')) {
			$uri = substr($uri, 0, $pos);
		}
		$this->uri         = $uri;
		$this->requestTime = $this->server['request_time_float'] ?? 0;
	}
}
