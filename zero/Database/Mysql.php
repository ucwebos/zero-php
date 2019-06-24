<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2018/12/6
 * Time: 下午1:57
 */

namespace Zero\Database;

use Zero\Exception\DbException;
use Zero\Config;

class Mysql {
	static private $instances = [];
	protected      $dbName;
	protected      $config;
	/**
	 * @var \PDO
	 */
	protected $pdo;
	/**
	 * @var \PDOStatement
	 */
	protected $stmt;
	public    $lastActiveTime;

	/**
	 * @param $name
	 * @return Mysql
	 * @throws \Exception
	 */
	public static function getInstance($name) {
		if (!isset(self::$instances[$name])) {
			self::$instances[$name] = new self($name);
		}
		return self::$instances[$name];
	}

	/**
	 * Mysql constructor.
	 * @param       $dbName
	 * @param array $options
	 * @throws DbException
	 */
	public function __construct($dbName, $options = []) {
		$this->dbName = $dbName;
		$this->config = Config::get('MYSQL.' . $dbName) ?? [];
		if (!$this->config) {
			throw new DbException("DB.$dbName", "Error config MYSQL", DbException::ERROR_CONFIG);
		}
		$this->config['options'] = $options + [
				\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
				\PDO::ATTR_ORACLE_NULLS       => \PDO::NULL_TO_STRING,
				\PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
				\PDO::ATTR_STRINGIFY_FETCHES  => FALSE,
				//				\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => FALSE
			];

		$this->config['dsn'] = "mysql:host={$this->config['host']};dbname={$this->config['database']};port={$this->config['port']}";
		try {
			$this->pdo = new \PDO($this->config['dsn'], $this->config['user'], $this->config['password'], $this->config['options']);
		} catch (\PDOException $e) {
			throw new DbException("DB.$dbName", $e->getMessage(), DbException::ERROR_CONN, $e);
		}
		$this->lastActiveTime = time();
	}

