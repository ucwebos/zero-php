<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2019-03-06
 * Time: 15:23
 */

namespace App\Http;

use Zero\Co\SyncCo;


class Test extends BaseController {
	protected $service;

	public function t() {
		$p1 = $this->request->getParam('p1', '');
		return ['p1' => $p1];
	}

	public function t2() {
		return ['rt2' => $this->request->params()];
	}

	public function t3() {

		SyncCo::proxy()->setTimeout(3)->test();
		SyncCo::proxy()->setTimeout(100)->test();

		return ["r" => 't3'];
	}
}
