<?php

namespace App\Dao\Entity;

use Zero\Business\Dao\Entity;

/**
 * Class UserEntity
 * @property int    uid   ID
 * @property string name  昵称
 * @property string icon  头像
 * @property int    state 状态
 * @property string create_time
 * @property string update_time
 */
class UserEntity extends Entity {
	const TABLE = 'user';
	protected $type = [
		'uid'         => self::INT,
		'name'        => self::STRING,
		'icon'        => self::STRING,
		'state'       => self::INT,
		'create_time' => self::STRING,
		'update_time' => self::STRING,
	];
}
