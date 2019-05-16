<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2018/12/6
 * Time: 下午4:49
 */

namespace Zero\Component;

trait Singleton {
	private static $instance;

	static function getInstance(...$args) {
		if (!isset(self::$instance)) {
			self::$instance = new static(...$args);
		}
		return self::$instance;
	}
}
