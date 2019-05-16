<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2019-03-07
 * Time: 18:41
 */

namespace Zero\Co\Pool;

use Zero\Exception\PoolException;

class PoolManager {
	/**
	 * @var Pool[]
	 */
	protected static $map = [];

	/**
	 * @param string $poolClass
	 * @param string $name
	 * @param int    $poolSize    连接池大小
	 * @param int    $maxIdleTime //秒(s)
	 * @param float  $poolTimeout //秒(s)
	 * @throws PoolException
	 */
	public static function register(string $poolClass, string $name, $poolSize = 10, $maxIdleTime = 300, $poolTimeout = 0.1) {
		if (!class_exists($poolClass)) {
			throw new PoolException("Not found Pool Class[{$poolClass}]!");
		}
		/**
		 * @var $pool Pool
		 */
		try {
			$pool = new $poolClass($name, $poolSize, $maxIdleTime, $poolTimeout);
		} catch (\Throwable $e) {
			throw new PoolException("Register error: " . $e->getMessage());
		}
		if ($pool->ping()) {
			self::$map[$poolClass . '.' . $name] = $pool;
			return;
		}
		throw new PoolException("The pool [{$poolClass}][{$name}] ping error");
	}

	public static function init() {
		foreach (self::$map as $k => $pool) {
			self::$map[$k]->init();
		}
	}

	public static function check($tickTime = 10000) {
		foreach (self::$map as $k => $pool) {
			self::$map[$k]->check($tickTime);
		}
	}

	/**
	 * @param string $poolClass
	 * @param string $name
	 * @return Pool
	 */
	public static function pool($poolClass, $name) {
		return self::$map[$poolClass . '.' . $name];
	}

	/**
	 * @param string $poolClass
	 * @param string $name
	 */
	public static function reset($poolClass, $name) {
		self::$map[$poolClass . '.' . $name] = new $poolClass($name);
	}
}
