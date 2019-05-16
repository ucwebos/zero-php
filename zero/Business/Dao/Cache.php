<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2018/12/6
 * Time: 下午7:13
 */

namespace Zero\Business\Dao;

use Zero\Contract;
use Zero\Co\Pool\PoolManager;
use Zero\Co\Pool\RedisPool;
use Zero\Database\Redis;
use Zero\Exception\PoolException;

class Cache extends Contract {
	/**
	 * @var string db配置
	 */
	protected $redis = "cache";
	/**
	 * @var \Redis
	 */
	protected $conn;
	/**
	 * @var
	 */
	protected $pool;

	/**
	 * Model constructor.
	 */
	public function __construct() {
		if ($this->isCo()) {
			$this->pool = PoolManager::pool(RedisPool::class, $this->redis);
			if ($this->pool == NULL) {
				throw new PoolException("redis pool is null", PoolException::ERROR_POOL);
			}
			$this->conn = $this->pool->get();
			if ($this->conn == FALSE) {
				throw new PoolException("redis pool get is false", PoolException::ERROR_GET);
			}
		} else {
			$this->conn = Redis::getInstance($this->redis);
		}
	}

	public function __destruct() {
		if ($this->isCo()) {
			$this->pool->recycle($this->conn);
		}
	}
}
