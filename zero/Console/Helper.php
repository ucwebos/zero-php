<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2018/12/7
 * Time: 下午5:48
 */

namespace Zero\Console;

use Zero\Config;

class Helper implements Cmd {
	const CMD = [
		'srv' => [
			'class' => Server\Command::class,
			'note'  => '服务管理'
		],
		'gen' => [
			'class' => Gen\Command::class,
			'note'  => '代码生成器'
		],
		'job' => [
			'class' => Job\Command::class,
			'note'  => '脚本任务 [JobClass] '
		],
	];

	public function __construct($env = '') {
		if ($env) {
			Config::setEnv($env);
		}
		Config::load();
	}

	public function exec($argv) {
		$argObj = new Args($argv);
		if (!$argObj->getCmd()) {
			exit($this->help() . PHP_EOL);
		}
		$ctl = NULL;
		try {
			$conf = self::CMD[$argObj->getCmd()] ?? [];
			if (!$conf) {
				throw  new \Exception("error cmd");
			}
			$class = $conf['class'] ?: '';
			/**
			 * @var $ctl Cmd
			 */
			$ctl = new $class;
		} catch (\Throwable $e) {
			$str = "\e[31m 命令不正确！，请输入正确的命令\e[0m" . PHP_EOL . PHP_EOL;
			$str .= $this->help() . PHP_EOL;
			die($str);
		}
		if (!$argObj->getSubCmd()) {
			if ($argObj->isHelp()) {
				$str = $ctl->help();
				if ($str) {
					exit($str . PHP_EOL);
				}
			}
			$str = "\e[31m 请输入子命令！\e[0m" . PHP_EOL . PHP_EOL;
			$str .= $ctl->help() . PHP_EOL;
			die($str);
		}
		$ctl->exec(['cmd' => $argObj->getSubCmd(), 'args' => $argObj->getArgs()]);
	}

	public static function getCommends(array $cmds) {
		$commends = "";
		foreach ($cmds as $cmd => $item) {
			$commends .= "\e[32m {$cmd}\e[0m  {$item['note']}" . PHP_EOL;
			if (isset($item['args'])) {
				foreach ($item['args'] as $arg => $node) {
					$commends .= "  参数：\e[34m {$arg}\e[0m  {$node}" . PHP_EOL;
				}
			}
		}
		return $commends;
	}

	public function help() {
		$commends = self::getCommends(self::CMD);
		$help     = <<<HELP
ZERO 控制台

\e[33m用法：\e[0m 命令 子命令 [--help] [参数]

命令列表：

{$commends}

HELP;
		return $help;
	}
}
