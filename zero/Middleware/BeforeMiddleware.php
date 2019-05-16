<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2018/12/6
 * Time: 下午1:56
 */

namespace Zero\Middleware;

use Zero\Business\Http\Request;

abstract class BeforeMiddleware {
	/**
	 * @param Request $request
	 * @return Request|MiddlewareRejected
	 */
	abstract public function handle(Request $request);
}
