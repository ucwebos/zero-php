<?php

namespace App\Middleware;

use Zero\Business\Http\Request;
use Zero\Middleware\BeforeMiddleware;

class JsonParams extends BeforeMiddleware {
	public function handle(Request $request) {
		$raw    = $request->getRaw();
		$params = json_decode($raw,TRUE) ?? [];
		$request->setParams($params);
		return $request;
	}
}
