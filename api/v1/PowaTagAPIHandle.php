<?php

require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.inc.php';
require_once _PS_ROOT_DIR_.DIRECTORY_SEPARATOR.'init.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'PowaTagAPI.php';

class PowaTagAPIHandle
{

	static public function init()
	{
		if (!array_key_exists('HTTP_ORIGIN', $_SERVER))
			$_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
		
		try
		{

			$request = null;

			if (array_key_exists('request', $_GET) && !empty($_GET['request']))
				$request = $_GET['request'];

			$api = new PowaTagAPI($request, $_SERVER['HTTP_ORIGIN']);
			$content = $api->processAPI();
		}
		catch (Exception $e)
		{
			$content = Tools::jsonEncode(array('error' => $e->getMessage()));
		}

		return $content;
	}

}

echo PowaTagAPIHandle::init();

?>