<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2018/12/6
 * Time: 下午4:43
 */

namespace Zero\Co;

use Swoole\Coroutine\Channel;

class EventCo {
	protected $chan;

	public function __construct($chanSize, $procSize) {
		$this->chan = new Channel($chanSize);
		for ($i = 0; $i < $procSize; $i++) {
			go(function () {
				while (TRUE) {
					$callback = $this->chan->pop();
					call_user_func($callback);
				}
			});
		}
	}

	public function add(callable $callback) {
		$this->chan->push($callback);
	}

	public function stats() {
		return $this->chan->stats();
	}
}
