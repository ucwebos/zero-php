<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2018/12/7
 * Time: 下午5:49
 */

namespace Zero\Console\Job;

use Zero\Console\Cmd;

class Commend implements Cmd {
	protected $namespace = '\\App\\Command';

	public function exec($argv) {
		$cmd  = $argv['cmd'] ?? '';
		$args = $argv['args'] ?? [];
		$className = $this->namespace . '\\' . trim(str_replace('.', '\\', $cmd), '\\');
		if(!class_exists($className)){
			die("\e[31m Job类不存在！，请输入正确的Job类名\e[0m" . PHP_EOL);
		}
		(new $className())->exec($args);
	}

	public function help() {
		$help = <<<HELP
ZERO 控制台 [脚本任务管理命令]

\e[33m用法：\e[0m job 任务类 [参数]
c
HELP;
		return $help;
	}
}
