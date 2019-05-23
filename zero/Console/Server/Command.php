<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2018/12/7
 * Time: 上午10:05
 */

namespace Zero\Console\Server;

use Zero\Console\Cmd;
use Zero\Console\Helper;

class Command implements Cmd {
	const CMD = [
		'start'   => [
			'note' => '启动服务',
		],
		'daemon'  => [
			'note' => '启动服务[守护进程模式]',
		],
		'reload'  => [
			'note' => '热重启 [仅适用于多进程模式]',
		],
		'restart' => [
			'note' => '重启服务',
		],
		'stop'    => [
			'note' => '停止服务',
		],
		'kill'    => [
			'note' => '停止服务[强制]',
		]
	];

	public function exec($argv) {
		$manager = new Manager();
		$cmd     = $argv['cmd'] ?? '';
		$args    = $argv['args'] ?? [];
		echo $cmd.PHP_EOL;
		if (!$cmd || !method_exists($manager, $cmd)) {
			$str = "\e[31m 命令不正确！，请输入正确的命令\e[0m" . PHP_EOL . PHP_EOL;
			$str .= $this->help() . PHP_EOL;
			die($str);
		}
		return call_user_func_array([$manager, $cmd], $args);
	}

	public function help() {
		$commends = Helper::getCommends(self::CMD);
		$help     = <<<HELP
ZERO 控制台 [服务管理命令]

\e[33m用法：\e[0m srv 子命令 [--help] [参数]

子命令列表：

{$commends}

HELP;
		return $help;
	}
}
