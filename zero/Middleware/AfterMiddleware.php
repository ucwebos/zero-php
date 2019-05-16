<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2018/12/6
 * Time: 下午1:56
 */

namespace Zero\Middleware;

use Zero\Business\Http\Request;
use Zero\Business\Http\Response;

abstract class AfterMiddleware {
	/**
	 * @param Request  $request
	 * @param Response $response
	 * @return Response|MiddlewareRejected
	 */
	abstract public function handle(Request $request, Response $response);
}
