<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2018/12/6
 * Time: 下午1:59
 */

namespace Zero\Database;

use Zero\Config;
use Zero\Exception\DbException;

/**
 * Class Redis
 * @package Zero\Database
 */
class Redis {
	static private $instances = [];
	private        $config    = [];
	private        $redis     = NULL;
	private        $name      = '';
	public         $lastActiveTime;

	/**
	 * @param $name
	 * @return \Redis
	 * @throws DbException
	 */
	public static function getInstance($name) {
		if (!isset(self::$instances[$name])) {
			self::$instances[$name] = new self($name);
		}
		return self::$instances[$name];
	}

	/**
	 * Redis constructor.
	 * @param $name
	 * @throws DbException
	 */
	public function __construct($name) {
		$this->name   = $name;
		$this->config = Config::get('REDIS.' . $name) ?? [];
		if (!$this->config) {
			throw new DbException("REDIS.{$name}", "Error config", DbException::ERROR_CONFIG);
		}
		$ip   = $this->config['host'] ?? '';
		$port = $this->config['port'] ?? '';
		$auth = $this->config['auth'] ?? '';

		$redis = new \Redis();
		$bool  = $redis->connect($ip, $port);
		if ($bool && $auth) {
			$bool = $redis->auth($auth);
		}
		if (!$bool) {
			throw new DbException("REDIS.{$name}", "Error to conn", DbException::ERROR_CONN);
		}
		$this->redis = $redis;
	}

	/**
	 * @return bool
	 * @throws DbException
	 */
	public function reconnect() {
		$ip   = $this->config['host'] ?? '';
		$port = $this->config['port'] ?? '';
		$auth = $this->config['auth'] ?? '';
		$bool = $this->redis->connect($ip, $port);
		if ($bool && $auth) {
			return $this->redis->auth($auth);
		}
		if (!$bool) {
			throw new DbException("REDIS.{$this->name}", "Error to reconnect REDIS.{$this->name}", DbException::ERROR_CONN);
		}
	}

	/**
	 * @return bool
	 */
	public function ping() {
		try {
			if ('+PONG' == $this->redis->ping()) {
				return TRUE;
			}
		} catch (\Exception $e) {
			return FALSE;
		}
		return FALSE;
	}

	/**
	 * @param $name
	 * @param $arguments
	 * @return bool|mixed
	 * @throws \Exception
	 */
	public function __call($name, $arguments) {
		try {
			$this->lastActiveTime = time();
			return call_user_func_array([$this->redis, $name], $arguments);
		} catch (\Exception $e) {
			throw new DbException("REDIS.{$this->name}", $e->getMessage(), DbException::ERROR_EXEC, $e);
		}
	}
}
