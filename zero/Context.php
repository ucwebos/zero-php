<?php

namespace Zero;

use Swoole\Coroutine;
use Zero\Component\Singleton;

class Context {
	use Singleton;
	private $values = [];
	private $isCo   = FALSE;

	public function __construct() {
		if (PHP_SAPI == 'cli' && defined('COROUTINE_SERVER')) {
			$this->isCo = TRUE;
		}
	}

	public function has($key) {
		if ($this->isCo) {
			return isset(Coroutine::getContext()[$key]);
		}
		return isset($this->values[$key]);
	}

	public function set($key, $value) {
		if ($this->isCo) {
			Coroutine::getContext()[$key] = $value;
		}
		$this->values[$key] = $value;
	}

	public function get($key) {
		if ($this->isCo) {
			return Coroutine::getContext()[$key];
		}
		return $this->values[$key];
	}

	public function clean() {
		if (!$this->isCo) {
			return $this->values = [];
		}
	}
}
