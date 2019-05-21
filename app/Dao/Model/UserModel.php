<?php

namespace App\Dao\Model;

use Zero\Business\Dao\Model;
use Zero\Exception\DbException;
use App\Dao\Entity\UserEntity;

class UserModel extends Model {
	protected $db = 'core';

	/**
	 * @param array $uids
	 * @return UserEntity[]
	 * @throws DbException
	 */
	public function findList(array $uids) {
		if (!$uids) {
			return [];
		}
		$SQL  = sprintf("select * from %s where uid in (%s)", UserEntity::TABLE, $this->strIds($uids));
		$data = $this->conn->getAll($SQL);
		if (!$data) {
			return [];
		}
		$users = [];
		foreach ($data as $item) {
			$users[$item['uid']] = new UserEntity($item);
		}
		return $users;
	}

	/**
	 * @param $uid
	 * @return UserEntity|array
	 * @throws DbException
	 */
	public function find($uid) {
		if (!$uid) {
			return [];
		}
		$SQL = sprintf("select * from %s where uid=:uid", UserEntity::TABLE);
		$row = $this->conn->getRow($SQL, [
			'uid' => $uid
		]);
		if (!$row) {
			return [];
		}
		$user = new UserEntity($row);
		return $user;
	}

	/**
	 * @param UserEntity $user
	 * @param bool       $returnId
	 * @return bool|int
	 * @throws DbException
	 */
	public function insert(UserEntity $user, $returnId = FALSE) {
		$result = $this->conn->insert(UserEntity::TABLE, $user->toArray());
		if ($returnId) {
			return intval($this->conn->lastInsertId());
		}
		return $result;
	}

	/**
	 * @param              $uid
	 * @param UserEntity   $user
	 * @return bool
	 * @throws DbException
	 */
	public function update($uid, UserEntity $user) {
		$result = $this->conn->update(UserEntity::TABLE, $user->toArray(), ['uid' => $uid]);
		return $result;
	}

	/**
	 * @param UserEntity $user
	 * @return array
	 * @throws DbException
	 */
	public function findBy(UserEntity $user) {
		$wheres   = $user->toArray();
		$whereStr = $this->strWhere($wheres);
		$SQL      = sprintf("select * from %s where %s ", UserEntity::TABLE, $whereStr);
		$data     = $this->conn->getColumn($SQL, $wheres);
		return $data;
	}
}
