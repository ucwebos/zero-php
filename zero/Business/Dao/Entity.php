<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2019-03-20
 * Time: 17:02
 */

namespace Zero\Business\Dao;

use ArrayAccess;
use Countable;
use JsonSerializable;

/**
 * Class Entity
 */
class Entity implements ArrayAccess, JsonSerializable, Countable {
	/**
	 * 字段类型
	 */
	const BOOL   = 1;
	const INT    = 2;
	const STRING = 3;
	const FLOAT  = 4;
	const ARRAY  = 5;
	const OBJECT = 6;
	/**
	 * @var array
	 */
	protected $property = [];
	protected $type     = [];
	/**
	 * json 自动反序列化
	 * @var bool
	 */
	protected $JSON_AUTO_UN_SERIALIZE = TRUE;

	public function __construct($data = []) {
		if (!$data) {
			return;
		}
		foreach ($this->type as $k => $t) {
			if (isset($data[$k])) {
				$this->property[$k] = $this->convert($t, $data[$k]);
			}
		}
	}

	public function empty() {
		return $this->property ? FALSE : TRUE;
	}

	/**
	 * @param $name
	 * @param $value
	 */
	public function __set($name, $value) {
		if (isset($this->type[$name])) {
			$this->property[$name] = $this->convert($this->type[$name], $value);
		}
	}

	/**
	 * @param $name
	 */
	public function __unset($name) {
		unset($this->property[$name]);
	}

	/**
	 * @param $name
	 * @return mixed
	 */
	public function __get($name) {
		if (isset($this->property[$name])) {
			return $this->property[$name];
		}
	}

	/**
	 * @param        $type
	 * @param        $value
	 * @return mixed
	 */
	protected function convert($type, $value) {
		switch ($type) {
			case self::BOOL:
				return boolval($value);
			case self::INT:
				return intval($value);
			case self::STRING:
				return strval($value);
			case self::FLOAT:
				return floatval($value);
			case self::ARRAY:
				if (is_array($value)) {
					return $value;
				}
				if (is_string($value) && $this->JSON_AUTO_UN_SERIALIZE) {
					return json_decode($value, TRUE) ?: [];
				}
				return [];
			case self::OBJECT:
				return $value ?: NULL;
		}
		return $value;
	}

	public function offsetExists($offset) {
		return $this->property[$offset];
	}

	public function offsetGet($offset) {
		return $this->property[$offset] ?? NULL;
	}

	public function offsetSet($offset, $value) {
		if (isset($this->type[$offset])) {
			$this->property[$offset] = $this->convert($this->type[$offset], $value);
		}
	}

	public function offsetUnset($offset) {
		unset($this->property[$offset]);
	}

	public function jsonSerialize() {
		return $this->property;
	}

	public function count() {
		return count($this->property);
	}

	/**
	 * @return array
	 */
	public function toArray() {
		return $this->property;
	}
}
