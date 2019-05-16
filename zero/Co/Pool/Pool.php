<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2018/12/7
 * Time: 上午11:57
 */

namespace Zero\Co\Pool;

use Swoole\Coroutine\Channel;

abstract class Pool {
	protected $name;
	/**
	 * @var Channel
	 */
	protected $chan;
	protected $poolSize;
	protected $maxIdleTime; //s
	protected $poolTimeout; //s
	protected $createNum = 0;

	public function __construct($name, $poolSize, $maxIdleTime, $poolTimeout) {
		$this->name        = $name;
		$this->poolSize    = $poolSize;
		$this->maxIdleTime = $maxIdleTime;
		$this->poolTimeout = $poolTimeout;
	}

	public function init() {
		$this->chan = new Channel($this->poolSize);
	}

	abstract public function ping();

	abstract protected function create();

	abstract public function check($tickTime = 10000);

	abstract public function get();

	abstract public function recycle($cli);

	abstract protected function delete($cli);
}
