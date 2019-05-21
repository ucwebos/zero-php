<?php

namespace App\Command;

use Zero\Console\Job\Job;

class Test extends Job {
	public function run() {
		echo 'CommandTest'.PHP_EOL;
	}
}
