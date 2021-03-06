<?php

use Core\{Config, View, Route};

/**
* helper functions
*/
if(!function_exists('config')){
	/**
	* get config item
	*/
	function config(string $config, $default = null)
	{
		return Config::get($config, $default);
	}
}
if(!function_exists('e')){
	/**
	* filter string to print
	*/
	function e(string $string = '')
	{
		return htmlspecialchars($string);
	}
}
if(!function_exists('view')){
	/*
	* show template
	*/
	function view($tpl){
		return View::show($tpl);
	}
}
if(!function_exists('dd')){
	/**
	* print data by format
	*/
	function dd($dump){
		echo "<pre>";
		var_dump($dump);
		exit;
	}
}
