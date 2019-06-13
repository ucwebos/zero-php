<?php

namespace App\Utils;

/**
 * 同步阻塞方法类
 * Class SyncFunc
 * @method $this setTimeout(int $timeout) //s
 * @package App\Utils
 */
class SyncCall {
	public function test() {
		sleep(1);
		return 'ok';
	}
}
