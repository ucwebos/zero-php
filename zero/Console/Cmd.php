<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2018/12/17
 * Time: 上午11:54
 */

namespace Zero\Console;

interface Cmd {
	public function exec($argv);

	public function help();
}
