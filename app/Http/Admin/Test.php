<?php

namespace App\Http\Admin;

use App\Http\BaseController;

class Test extends BaseController {
	public function t2(){

		return ['r-admin'=>$this->request->params()];
	}

}
