<?php
/**
* 2007-2014 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2014 PrestaShop SA
*  @version  Release: $Revision: 7776 $
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

require_once(dirname(__FILE__).'/../../classes/PowaTagRequestLogs.php');

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

		
		PowaTagRequestLogs::add(array(
			'args' => $this->args,
			'endpoint' => $this->endpoint,
			'verb' => $this->verb,
			'method' => $this->method,
			'data' => $this->data,
		));

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
		if ((int)method_exists($this, $this->endpoint) > 0)
			return $this->_response(call_user_func(array($this, $this->endpoint), $this->args));

		$this->setResponse(404);

		return $this->_response("No Endpoint: $this->endpoint");
	}

	protected function _response($data)
	{
		$status = $this->getResponse();

		header("HTTP/1.1 ".$status." ".$this->_requestStatus($status));
		PowaTagRequestLogs::add(array(
			'response' => Tools::jsonEncode($data)
		));
		return Tools::jsonEncode($data);
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