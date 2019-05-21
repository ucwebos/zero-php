<?php

namespace Zero;

/**
 * Class Contract
 * @package Zero\Business
 */
class Contract {
	/**
	 * @var Logger
	 */
	private $logger;
	/**
	 * @var Container
	 */
	private $container;

	public function __get($name) {
		if (!$this->container) {
			$this->container = new Container();
		}
		switch ($name) {
			case 'logger':
				return $this->container->get('logger');
			case '':
				return $this->container->get('logger');
		}
	}

	/**
	 * @return Container
	 */
	protected function app() {
		return Container::app();
	}

	/**
	 * @return Logger
	 */
	protected function logger() {
		if (!$this->logger) {
			$this->logger = new Logger();
		}
		return $this->logger;
	}

	/**
	 * @return Context
	 */
	protected function ctx() {
		return Context::getInstance();
	}

	/**
	 * @return Container
	 */
	protected function container() {
		if (!$this->container) {
			$this->container = new Container();
		}
		return $this->container;
	}

	/**
	 * @return bool
	 */
	protected function isCo() {
		return PHP_SAPI == 'cli' && defined('COROUTINE_SERVER');
	}
}
