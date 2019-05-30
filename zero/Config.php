<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2018/12/6
 * Time: 下午1:57
 */

namespace Zero;

class Config {
	private static $config = [];
	private static $sets   = [];

	/**
	 * 初始化配置
	 */
	public static function load() {
		switch (CONF_TYPE) {
			case 'YAML':
				self::loadYAML();
				break;
			case 'PHP':
				self::loadPHP();
				break;
		}
	}

	/**
	 * 获取配置项
	 * @param string $node 节点名(aa.bb.cc)
	 * @return mixed
	 */
	public static function get($node) {
		if (!$node) {
			return NULL;
		}
		if (isset(self::$sets[$node])) {
			return self::$sets[$node];
		}
		if (!self::$config) {
			return NULL;
		}
		$result = self::$config;
		foreach (explode('.', $node) as $_node) {
			if (isset($result[$_node])) {
				$result = $result[$_node];
			} else {
				return NULL;
			}
		}
		return $result;
	}

	/**
	 * 设置配置节点项【最大5级】
	 * @param $key
	 * @param $value
	 * @return bool
	 */
	public static function setField($key, $value) {
		if (!self::$config) {
			return FALSE;
		}
		$keys  = explode(".", $key);
		$count = count($keys);
		if ($count > 5) {
			return FALSE;
		}
		switch ($count) {
			case 1:
				self::$config[$keys[0]] = $value;
				break;
			case 2:
				self::$config[$keys[0]][$keys[1]] = $value;
				break;
			case 3:
				self::$config[$keys[0]][$keys[1]][$keys[2]] = $value;
				break;
			case 4:
				self::$config[$keys[0]][$keys[1]][$keys[2]][$keys[3]] = $value;
				break;
			case 5:
				self::$config[$keys[0]][$keys[1]][$keys[2]][$keys[3]][$keys[4]] = $value;
				break;
		}
		return TRUE;
	}

	/**
	 * 获取所有配置
	 * @return array
	 */
	public static function getAll() {
		return self::$config;
	}

	/**
	 * 获取所有设置
	 * @return array
	 */
	public static function getSets() {
		return self::$sets;
	}

	/**
	 * 加载配置
	 */
	private static function loadPHP() {
		$env  = ENV;
		$file = CONF_DIR . "/$env.php";
		try {
			$config       = include "{$file}";
			self::$config = $config;
		} catch (\Throwable $e) {
			die("error config:" . $e->getMessage());
		}
	}

	/**
	 * 加载配置
	 */
	private static function loadYAML() {
		$env  = ENV;
		$file = CONF_DIR . "/$env.yml";
		try {
			$config = yaml_parse_file($file, -1);
			if ($config[0]) {
				self::$config = $config[0];
			}
			if (!self::$config) {
				throw new \Exception('empty config!');
			}
		} catch (\Throwable $e) {
			die("error yaml :" . $e->getMessage());
		}
	}
}
