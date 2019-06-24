<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2018/12/17
 * Time: 下午1:56
 */

namespace Zero\Console;

class Args {
	private $isHelp = FALSE;
	private $cmd    = '';
	private $subCmd = '';
	private $args   = [];

	public function __construct($argv) {
		foreach ($argv as $item) {
			if (strpos($item, "tools") !== FALSE) {
				continue;
			}
			if ($item == "--help") {
				$this->isHelp = TRUE;
				break;
			}
			if (!$this->cmd) {
				$this->cmd = $item;
				continue;
			}
			if (!$this->subCmd) {
				$this->subCmd = $item;
				continue;
			}
			$this->args[] = $item;
		}
	}

	public function getCmd() {
		return $this->cmd;
	}

	public function getSubCmd() {
		return $this->subCmd;
	}

	public function getArgs() {
		return $this->args;
	}

	public function isHelp() {
		return $this->isHelp;
	}
}
