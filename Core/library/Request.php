<?php 
namespace Core;

use Core\sexy\View;
use Internal\InternalEnum;
/***
 * Request builder
 */
class Request
{
	// self object
	private static $instance = null;

	// request data
	private $arguments = null;

	// filter rule
	private static $rule = [
		"/<(\\/?)(script|i?frame|style|html|body|title|link|meta|object|\\?|\\%)([^>]*?)>/isU",
     	"/(<[^>]*)on[a-zA-Z]+\s*=([^>]*>)/isU",
       	"/select\b|insert\b|update\b|delete\b|drop\b|;|\"|\'|\/\*|\*|\.\.\/|\.\/|union|into|load_file|outfile|dump/is"
	];

	private function __construct()
	{
		if($this->isGet()){
			$this->arguments = $_GET;
		}elseif($this->isGet()){
			$this->arguments = $_POST;
		}
		if(!empty($this->arguments)){
			$this->arguments = self::filter($this->arguments);
		}
	}

	private function __clone(){}

	/**
	* Get only one self object.
	*/
	public static function instance()
	{
		if(self::$instance && self::$instance instanceof self){
			return self::$instance;
		}
		self::$instance = new self;
		return self::$instance;
	}

	/**
 	* Get arguments by method get.
	*/
	public function get($key = '', $default = null)
	{
		$result = null;
		if($this->isGet()){
			$key = trim($key);
			if($key == ''){
				$result = $this->arguments;
			}else{
				$result = $this->getArgument($key, $default);
			}
		}
		return $result;
	}

	/**
 	* Get arguments by method post.
	*/
	public function post($key = '', $default = null)
	{
		$result = null;
		if($this->isPost()){
			$key = trim($key);
			if($key == ''){
				$result = $this->arguments;
			}else{
				$result = $this->getArgument($key, $default);
			}
		}
		return $result;
	}

	/**
	* Get all arguments by any method.
	*/
	public function all()
	{
		if($this->isGet() || $this->isPost()){
			return $this->arguments;
		}
		return [];
	}

	/**
	* Get many argument by the method.
	*/
	public function input(array $keys = [], $method = 'GET')
	{
		try{
			if(!is_array($keys)){
				throw new \Exception("Method 'input' argument 1 type should be Array.", 1);
			}

			$result = [];
			$way = strtoupper($method);
			if(($this->isGet() && $way == 'GET') || ($this->isPost() && $way == 'POST')){
				if(!empty($keys)){
					foreach($keys as $key){
						$result[trim($key)] = $this->getArgument(trim($key), null);
					}
				}else{
					$result = $this->arguments;
				}
				unset($way);
			}
			return $result;
		}catch(\Exception $e){
			Log::instance()->notice($e->getMessage());
			View::showErr([
				'code'=> InternalEnum::ERR_COMMON,
				'title'=> InternalEnum::ERR_EXCEPTION_TITLE,
				'content'=> $e->getMessage()
			]);
		}
	}

	/**
	* Check request method is GET.
	*/
	public function isGet():bool
	{
		if(isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) == 'GET'){
			return true;
		}
		return false;
	}

	/**
	* Check request method is POST.
	*/
	public function isPost():bool
	{
		if(isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) == 'POST'){
			return true;
		}
		return false;
	}

	/**
	* Check request is AJAX.
	*/
	public function isAjax():bool
	{
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
			return true;
		}
		return false;
	}


	/**
	* Filter the request string if it's illeagal.
	*/
	protected static function filter($param)
	{
		try{
			if(is_array($param)){
				foreach($param as &$item){
					$item = self::filter($item);
				}
				return $param;
			}
			if(!is_string($param)){
				throw new \Exception("Paramaters is Illeagel.", 1);
			}else{
				$str = preg_replace(self::$rule, '', $param);
				$str = htmlspecialchars($str);
				return $str;
			}
		}catch(\Exception $e){
			Log::instance()->notice($e->getMessage());
			View::showErr([
				'code'=> InternalEnum::ERR_COMMON,
				'title'=> InternalEnum::ERR_EXCEPTION_TITLE,
				'content'=> $e->getMessage()
			]);
		}
        exit;
    }

	/**
	* Throw the one argument.
	*/
	protected function getArgument($key, $default)
	{
		$result = null;
		if(!empty($key)){
			if(isset($this->arguments[$key])){
				$result = $this->arguments[$key];
			}elseif(!is_null($default)){
	            $result = $default;
	        }
		}
        return $result;
	}
	
	public static function __callStatic($method, $arguments)
	{
		$self = self::instance();
		if(method_exists($self, $method)){
			return call_user_func_array([$self, $method], $arguments);
		}else{
			View::showErr([
				'code'=> InternalEnum::METHOD_NOT_EXIST,
				'title'=> 'Method is not exist.',
				'content'=> 'Method is not exist on static calling way.'
			]);
		}
        exit;
    }
}