	/**
	 * @return bool
	 */
	public function ping() {
		try {
			$this->pdo->getAttribute(\PDO::ATTR_SERVER_INFO);
		} catch (\PDOException $e) {
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * @return bool
	 */
	public function reconnect() {
		try {
			$this->pdo = new \PDO($this->config['dsn'], $this->config['user'], $this->config['password'], $this->config['options']);
		} catch (\PDOException $e) {
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * @param $sql
	 * @return false|\PDOStatement
	 */
	public function query($sql) {
		return $this->pdo->query($sql, \PDO::FETCH_ASSOC);
	}

	/**
	 * @param       $sql
	 * @param int   $from
	 * @param int   $limit
	 * @param array $params
	 * @return array
	 * @throws DbException
	 */
	public function getLimit($sql, $from = 0, $limit = 20, $params = []) {
		$params['__from']  = (int) $from;
		$params['__limit'] = (int) $limit;
		$sql               .= ' limit :__from,:__limit';
		return $this->getAll($sql, $params);
	}

	/**
	 * @param       $sql
	 * @param int   $page
	 * @param int   $size
	 * @param array $params
	 * @return array
	 * @throws DbException
	 */
	public function getPage($sql, $page = 1, $size = 20, $params = []) {
		$start = ($page - 1) * $size;
		return $this->getLimit($sql, $start, $size, $params);
	}

	/**
	 * @param string $tableName
	 * @param array  $where
	 * @return array
	 * @throws DbException
	 */
	public function getWhere($tableName, $where) {
		$sql = "select * from {$tableName} where 1";
		foreach ($where as $k => $val) {
			$sql .= " and $k=:$k";
		}
		return $this->getAll($sql, $where);
	}

	/**
	 * @param string $tableName
	 * @param array  $where
	 * @param int    $from
	 * @param int    $limit
	 * @return array
	 * @throws DbException
	 */
	public function getWhereLimit($tableName, $where, $from = 0, $limit = 20) {
		$sql = "select * from {$tableName} where 1";
		foreach ($where as $k => $val) {
			$sql .= " and $k=:$k";
		}
		return $this->getLimit($sql, $from, $limit, $where);
	}

	/**
	 * 获取一个记录
	 * @param string $sql    SQL语句
	 * @param array  $params 绑定参数
	 * @param int    $column 列号
	 * @return string
	 * @throws DbException
	 */
	public function getOne($sql, $params = [], $column = 0) {
		return $this->execute($sql, $params) ? $this->stmt->fetchColumn($column) : '';
	}

	/**
	 * 获取一列记录
	 * @param string $sql    SQL语句
	 * @param array  $params 绑定参数
	 * @param int    $column 列号
	 * @return array
	 * @throws DbException
	 */
	public function getColumn($sql, $params = [], $column = 0) {
		$data = [];
		if ($this->execute($sql, $params)) {
			while ($one = $this->stmt->fetchColumn($column)) {
				$data[] = $one;
			}
		}
		return $data;
	}

	/**
	 * 获取全部记录
	 * @param string $sql    SQL语句
	 * @param array  $params 绑定参数
	 * @return array
	 * @throws DbException
	 */
	public function getAll($sql, $params = []) {
		return $this->execute($sql, $params) ? $this->stmt->fetchAll(\PDO::FETCH_ASSOC) : [];
	}

	/**
	 * 获取一行记录
	 * @param string $sql    SQL语句
	 * @param array  $params 绑定参数
	 * @return array
	 * @throws DbException
	 */
	public function getRow($sql, $params = []) {
		return $this->execute($sql, $params) ? ($this->stmt->fetch(\PDO::FETCH_ASSOC) ?: []) : [];
	}

	/**
	 * 插入一行记录
	 * @param string $tableName  表名
	 * @param array  $data       绑定参数
	 * @param string $sqlKeyword SQL关键词
	 * @return bool
	 * @throws DbException
	 */
	public function insert($tableName, array $data, $sqlKeyword = '') {
		if (!is_array($data) || empty($data)) {
			return FALSE;
		}
		if ($sqlKeyword && !in_array(strtoupper($sqlKeyword), ['LOW_PRIORITY', 'DELAYED', 'HIGH_PRIORITY', 'IGNORE'])) {
			$sqlKeyword = '';
		}
		$keys   = array_keys($data);
		$cols   = '`' . implode('`,`', $keys) . '`';
		$values = ':' . implode(',:', $keys);
		$sql    = "insert {$sqlKeyword} into {$tableName} ({$cols}) values ({$values})";

		return $this->execute($sql, $data);
	}

	/**
	 * 插入一行记录存在则更新
	 * @param       $tableName
	 * @param array $data
	 * @param array $update
	 * @return bool
	 * @throws DbException
	 */
	public function insertUpdate($tableName, array $data, array $update) {
		if (!is_array($data) || empty($data)) {
			return FALSE;
		}
		$keys   = array_keys($data);
		$cols   = '`' . implode('`,`', $keys) . '`';
		$values = ':' . implode(',:', $keys);
		$sets   = [];
		foreach ($update as $k => $v) {
			$sets[] = '`' . $k . '`' . '=:' . $k;
		}
		$sets = implode(',', $sets);
		$sql  = "insert into {$tableName} ({$cols}) values ({$values}) ON DUPLICATE KEY UPDATE {$sets}";

		return $this->execute($sql, array_merge($data, $update));
	}

	/**
	 * 替换一行记录
	 * @param string $tableName 表名
	 * @param array  $data      绑定参数
	 * @return bool
	 * @throws DbException
	 */
	public function replace($tableName, $data) {
		if (!is_array($data) || empty($data)) {
			return FALSE;
		}
		$keys   = array_keys($data);
		$cols   = '`' . implode('`,`', $keys) . '`';
		$values = ':' . implode(',:', $keys);
		$sql    = "replace into {$tableName} ({$cols}) values ({$values})";

		return $this->execute($sql, $data);
	}

	/**
	 * 更新数据
	 * @param string $tableName 表名
	 * @param array  $data      绑定参数
	 * @param array  $where     筛选条件
	 * @return bool
	 * @throws DbException
	 */
	public function update($tableName, $data, $where = []) {
		if (!is_array($data) || empty($data)) {
			return FALSE;
		}
		$sets = $wheres = [];
		foreach ($data as $k => $v) {
			$sets[] = '`' . $k . '`' . '=:' . $k;
		}
		foreach ($where as $k => $v) {
			$wheres[] = '`' . $k . '`' . '=:' . $k;
		}
		$sets   = implode(',', $sets);
		$wheres = implode(' and ', $wheres);
		$sql    = "update {$tableName} set {$sets} where {$wheres}";

		return $this->execute($sql, array_merge($data, $where));
	}

	/**
	 * 删除数据
	 * @param string $tableName 表名
	 * @param array  $where     筛选条件
	 * @return bool
	 * @throws DbException
	 */
	public function delete($tableName, $where) {
		if (!is_array($where) || empty($where)) {
			return FALSE;
		}
		$wheres = [];
		foreach ($where as $k => $v) {
			$wheres[] = $k . '=:' . $k;
		}
		$sql = "delete from {$tableName} where " . implode(' and ', $wheres);
		return $this->execute($sql, $where);
	}

	/**
	 * 执行SQL
	 * @param string $sql    SQL语句
	 * @param array  $params 绑定参数
	 * @return bool
	 * @throws DbException
	 */
	public function execute($sql, $params = []) {
		try {
			$this->stmt = $this->pdo->prepare($sql);
			foreach ($params as $k => &$v) {
				if (is_null($v)) {
					$this->stmt->bindParam($k, $v, \PDO::PARAM_NULL);
				} elseif (is_int($v)) {
					$this->stmt->bindParam($k, $v, \PDO::PARAM_INT);
				} else {
					$this->stmt->bindParam($k, $v, \PDO::PARAM_STR);
				}
			}
			$ret = $this->stmt->execute();
		} catch (\PDOException $e) {
			if ($e->getCode() == 'HY000') {
				$this->reconnect();
			}
			throw new DbException("DB.$this->dbName", $e->getMessage(), DbException::ERROR_EXEC, $e);
		}
		$this->lastActiveTime = time();
		return $ret;
	}

	/**
	 * 返回受上一个 SQL 语句影响的行数
	 * @return int
	 */
	public function rowCount() {
		return $this->stmt->rowCount();
	}

	/**
	 * 获取跟上一次语句句柄操作相关的 SQLSTATE
	 * @return string
	 */
	public function errorCode() {
		return $this->stmt->errorCode();
	}

	/**
	 * 获取跟上一次语句句柄操作相关的扩展错误信息
	 * @return array
	 */
	public function errorInfo() {
		return $this->stmt->errorInfo();
	}

	/**
	 * 返回最后插入行的ID或序列值
	 * @param $name
	 * @return string
	 */
	public function lastInsertId($name = NULL) {
		return $this->pdo->lastInsertId($name);
	}

	/**
	 * 启动一个事务
	 * @return bool
	 */
	public function beginTransaction() {
		return $this->pdo->beginTransaction();
	}

	/**
	 * 提交一个事务
	 * @return bool
	 */
	public function commit() {
		return $this->pdo->commit();
	}

	/**
	 * 回滚一个事务
	 * @return bool
	 */
	public function rollBack() {
		return $this->pdo->rollBack();
	}

	/**
	 * 检查是否在一个事务内
	 * @return bool
	 */
	public function inTransaction() {
		return $this->pdo->inTransaction();
	}
}
