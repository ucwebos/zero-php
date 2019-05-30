<?php

if (!function_exists('app')) {
	/**
	 * @param null $id
	 * @return object
	 */
	function app($id = NULL) {
		if(!$id){
			return \Zero\Container::app();
		}
		return \Zero\Container::app()->get($id);
	}

	/**
	 * @param $id
	 * @param $obj
	 */
	function register($id,$obj){
		return \Zero\Container::app()->set($id,$obj);
	}

}
