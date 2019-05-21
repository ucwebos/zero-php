<?php

namespace App\Middleware;

use Zero\Business\Http\Request;
use Zero\Business\Http\Response;
use Zero\Middleware\AfterMiddleware;

class Monitor extends AfterMiddleware {

	public function handle(Request $request, Response $response) {
		// TODO: Implement handle() method.
		return $response;
	}
}
