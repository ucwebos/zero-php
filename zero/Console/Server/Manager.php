<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2018/12/7
 * Time: 上午10:05
 */

namespace Zero\Console\Server;

use Zero\Config;
use Zero\Server\HttpServer;

class Manager {
	/**
	 * @var HttpServer
	 */
	private $server;
	private $config;
	private $pidFile;
	private $projectName;

	public function __construct() {
		$this->config      = Config::get('MAIN_SERVER');
		$this->projectName = $this->config['name'] ?? 'zero-php-project';
		$this->pidFile     = ROOT_PATH . '/' . $this->projectName . '.pid';
		$this->server      = new HttpServer($this->projectName, ROOT_PATH);
	}

	public function start() {
		$this->server->run();
	}

	public function daemon() {
		$setting              = Config::get('MAIN_SERVER.setting') ?: [];
		$setting['daemonize'] = 1;
		Config::setField('MAIN_SERVER.setting', $setting);
		$this->server->run();
	}

	public function reload() {
		$pid = intval(file_get_contents($this->pidFile));
		shell_exec("kill -USR1 {$pid}");
	}

	public function restart() {
		$this->stop();
		$this->daemon();
	}

	public function stop() {
		$pid = intval(file_get_contents($this->pidFile));
		while (\Swoole\Process::kill($pid, 0)) {
			@\Swoole\Process::kill($pid);
		}
	}

	public function kill() {
		exec("ps -ef|grep '{$this->projectName}'|grep -v grep|cut -c 9-15|xargs kill -9");
		sleep(1);
	}
}
