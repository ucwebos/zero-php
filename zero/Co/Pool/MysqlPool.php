<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2019-03-07
 * Time: 18:42
 */

namespace Zero\Co\Pool;

use Zero\Database\Mysql;
use Zero\Exception\DbException;
use Zero\Exception\PoolException;

class MysqlPool extends Pool {
	/**
	 * @return bool
	 * @throws DbException
	 */
	public function ping() {
		$mysql = new Mysql($this->name);
		return $mysql->ping();
	}

	/**
	 * @throws DbException
	 */
	protected function create() {
		$mysql = new Mysql($this->name);
		$this->chan->push($mysql);
		$this->createNum++;
	}

	public function check($tickTime = 10000) {
		swoole_timer_tick($tickTime, function () {
			$len = $this->chan->length();
			for ($i = 0; $i < $len; $i++) {
				/**
				 * @var $cli Mysql
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
	 * @return Mysql|false
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
