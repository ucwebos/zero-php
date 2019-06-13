<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2018/12/6
 * Time: 下午1:59
 */

namespace Zero\Server;

use Zero\Business\Http\Response;
use Zero\Config;
use Zero\Container;
use Zero\Route\Route;
use Zero\Route\Dispatcher;
use Zero\IBootstrap;

class HttpServer extends IServer {
	/**
	 * @var IBootstrap;
	 */
	protected $bootstrap;
	/**
	 * @var Dispatcher
	 */
	protected $dispatcher;

	public function init(\Swoole\Server $server) {
		echo "bootstrap init ....".PHP_EOL;
		$this->bootstrap->init();
		$r = $this->bootstrap->route(new Route($this->bootstrap->namespace));
		$this->dispatcher = new Dispatcher($r->getRoutes());
	}

	public function onRequest(\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
		$req = new Request($request);
		/**
		 * @var Response $resp
		 */
		$resp   = $this->dispatcher->dispatch($req);
		$result = $resp->getResult();
		if (is_array($result) || is_object($result)) {
			$result = json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		}
		foreach ($resp->getHeaders() as $k => $v) {
			$response->header($k, $v);
		}
		foreach ($resp->getCookies() as $k => $v) {
			$value  = $v['value'] ?? '';
			$expire = $v['expire'] ?? 0;
			$path   = $v['path'] ?? NULL;
			$domain = $v['domain'] ?? NULL;
			$secure = $v['secure'] ?? NULL;
			$response->cookie($k, $value, $expire, $path, $domain, $secure);
		}
		$response->status($resp->getStatus());
		$response->end($result);
	}

	public function onTask(\Swoole\Server $server, $task_id, $src_worker_id, $data) {
		if (is_array($data) && count($data) == 4) {
			list($class, $args, $name, $arguments) = $data;
			$obj = new $class(...$args);
			return call_user_func_array([$obj, $name], $arguments);
		}
	}

	public function onPipeMessage(\Swoole\Server $server, $from_worker_id, $message) {
	}

	public function onWorkerStart(\Swoole\Server $server, $workerId) {
		if ($server->taskworker) {
			define('TASK_WORKER', TRUE);
		}
		$this->bootstrap->start();
	}

	public function onWorkerStop(\Swoole\Server $server, $workerId) {
		parent::onWorkerStop($server, $workerId);
	}

	public function onWorkerError(\Swoole\Server $server, $workerId, $worker_pid, $exit_code, $signal) {
		parent::onWorkerError($server, $workerId, $worker_pid, $exit_code, $signal);
	}

	public function run() {
		$tag    = Config::get('SERVER_TAG') ?: 'MAIN';
		$config = Config::get('SERVER.' . $tag);
		if(!$config){
			die("not found config [SERVER." . $tag . "]!");
		}
		$host  = $config['host'] ?? '';
		$port  = $config['port'] ?? '';
		$model = SWOOLE_PROCESS;
		if ($config['model'] == 'BASE') {
			$model = SWOOLE_BASE;
		}

		$bootstrap = $config['bootstrap'] ?? '';
		if (!class_exists($bootstrap)) {
			die("not found class [SERVER." . $tag . ".bootstrap]!");
		}
		$this->bootstrap = new $bootstrap;
		if (!$this->bootstrap instanceof IBootstrap) {
			die("the class [{$bootstrap}] unrealized interface [IBootstrap]");
		}

		$this->server = new \Swoole\Http\Server($host, $port, $model);
		$this->server->set($config['setting'] ?? []);
		$this->server->on("Start", [$this, "onStart"]);
		$this->server->on("Shutdown", [$this, "onShutDown"]);
		$this->server->on("ManagerStart", [$this, "onManagerStart"]);
		$this->server->on("ManagerStop", [$this, "onManagerStop"]);
		$this->server->on("WorkerStart", [$this, "doWorkerStart"]);
		$this->server->on("PipeMessage", [$this, "onPipeMessage"]);

		$this->server->on("Request", [$this, "onRequest"]);

		if (isset($config['setting']['task_worker_num']) && $config['setting']['task_worker_num'] > 0) {
			//同步代理任务定义
			Container::app()->set(C_SRV, $this->server);
			$this->server->on("Task", [$this, "onTask"]);
		}
		try {
			$this->init($this->server);
		} catch (\Throwable $e) {
			die("init error: " . $e->getMessage());
		}
		echo "server start!".PHP_EOL;
		$this->server->start();
	}
}
