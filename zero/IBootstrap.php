<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2019-03-06
 * Time: 14:04
 */

namespace Zero;

use Zero\Route\Route;

abstract class IBootstrap {
	/**
	 * 控制器命名空间
	 * @var string
	 */
	public $namespace = '';

	/**
	 * 服务路由注册
	 * @param Route $r
	 * @return mixed
	 */
	abstract public function route(Route $r): Route;

	/**
	 * 服务启动前的初始化配置
	 * @return mixed
	 */
	abstract public function init();

	/**
	 * 服务启动后执行的操作
	 * @return mixed
	 */
	abstract public function start();
}
