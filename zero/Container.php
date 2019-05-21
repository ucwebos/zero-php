<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2018/12/7
 * Time: 上午9:49
 */

namespace Zero;

use Psr\Container\ContainerInterface;
use Zero\Component\Singleton;

class Container implements ContainerInterface {
	private static $app;
	protected      $instance = [];

	static function app() {
		if (!isset(self::$app)) {
			self::$app = new self();
		}
		return self::$app;
	}

	public function get($id) {
		return $this->instance[$id] ?? NULL;
	}

	public function has($id) {
		return isset($this->instance[$id]);
	}

	public function set($id, $obj) {
		$this->instance[$id] = $obj;
	}
}
