<?php

use Zero\Container;
use Zero\Context;
use Zero\Log\Logger;

if (!function_exists('app')) {
	/**
	 * @param null $id
	 * @return object
	 */
	function app($id = NULL) {
		if(!$id){
			return Container::app();
		}
		return Container::app()->get($id);
	}
}

if (!function_exists('logger')) {
	/**
	 * @return Logger
	 */
	function logger() {
		if (!Container::app()->has(C_LOGGER)) {
			Container::app()->set(C_LOGGER, new Logger());
		}
		return Container::app()->get(C_LOGGER);
	}
}

if (!function_exists('register')) {
	/**
	 * @param $id
	 * @param $obj
	 */
	function register($id, $obj) {
		return Container::app()->set($id, $obj);
	}
}

if (!function_exists('ctx')) {
	/**
	 * @return Context
	 */
	function ctx() {
		return Context::getInstance();
	}
}

if (!function_exists('isCo')) {

	/**
	 * @return bool
	 */
	function isCo() {
		return PHP_SAPI == 'cli' && defined('COROUTINE_SERVER') && !defined('TASK_WORKER');
	}
}
