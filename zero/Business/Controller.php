<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2018/12/6
 * Time: 下午3:28
 */

namespace Zero\Business;

use Zero\Contract;
use Zero\Business\Http\Request;
use Zero\Business\Http\Response;

class Controller extends Contract {
	/**
	 * @var Request
	 */
	protected $request;
	/**
	 * @var Response
	 */
	protected $response;

	public function __construct(Request $request, Response &$response) {
		$this->request  = $request;
		$this->response = $response;
	}

}
