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
	protected $dbCli;
	protected $db;
	protected $outPath;
	protected $withModel;
	protected $modelOutPath;

	public function gen($args) {
		$table           = $args['t'] ?? '';
		$this->db        = $args['db'] ?? '';
		$this->withModel = boolval($args['--model'] ?? FALSE);
		if (!$this->db || !$table) {
			die("Please input args like db={database} t={table or ALL} -o={outPath} ");
		}
		try {
			$this->dbCli = new Mysql($this->db);
		} catch (\Throwable $e) {
			die("Generating err: " . $e->getMessage());
		}
		$out                = $args['-o'] ?? '';
		$this->outPath      = ROOT_PATH . '/app/Dao/Structure';
		$this->modelOutPath = ROOT_PATH . '/app/Dao/Model';
		if ($out) {
			$this->outPath = ROOT_PATH . '/' . $out;
		}
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
			$this->genStructure($database, $_table);
		}
	}

	protected function getAllTables() {
		return $this->dbCli->getColumn("show tables");
	}

	protected function genStructure($database, $table) {
		$className  = implode('', array_map('ucwords', explode('_', $table)));
		$data       = $this->dbCli->getAll("select COLUMN_NAME,COLUMN_TYPE,COLUMN_KEY,COLUMN_DEFAULT,COLUMN_COMMENT,EXTRA,IS_NULLABLE from information_schema.columns where table_schema='{$database}' and table_name='{$table}'");
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

			switch ($type) {
				case "string":
					$default = "'{$default}'";
					break;
				case "int":
					$default = intval($default);
					break;
				case "float":
					$default = floatval($default);
					break;
			}

			$notes .= "* @property {$type} {$column} {$comment}
 ";
			// 字段属性
			$typeArr .= "
		'{$column}' => self::$typeUp,";

		}

		$type     = "[{$typeArr}
	]";

		$class = "<?php

namespace App\Dao\Structure;

use Zero\Business\Structure;

/**
 * Class $className
 {$notes}
 */
class {$className} extends Structure {
	
	const TABLE = '{$table}';

	protected \$type = {$type};

	
}";
		file_put_contents($this->outPath . "/{$className}.php", $class);

		if ($this->withModel) {
			echo "Start generating model for table >> " . $database . '-----' . $table . PHP_EOL;
			$this->genModel($className, $priKey);
		}
	}

	protected function genModel($className, $priKey) {
		//未设置主健不能生成
		if(!$priKey){
			return;
		}

		$varName     = lcfirst($className);
		$structureAs = $className . 'Data';

		$model = <<<CODE
<?php

namespace App\Dao\Model;

use Zero\Business\Model;
use Zero\Exception\PoolException;

use App\Dao\Structure\\{$className} as {$structureAs};

class {$className} extends Model {
	protected \$dbName = '{$this->db}';
	

	/**
	 * @param array \${$priKey}s
	 * @return {$structureAs}[]
	 * @throws PoolException
	 */
	public function get{$className}s(array \${$priKey}s) {
		if(!\${$priKey}s){
			return [];
		}
		\$SQL = sprintf("select * from %s where {$priKey} in (%s)", {$structureAs}::TABLE, \$this->strIds(\${$priKey}s));
		\$data = \$this->db->getAll(\$SQL);
		\${$varName}s = [];
		foreach (\$data as \$item) {
			\${$varName}s[\$item['{$priKey}']] = new $structureAs(\$item);
		}
		return \${$varName}s;
	}
	
	/**
	 * @param array \${$priKey}
	 * @return {$structureAs}
	 * @throws PoolException
	 */
	public function get{$className}s(array \${$priKey}) {
		if(!\${$priKey}s){
			return new $structureAs();
		}
		\$SQL = sprintf("select * from %s where {$priKey}=:{$priKey}", {$structureAs}::TABLE);
		\$row = \$this->db->getRow(\$SQL,[
			'{$priKey}' => \${$priKey}
		]);
		
		\${$varName} = new $structureAs(\$row);
		return \${$varName};
	}

	/**
	 * @param {$structureAs} \${$varName}
	 * @param bool         \$returnId
	 * @return bool|int
	 * @throws PoolException
	 */
	public function add{$className}($structureAs \${$varName}, \$returnId = FALSE) {
		\$result = \$this->db->insert({$structureAs}::TABLE, \${$varName}->insertData());
		if (\$returnId) {
			return intval(\$this->db->lastInsertId());
		}
		return \$result;
	}

	/**
	 * @param              \${$priKey}
	 * @param $structureAs \${$varName}
	 * @return bool
	 * @throws PoolException
	 */
	public function set{$className}(\${$priKey}, $structureAs \${$varName}) {
		\$result = \$this->db->update({$structureAs}::TABLE, \${$varName}->insertData(), ['{$priKey}' => \${$priKey}]);
		return \$result;
	}

	/**
	 * @param \${$priKey}
	 * @return bool
	 * @throws PoolException
	 */
	public function delete(\${$priKey}) {
		\$result = \$this->db->delete({$structureAs}::TABLE, ['{$priKey}' => \${$priKey}]);
		return \$result;
	}
	
	/**
	 * @param $structureAs \${$varName}
	 * @return array
	 * @throws PoolException
	 */
	public function getIdsBy($structureAs \${$varName}) {
		\$wheres   = \${$varName}->getSetField(\${$varName});
		\$whereStr = \$this->strWhere(\$wheres);
		\$SQL      = sprintf("select {$priKey} from %s where %s ", $structureAs::TABLE, \$whereStr);
		\$data = \$this->db->getColumn(\$SQL, \$wheres);
		return \$data;
	}
}
CODE;

		file_put_contents($this->modelOutPath . "/{$className}.php", $model);
	}
}
