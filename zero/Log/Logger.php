<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2019-03-08
 * Time: 16:34
 */

namespace Zero\Log;

use Psr\Log\LoggerInterface;
use Zero\Container;

class Logger implements LoggerInterface {
	const EMERGENCY = 'emergency';
	const ALERT     = 'alert';
	const CRITICAL  = 'critical';
	const ERROR     = 'error';
	const WARNING   = 'warning';
	const NOTICE    = 'notice';
	const INFO      = 'info';
	const DEBUG     = 'debug';
	/**
	 * @var string
	 */
	protected $tag = '';
	/**
	 * @var WriterInterface
	 */
	protected $writer;

	public function __construct() {
		if ($writer = Container::app()
			->get(C_LOG_WRITER)) {
			$this->writer = $writer;
		} else {
			$this->writer = new File();
		}
	}

	public function log($level, $message, array $context = []) {
		$this->writer->write($level, $this->tag, $message, $context);
	}

	public function setTag($tag) {
		$this->tag = $tag;
	}

	public function serWriter(WriterInterface $writer) {
		$this->writer = $writer;
	}

	/**
	 * System is unusable.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return void
	 */
	public function emergency($message, array $context = []) {
		self::log(self::EMERGENCY, $message, $context);
	}

	/**
	 * Action must be taken immediately.
	 *
	 * Example: Entire website down, database unavailable, etc. This should
	 * trigger the SMS alerts and wake you up.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return void
	 */
	public function alert($message, array $context = []) {
		self::log(self::ALERT, $message, $context);
	}

	/**
	 * Critical conditions.
	 *
	 * Example: Application component unavailable, unexpected exception.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return void
	 */
	public function critical($message, array $context = []) {
		self::log(self::CRITICAL, $message, $context);
	}

	/**
	 * Runtime errors that do not require immediate action but should typically
	 * be logged and monitored.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return void
	 */
	public function error($message, array $context = []) {
		self::log(self::ERROR, $message, $context);
	}

	/**
	 * Exceptional occurrences that are not errors.
	 *
	 * Example: Use of deprecated APIs, poor use of an API, undesirable things
	 * that are not necessarily wrong.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return void
	 */
	public function warning($message, array $context = []) {
		self::log(self::WARNING, $message, $context);
	}

	/**
	 * Normal but significant events.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return void
	 */
	public function notice($message, array $context = []) {
		self::log(self::NOTICE, $message, $context);
	}

	/**
	 * Interesting events.
	 *
	 * Example: User logs in, SQL logs.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return void
	 */
	public function info($message, array $context = []) {
		self::log(self::INFO, $message, $context);
	}

	/**
	 * Detailed debug information.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return void
	 */
	public function debug($message, array $context = []) {
		self::log(self::DEBUG, $message, $context);
	}
}
