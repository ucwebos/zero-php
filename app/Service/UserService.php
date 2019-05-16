<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2018/12/6
 * Time: 下午4:44
 */

namespace App\Service;

use App\Dao\Model\UserModel;

use Zero\Business\Service as BaseService;

class UserService extends BaseService {


	public function getUserInfo() {

		$r =  UserModel::conn()->get();

	}
}
