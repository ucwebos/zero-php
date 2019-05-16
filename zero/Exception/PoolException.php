<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2018/12/7
 * Time: 上午11:58
 */

namespace Zero\Exception;

/**
 * 连接池异常
 * Class PoolException
 * @package Zero\Exception
 */
class PoolException extends \Exception {
	/**
	 * 配置异常
	 */
	const ERROR_CONFIG = 5101;
	/**
	 * 获取连接池异常
	 */
	const ERROR_POOL = 5102;
	/**
	 * 获取连接异常
	 */
	const ERROR_GET = 5103;
	/**
	 * 创建连接异常
	 */
	const ERROR_CONN = 5104;
	/**
	 * 执行异常
	 */
	const ERROR_EXEC = 5105;
}
