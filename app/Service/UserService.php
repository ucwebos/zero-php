<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2018/12/6
 * Time: 下午4:44
 */

namespace App\Service;

use Zero\Business\Service;
use Zero\Exception\PoolException;
use Zero\Exception\DbException;
use App\Dao\Entity\UserEntity;
use App\Dao\Model\UserModel;

class UserService extends Service {
	/**
	 * @param $uid
	 * @return UserEntity|array
	 * @throws PoolException|DbException
	 */
	public function getUserInfo($uid) {
		return UserModel::conn()
			->find($uid);
	}
}
