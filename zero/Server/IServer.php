<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2018/12/17
 * Time: 下午5:40
 */

namespace Zero\Server;

abstract class IServer {
	/**
	 * @var \Swoole\Server | \Swoole\Http\Server | \Swoole\Websocket\server
	 */
	protected $server;
	/**
	 * @var string 项目名称
	 */
	protected $project_name;
	/**
	 * @var string PID文件存放路径
	 */
	protected $pid_path;

	public function __construct($project_name, $pid_path) {
		$this->project_name = $project_name;
		$this->pid_path     = $pid_path;
	}

	public function onStart(\Swoole\Server $server) {
		@cli_set_process_title($this->project_name . " running master:" . $server->master_pid);
		if (!empty($this->pid_path)) {
			file_put_contents($this->pid_path . DIRECTORY_SEPARATOR . $this->project_name . '.pid', $server->master_pid);
		}
	}

	/**
	 * @throws \Exception
	 */
	public function onShutDown() {
		if (!empty($this->pid_path)) {
			$filename = $this->pid_path . DIRECTORY_SEPARATOR . $this->project_name . '_master.pid';
			if (is_file($filename)) {
				unlink($filename);
			}
			$filename = $this->pid_path . DIRECTORY_SEPARATOR . $this->project_name . '_manager.pid';
			if (is_file($filename)) {
				unlink($filename);
			}
		}
	}

	/**
	 * @param $server
	 * @throws \Exception
	 * @desc 服务启动，设置进程名
	 */
	public function onManagerStart(\Swoole\Server $server) {
		@cli_set_process_title($this->project_name . ' manager:' . $server->manager_pid);
		if (!empty($this->pid_path)) {
			file_put_contents($this->pid_path . DIRECTORY_SEPARATOR . $this->project_name . '_manager.pid', $server->manager_pid);
		}
	}

	public function onManagerStop() {
		if (!empty($this->pid_path)) {
			$filename = $this->pid_path . DIRECTORY_SEPARATOR . $this->project_name . '_manager.pid';
			if (is_file($filename)) {
				unlink($filename);
			}
		}
	}

	/**
	 * @param $config
	 * @return int
	 */
	public function getSockType($config) {
		if (!isset($config['enable_ssl'])) {
			return SWOOLE_TCP;
		}
		if ($config['enable_ssl']) {
			if (!isset($config['setting']['ssl_cert_file']) || !isset($config['setting']['ssl_key_file'])) {
				return SWOOLE_TCP;
			} else {
				return SWOOLE_TCP | SWOOLE_SSL;
			}
		}
		return SWOOLE_TCP;
	}

	/**
	 * @param \Swoole\Server $server   swoole_server对象
	 * @param int            $workerId Worker进程ID
	 */
	public function doWorkerStart(\Swoole\Server $server, $workerId) {
		$workNum = $server->setting['worker_num'];
		if ($workerId >= $workNum) {
			@cli_set_process_title($this->project_name . ' tasker num: ' . ($server->worker_id - $workNum) . ' pid ' . $server->worker_pid);
		} else {
			@cli_set_process_title($this->project_name . ' worker num: ' . $server->worker_id . ' pid ' . $server->worker_pid);
		}
		$this->onWorkerStart($server, $workerId);
	}

	/**
	 * Worker进程退出时调用此函数
	 * @param \Swoole\Server $server   swoole_server对象
	 * @param int            $workerId Worker进程ID
	 **/
	public function onWorkerStop(\Swoole\Server $server, $workerId) {

	}

	/**
	 * @param \Swoole\Server $server     swoole_server对象
	 * @param int            $workerId   Worker进程ID
	 * @param int            $worker_pid Worker进程PID
	 * @param int            $exit_code  退出的错误码
	 * @param int            $signal     进程退出的信号
	 */
	public function onWorkerError(\Swoole\Server $server, $workerId, $worker_pid, $exit_code, $signal) {

	}

	/**
	 * 初始化函数，在swoole_server启动前执行
	 * @param \Swoole\Server $server
	 */
	abstract public function init(\Swoole\Server $server);

	/**
	 * Worker进程启动前回调此函数
	 * @param \Swoole\Server $server   swoole_server对象
	 * @param int            $workerId Worker进程ID
	 */
	abstract public function onWorkerStart(\Swoole\Server $server, $workerId);

	/**
	 * 当Worker进程投递任务到Task Worker进程时调用此函数
	 * @param \Swoole\Server $server        swoole_server对象
	 * @param int            $task_id       任务ID
	 * @param int            $src_worker_id 发起任务的Worker进程ID
	 * @param mixed          $data          任务数据
	 */
	abstract public function onTask(\Swoole\Server $server, $task_id, $src_worker_id, $data);

	/**
	 * Swoole进程间通信的回调函数
	 * @param \Swoole\Server $server         swoole_server对象
	 * @param int            $from_worker_id 来源Worker进程ID
	 * @param mixed          $message        消息内容
	 */
	abstract public function onPipeMessage(\Swoole\Server $server, $from_worker_id, $message);

	/**
	 * @return mixed
	 */
	abstract public function run();
}
