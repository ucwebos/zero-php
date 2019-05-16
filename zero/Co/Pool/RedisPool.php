<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2019-03-07
 * Time: 18:42
 */

namespace Zero\Co\Pool;

use Zero\Database\Redis;
use Zero\Exception\DbException;
use Zero\Exception\PoolException;

class RedisPool extends Pool {
	/**
	 * @return bool
	 * @throws DbException
	 */
	public function ping() {
		$redis = new Redis($this->name);
		return $redis->ping();
	}

	/**
	 * @throws DbException
	 */
	protected function create() {
		$mysql = new Redis($this->name);
		$this->chan->push($mysql);
		$this->createNum++;
	}

	public function check($tickTime = 10000) {
		swoole_timer_tick($tickTime, function () {
			$len = $this->chan->length();
			for ($i = 0; $i < $len; $i++) {
				/**
				 * @var $cli Redis
				 */
				$cli = $this->chan->pop($this->poolTimeout);
				if(!$cli){
					continue;
				}
				if (time() - $cli->lastActiveTime > $this->maxIdleTime) {
					$this->delete($cli);
					continue;
				}
				if (!$cli->ping()) {
					$cli->reconnect();
				}
				$this->recycle($cli);
			}
		});
	}

	/**
	 * @return \Redis|false
	 * @throws DbException
	 */
	public function get() {
		if ($this->chan->isEmpty() && $this->createNum < $this->poolSize) {
			$this->create();
		}
		return $this->chan->pop($this->poolTimeout);
	}

	/**
	 * @param $cli
	 */
	public function recycle($cli) {
		if ($this->chan->isFull()) {
			return;
		}
		$this->chan->push($cli, $this->poolTimeout);
	}

	/**
	 * @param $cli
	 */
	protected function delete($cli) {
		$this->createNum--;
		unset($cli);
	}
}
