<?php

namespace Zero;

use Zero\Log\Logger;

/**
 * Class Contract
 * @package Zero
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
	 * @return Container
	 */
	protected function container() {
		if (!$this->container) {
			$this->container = new Container();
		}
		return $this->container;
	}

}
