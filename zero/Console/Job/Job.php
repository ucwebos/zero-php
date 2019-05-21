<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2018/12/7
 * Time: 上午10:05
 */

namespace Zero\Console\Job;

abstract class Job {
	protected $args = [];

	abstract public function run();

	public function exec(array $args) {
		try {
			$this->args = $args;
			$this->run();
		} catch (\Throwable $e) {
			echo date('Y-m-d H:i:s') . ' ---- ' . $e->getMessage() . PHP_EOL;
		}
	}
}
