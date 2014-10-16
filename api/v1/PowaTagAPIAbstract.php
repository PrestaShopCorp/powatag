<?php

abstract class PowaTagAPIAbstract
{

	/**
	 * Property: method
	 * The HTTP method this request was made in, either GET, POST, PUT or DELETE
	 */
	protected $method = '';

	/**
	 * Property: endpoint
	 * The Model requested in the URI. eg: /files
	 */
	protected $endpoint = '';

	/**
	 * Property: verb
	 * An optional additional descriptor about the endpoint, used for things that can
	 * not be handled by the basic methods. eg: /files/process
	 */
	protected $verb = '';

	/**
	 * Property: args
	 * Any additional URI components after the endpoint and verb have been removed, in our
	 * case, an integer ID for the resource. eg: /<endpoint>/<verb>/<arg0>/<arg1>
	 * or /<endpoint>/<arg0>
	 */
	protected $args = array();

	/**
	 * Property: file
	 * Stores the input of the PUT request
	 */
	 protected $file = null;

	 /**
	  * Property: Enable applicative log
	  * @var boolean
	  */
	 protected static $api_log;

	 /**
	  * Property: Enable request log
	  * @var boolean
	  */
	 protected static $request_log;

	 /**
	  * Instance of module
	  * @var \Module
	  */
	 protected $module;

	 /**
	  * Data in content call
	  * @var string
	  */
	 protected $data;

	 /**
	  * Response header
	  * @var int
	  */
	 protected $response = 200;

	/**
	 * Constructor: __construct
	 * Allow for CORS, assemble and pre-process the data
	 */
	public function __construct($request)
	{
		header("Access-Control-Allow-Orgin: *");
		header("Access-Control-Allow-Methods: *");
		header("Content-Type: application/json; charset=utf-8");

		$this->args = explode('/', rtrim($request, '/'));
		$this->endpoint = array_shift($this->args);

		if (array_key_exists(0, $this->args) && !is_numeric($this->args[0]))
			$this->verb = array_shift($this->args);

		$this->method = $_SERVER['REQUEST_METHOD'];
		if ($this->method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER))
		{
			if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE')
				$this->method = 'DELETE';
			else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT')
				$this->method = 'PUT';
			else
				throw new Exception("Unexpected Header");
		}
		
		$this->data = Tools::file_get_contents("php://input");

		self::$api_log = Configuration::get('POWATAG_API_LOG');
		self::$request_log = Configuration::get('POWATAG_REQUEST_LOG');

		$this->module = Module::getInstanceByName('powatag');
	}

	public function setResponse($response)
	{
		$this->response = $response;
	}

	public function getResponse()
	{
		return $this->response;
	}

	public function processAPI()
	{

		if ((int) method_exists($this, $this->endpoint) > 0)
			return $this->_response(call_user_func(array($this, $this->endpoint), $this->args));

		$this->setResponse(404);

		return $this->_response("No Endpoint: $this->endpoint");
	}

	protected function _response($data)
	{
		$status = $this->getResponse();

		header("HTTP/1.1 " . $status . " " . $this->_requestStatus($status));
		return Tools::jsonEncode($this->jsonEncode($data));
	}

	private function jsonEncode($datas, $isArray = true)
	{
		//Use if needed
		return $datas;
	}

	private function _cleanInputs($data)
	{
		$clean_input = array();
		if (is_array($data))
		{
			foreach ($data as $k => $v)
				$clean_input[$k] = $this->_cleanInputs($v);
		}
		else
			$clean_input = trim(strip_tags($data));

		return $clean_input;
	}

	private function _requestStatus($code)
	{
		$status = array(  
			200 => 'OK',
			400 => 'Bad Request',   
			404 => 'Not Found',   
			405 => 'Method Not Allowed',
			500 => 'Internal Server Error',
		); 
		
		return ($status[$code]) ? $status[$code] : $status[500]; 
	}

	public static function requestLog()
	{
		return self::$request_log;
	}

	public static function apiLog()
	{
		return self::$api_log;
	}
}

?> 