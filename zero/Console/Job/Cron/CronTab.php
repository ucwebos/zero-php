<?php

namespace Zero\Console\Job\Cron;

class CronTab {
	private $tasks = [];

	public function getTasks() {
		return $this->tasks;
	}

	public function add(CronRule $rule, $task) {
		$this->tasks[] = [$rule, $task];
	}
}
