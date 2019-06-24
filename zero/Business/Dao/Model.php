<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2018/12/6
 * Time: 下午7:13
 */

namespace Zero\Business\Dao;

use Zero\Contract;
use Zero\Co\Pool\PoolManager;
use Zero\Co\Pool\MysqlPool;
use Zero\Database\Mysql;
use Zero\Exception\PoolException;

class Model extends Contract {
	/**
	 * @var string db配置
	 */
	protected $db = "core";
	/**
	 * @var Mysql
	 */
	protected $conn;
	/**
	 * @var MysqlPool
	 */
	private $pool;

	/**
	 * @throws PoolException
	 */
	public static function conn() {
		return new static();
	}

	public function __construct() {
		if (isCo()) {
			$this->pool = PoolManager::pool(MysqlPool::class, $this->db);
			if ($this->pool == NULL) {
				throw new PoolException("mysql pool is null", PoolException::ERROR_POOL);
			}
			$this->conn = $this->pool->get();
			if ($this->conn == FALSE) {
				throw new PoolException("db pool get is false", PoolException::ERROR_GET);
			}
		} else {
			$this->conn = Mysql::getInstance($this->db);
		}
	}

	public function __destruct() {
		if (isCo()) {
			$this->pool->recycle($this->conn);
		}
	}

	protected function strIds(array $ids, $glue = ',') {
		$str = '';
		foreach ($ids as $i => $id) {
			$str .= $glue . "'$id'";
		}
		$str = substr($str, strlen($glue));
		return $str;
	}

	protected function strWhere(array $wheres) {
		$str = '1 ';
		foreach ($wheres as $k => $val) {
			$str .= " and $k=:$k";
		}
		return $str;
	}
}
