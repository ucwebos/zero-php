<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2018/12/6
 * Time: 下午3:35
 */

namespace App;

use Zero\Co\Pool\MysqlPool;
use Zero\Co\Pool\PoolManager;
use Zero\Co\Pool\RedisPool;
use Zero\Container;
use Zero\IBootstrap;
use Zero\Route\Route;
use Swoole\Runtime;

class Bootstrap implements IBootstrap {
	/**
	 * 配置路由
	 * @param Route $r
	 * @return Route
	 */
	public function route(Route $r): Route {
		//统一参数解析中间件
		$r->middleware(['JsonParams'],function (Route $r){


			$r->any('/', function () {
				return "welcome!";
			});
			$r->get('/test', 'Test::t');

			$r->any('/t1', 'Test::t');

			$r->group('/admin', function (Route $r) {
				$r->post('/t2', 'Test::t2');
			});

			$r->group('/admin', function (Route $r) {
				$r->post('/t22', 'Test::t2');
			},'Admin',['Auth']);



		});


		$r->get('/t3', 'Test::t3');


		return $r;
	}

	/**
	 * 服务启动前的初始化配置
	 * @return mixed|void
	 * @throws \Exception
	 */
	public function init() {
		if (PHP_SAPI == 'cli') {
			//设定是在协程环境
			define('COROUTINE_SERVER', TRUE);
			//开启协程hook
			Runtime::enableCoroutine(TRUE);

			//初始化连接池
			PoolManager::register(MysqlPool::class, 'core', 50, 300, 0.1);
			PoolManager::register(RedisPool::class, 'cache', 50, 300, 0.1);
			PoolManager::init();
			//初始化管道event等

		}
		// 可选
		// 注册默认LoggerWriter ...
		//	Container::app()->set('LoggerWriter',new ElkWriter());
		// 注册类到app容器 ...

	}

	/**
	 * 启动后的初始化
	 * @return mixed|void
	 */
	public function start() {
		if (PHP_SAPI == 'cli') {
			//连接池定时检测
			PoolManager::check(10000);
		}
	}
}
