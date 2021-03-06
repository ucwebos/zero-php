<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2018/12/7
 * Time: 下午5:41
 */

namespace Zero\Console\Gen;

use Zero\Console\Cmd;
use Zero\Console\Helper;

/**
 * 生成器命令
 * Class Commend
 * @package Zero\Console\Gen
 */
class Command implements Cmd {
	const CMD = [
		'doc'    => [
			'args'  => [
				'[srv]' => '服务默认MAIN',
				'[api]' => '测试api地址',
			],
			'note'  => '生成文档',
			'class' => GenDoc::class,
		],
		'table'  => [
			'args'  => [
				'db=[database]' => '数据库',
				't=[model]'     => '模型 为ALL时生成所有',
			],
			'note'  => '数据库表生成 WIP...',
			'class' => GenTable::class,
		],
		'entity' => [
			'args'  => [
				'db=[database]' => '数据库',
				't=[model]'     => '表名 为ALL时生成所有',
			],
			'note'  => '数据表实体类生成',
			'class' => GenEntity::class,
		],
		'model'  => [
			'args'  => [
				'db=[database]' => '数据库',
				't=[model]'     => '表名 为ALL时生成所有',
				'--model=true'  => '可选 生成model文件 默认只生成Entity',
			],
			'note'  => '数据表模型生成',
			'class' => GenModel::class,
		],

	];

	protected function parseArgs($args) {
		$_args = [];
		foreach ($args as $item) {
			$arr = explode('=', trim($item));
			if (count($arr) != 2) {
				continue;
			}
			$_args[$arr[0]] = $arr[1];
		}
		return $_args;
	}

	public function exec($argv) {
		$cmd  = $argv['cmd'] ?? '';
		$conf = self::CMD[$cmd] ?? [];
		if (!$conf) {
			$str = "\e[31m 命令不正确！，请输入正确的命令\e[0m" . PHP_EOL . PHP_EOL;
			$str .= $this->help() . PHP_EOL;
			die($str);
		}
		$class = $conf['class'] ?? '';
		if (!$class) {
			$str = "\e[31m sys err! \e[0m" . PHP_EOL . PHP_EOL;
			die($str);
		}
		$gen  = new $class;
		$args = $argv['args'] ?? [];
		$args = $this->parseArgs($args);
		return call_user_func_array([$gen, 'gen'], [$args]);
	}

	public function help() {
		$commends = Helper::getCommends(self::CMD);
		$help     = <<<HELP
ZERO 控制台 [生成器命令]

\e[33m用法：\e[0m gen 子命令 [--help] [参数]

子命令列表：

{$commends}

HELP;
		return $help;
	}
}
