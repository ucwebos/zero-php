<?php

namespace Zero\Console\Job\Cron;

use Zero\Console\Job\Job;

class CronManager {

	public function start(CronTab $tab){
		$tasks = $tab->getTasks();
		foreach ($tasks as [$rule, $task]){

		}
	}


	public function isLock(){

	}

	public function parse(){

	}

	public function isTime(){

		return TRUE;
	}

	public function run($task){
		try {
			(new $task)->run();
		}catch (\Throwable $e){
			logger()->info('');
		}
	}
}
