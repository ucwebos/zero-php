<?php

namespace App\Utils;

use Zero\Co\SyncProxy;

class Async extends SyncProxy {
	protected $class = SyncCall::class;
	/**
	 * @return SyncCall
	 */
	public static function syncCall() {
		return new self();
	}
}
