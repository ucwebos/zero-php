<?php

namespace Zero\Co;

use App\Utils\SyncCall;

class SyncCo extends SyncProxy {
	protected $class = SyncCall::class;
	/**
	 * @return SyncCall
	 */
	public static function proxy() {
		return new self();
	}
}
