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
	use Singleton;
	protected $instance = [];

	public function get($id) {
		return $this->instance[$id] ?? NULL;
	}

	public function has($id) {
		return isset($this->instance[$id]);
	}
}
