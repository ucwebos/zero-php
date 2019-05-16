<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2019-03-21
 * Time: 11:01
 */

namespace Zero\Exception;

use Throwable;

/**
 * 数据库异常
 * Class DbException
 * @package Zero\Exception
 */
class DbException extends \Exception {
	/**
	 * 配置异常
	 */
	const ERROR_CONFIG = 511;
	/**
	 * 创建连接异常
	 */
	const ERROR_CONN = 512;
	/**
	 * 执行异常
	 */
	const ERROR_EXEC = 513;

	public function __construct($db = '', $message = '', $code = 0, Throwable $previous = NULL) {
		$message = "The {$db}, error: {$message}";
		parent::__construct($message, $code, $previous);
	}
}
