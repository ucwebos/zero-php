<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2018/12/7
 * Time: 上午10:05
 */

namespace Zero\Console\Job;

use Zero\Log\Logger;

abstract class Job {
	protected $args = [];

	abstract public function run();
	/**
	 * @var Logger
	 */
	private $logger;

	public function exec(array $args) {
		try {
			$this->args = $args;
			$this->run();
		} catch (\Throwable $e) {
			echo date('Y-m-d H:i:s') . ' ---- ' . $e->getMessage() . PHP_EOL;
		}
	}

	/**
	 * @return Logger
	 */
	protected function logger() {
		if (!$this->logger) {
			$this->logger = new Logger();
		}
		return $this->logger;
	}
}
