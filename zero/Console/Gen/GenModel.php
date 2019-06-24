<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2018/12/7
 * Time: 上午11:54
 */

namespace Zero\Console\Gen;

use Zero\Database\Mysql;
use Zero\Config;

/**
 * Controller生成器
 * Class GenCode
 * @package Zero\Console\Gen
 */
class GenModel implements GenInterface {
	/**
	 * @var Mysql
	 */
	protected $conn;
	protected $db;
	protected $outPath;
	protected $withModel;
	protected $modelOutPath;

	public function gen($args) {
		$table           = $args['t'] ?? '';
		$this->db        = $args['db'] ?? '';
		$this->withModel = boolval($args['--model'] ?? FALSE);
		if (!$this->db || !$table) {
			die("Please input args like db={database} t={table or ALL} ");
		}
		try {
			$this->conn = new Mysql($this->db);
		} catch (\Throwable $e) {
			die("Generating err: " . $e->getMessage());
		}
		$this->outPath      = ROOT_PATH . '/app/Dao/Entity';
		$this->modelOutPath = ROOT_PATH . '/app/Dao/Model';
		if (!file_exists($this->outPath)) {
			die(" No such directory in {$this->outPath} ");
		}
		if ($table == 'ALL') {
			$tables = $this->getAllTables();
		} else {
			$tables = [$table];
		}
		$dbConf   = Config::get('MYSQL.' . $this->db) ?? [];
		$database = $dbConf['database'] ?? $this->db;
		foreach ($tables as $_table) {
			echo "Start generating structure for database table >> " . $database . '-----' . $_table . PHP_EOL;
			$this->genEntity($database, $_table);
		}
	}

	protected function getAllTables() {
		return $this->conn->getColumn("show tables");
	}

	protected function genEntity($database, $table) {
		$className  = implode('', array_map('ucwords', explode('_', $table)));
		$entityName = $className . 'Entity';
		$data       = $this->conn->getAll("select COLUMN_NAME,COLUMN_TYPE,COLUMN_KEY,COLUMN_DEFAULT,COLUMN_COMMENT,EXTRA,IS_NULLABLE from information_schema.columns where table_schema='{$database}' and table_name='{$table}' ORDER BY ORDINAL_POSITION");
		$typeArr    = "";
		$notes      = "";
		$priKey     = '';
		foreach ($data as $item) {

			$column     = $item['COLUMN_NAME'];
			$columnKey  = $item['COLUMN_KEY'];
			$columnType = $item['COLUMN_TYPE'];
			$default    = $item['COLUMN_DEFAULT'];
			$comment    = $item['COLUMN_COMMENT'];
			$extra      = $item['EXTRA'];
			$isNullable = $item['IS_NULLABLE'] == 'YES' ? 'TRUE' : 'FALSE';

			if (!$priKey && $columnKey == 'PRI') {
				$priKey = $column;
			}

			if ($extra) {
				$isNullable = 'TRUE';
			}
			$type = "string";
			if (strpos($columnType, 'int') !== FALSE) {
				$type = "int";
			}
			if (in_array($columnType, ['float', 'double', 'decimal'])) {
				$type = "float";
			}
			$typeUp = strtoupper($type);

			$notes .= "* @property {$type} {$column} {$comment}
 ";
			// 字段属性
			$typeArr .= "
		'{$column}' => self::$typeUp,";
		}

		$type = "[{$typeArr}
	]";

		$class      = "<?php

namespace App\Dao\Entity;

use Zero\Business\Dao\Entity;

/**
 * Class $entityName
 {$notes}
 */
class {$entityName} extends Entity {
	
	const TABLE = '{$table}';

	protected \$type = {$type};

	
}";
		$entityFile = $this->outPath . "/{$entityName}.php";
		if (file_exists($entityFile)) {
			$fileStr = file_get_contents($entityFile);
			preg_match('/protected\s*\$type\s*=\s*([^;]+)\s*;/s', $fileStr, $mh);
			$typeOld = $mh[1] ?? '';
			preg_match("/\*\s*Class\s*{$entityName}\s*([^\/]+)\*\//s", $fileStr, $mh2);
			$nodeOld = $mh2[1] ?? '';
			//替换字段
			$class = str_replace($typeOld, $type, $fileStr);
			//替换注释
			$class = str_replace($nodeOld, $notes, $class);

			file_put_contents($entityFile, $class);
		} else {
			file_put_contents($entityFile, $class);
		}

