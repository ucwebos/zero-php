<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2018/12/6
 * Time: 下午1:56
 */

namespace Zero\Middleware;

use Zero\Contract;
use Zero\Business\Http\Request;

abstract class BeforeMiddleware extends Contract {
	/**
	 * @param Request $request
	 * @return Request|MiddlewareRejected
	 */
	abstract public function handle(Request $request);
}
