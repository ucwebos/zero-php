<?php

namespace Zero\Fpm;

use Zero\Config;
use Zero\Route\Dispatcher;
use Zero\Business\Http\Response;
use Zero\Route\Route;
use Zero\IBootstrap;

class Entry {
	/**
	 * @var IBootstrap;
	 */
	protected $bootstrap;
	/**
	 * @var Dispatcher
	 */
	protected $dispatcher;

	/**
	 * Entry constructor.
	 * @param string $tag
	 */
	public function __construct($tag = 'MAIN') {
		Config::load();
		$config    = Config::get('SERVER.' . $tag);
		$bootstrap = $config['bootstrap'] ?? '';
		if (!class_exists($bootstrap)) {
			die("not found class [SERVER.$tag.bootstrap]!");
		}
		$this->bootstrap = new $bootstrap;
		if (!$this->bootstrap instanceof IBootstrap) {
			die("the class [{$bootstrap}] unrealized interface [IBootstrap]");
		}
		try {
			$this->bootstrap->init();
			$r = $this->bootstrap->route(new Route($this->bootstrap->namespace));
		} catch (\Throwable $e) {
			die($e->getMessage());
		}

		$this->dispatcher = new Dispatcher($r->getRoutes());
	}

	public function run() {
		$req = new Request();
		/**
		 * @var Response $resp
		 */
		$resp   = $this->dispatcher->dispatch($req);
		$result = $resp->getResult();
		if (is_array($result) || is_object($result)) {
			$result = json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		}
		http_response_code($resp->getStatus());
		foreach ($resp->getHeaders() as $k => $v) {
			header($k . ': ' . $v);
		}
		foreach ($resp->getCookies() as $k => $v) {
			$value  = $v['value'] ?? '';
			$expire = $v['expire'] ?? 0;
			$path   = $v['path'] ?? '';
			$domain = $v['domain'] ?? '';
			$secure = $v['secure'] ?? FALSE;
			setcookie($k, $value, $expire, $path, $domain, $secure);
		}
		echo $result;
	}
}