		if ($this->withModel) {
			echo "Start generating model for table >> " . $database . '-----' . $table . PHP_EOL;
			$this->genModel($className, $priKey);
		}
	}

	protected function genModel($className, $priKey) {
		//未设置主健不能生成
		if (!$priKey) {
			return;
		}

		$varName    = lcfirst($className);
		$entityName = $className . 'Entity';
		$modelName  = $className . 'Model';

		$model = <<<CODE
<?php

namespace App\Dao\Model;

use Zero\Business\Dao\Model;
use Zero\Exception\DbException;
use App\Dao\Entity\\{$entityName};

class {$modelName} extends Model {
	protected \$db = '{$this->db}';
	

	/**
	 * @param array \${$priKey}s
	 * @return {$entityName}[]
	 * @throws DbException
	 */
	public function findList(array \${$priKey}s) {
		if(!\${$priKey}s){
			return [];
		}
		\$SQL = sprintf("select * from %s where {$priKey} in (%s)", {$entityName}::TABLE, \$this->strIds(\${$priKey}s));
		\$data = \$this->conn->getAll(\$SQL);
		if(!\$data){
			return [];
		}
		\${$varName}s = [];
		foreach (\$data as \$item) {
			\${$varName}s[\$item['{$priKey}']] = new $entityName(\$item);
		}
		return \${$varName}s;
	}
	
	/**
	 * @param \${$priKey}
	 * @return {$entityName}|array
	 * @throws DbException
	 */
	public function find(\${$priKey}) {
		if(!\${$priKey}){
			return [];
		}
		\$SQL = sprintf("select * from %s where {$priKey}=:{$priKey}", {$entityName}::TABLE);
		\$row = \$this->conn->getRow(\$SQL,[
			'{$priKey}' => \${$priKey}
		]);
		if(!\$row){
			return [];
		}
		\${$varName} = new $entityName(\$row);
		return \${$varName};
	}

	/**
	 * @param {$entityName} \${$varName}
	 * @param bool         \$returnId
	 * @return bool|int
	 * @throws DbException
	 */
	public function insert($entityName \${$varName}, \$returnId = FALSE) {
		\$result = \$this->conn->insert({$entityName}::TABLE, \${$varName}->toArray());
		if (\$returnId) {
			return intval(\$this->conn->lastInsertId());
		}
		return \$result;
	}

	/**
	 * @param              \${$priKey}
	 * @param $entityName \${$varName}
	 * @return bool
	 * @throws DbException
	 */
	public function update(\${$priKey}, $entityName \${$varName}) {
		\$result = \$this->conn->update({$entityName}::TABLE, \${$varName}->toArray(), ['{$priKey}' => \${$priKey}]);
		return \$result;
	}
	
	/**
	 * @param $entityName \${$varName}
	 * @return {$entityName}|NULL
	 * @throws DbException
	 */
	public function save($entityName \${$varName}) {
		\${$priKey} = \${$varName}->$priKey;
		if (!\${$priKey}) {
			\$result = \$this->conn->insert($entityName::TABLE, \${$varName}->toArray());
			if (\$result) {
				\${$priKey} = \$this->conn->lastInsertId();
			}
		} else {
			\$_info = \$this->find(\${$priKey});
			if (\$_info) {
				\$result = \$this->conn->update($entityName::TABLE, \${$varName}->toArray(), ['$priKey' => \${$priKey}]);
			} else {
				\$result = \$this->conn->insert($entityName::TABLE, \${$varName}->toArray());
			}
		}
		if (!\$result) {
			return NULL;
		}

		\${$varName} = \$this->find(\${$priKey});
		return \${$varName};
	}
	
	/**
	 * @param $entityName \${$varName}
	 * @return {$entityName}[]
	 * @throws DbException
	 */
	public function findBy($entityName \${$varName}) {
		\$wheres   = \${$varName}->toArray();
		\$whereStr = \$this->strWhere(\$wheres);
		\$SQL      = sprintf("select * from %s where %s ", $entityName::TABLE, \$whereStr);
		\$data = \$this->conn->getAll(\$SQL, \$wheres);
		if(!\$data){
			return [];
		}
		\${$varName}s = [];
		foreach (\$data as \$item) {
			\${$varName}s[\$item['{$priKey}']] = new $entityName(\$item);
		}
		return \${$varName}s;
	}
}
CODE;

		file_put_contents($this->modelOutPath . "/{$modelName}.php", $model);
	}
}
