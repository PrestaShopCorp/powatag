<?php

class PowaTagLogs extends ObjectModel
{

	const IN_PROGRESS = 'in progress';
	const SUCCESS = 'success';
	const WARNING = 'warning';
	const ERROR = 'error';

	public  $subject, $status, $message, $date_add, $date_upd;

	static public $definition = array(
		'table' => 'powatag_logs',
		'primary' => 'id_powatag_logs', 
		'multilang' => false,
		'fields' => array(
		 	'subject'  => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
		 	'status'   => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
		 	'message'  => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
		 	'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
		 	'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
		),
	);

	static public function getIds()
	{
		$sql = "SELECT `".self::$definition['primary']."` FROM " ._DB_PREFIX_.self::$definition['table']."";
		$objsIDs = Db::getInstance()->ExecuteS($sql);
		return $objsIDs;
	}

	static public function install()
	{
		// Create Category Table in Database
		$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.self::$definition['table'].'` (
				  	`'.self::$definition['primary'].'` int(16) NOT NULL AUTO_INCREMENT,
				 	`subject` VARCHAR(255) NOT NULL,
				 	`status` VARCHAR(255) NOT NULL,
				 	`message` VARCHAR(255) NOT NULL,
				 	date_add DATETIME NOT NULL,
					date_upd DATETIME NOT NULL,
					UNIQUE(`'.self::$definition['primary'].'`),
				  	PRIMARY KEY  ('.self::$definition['primary'].')
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
		

		foreach ($sql as $q) 
			Db::getInstance()->Execute($q);	
	}

	static public function uninstall()
	{
		// Create Category Table in Database
		$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.self::$definition['table'].'`';
		

		foreach ($sql as $q) 
			Db::getInstance()->Execute($q);
	}

	static public function initLog($subject, $message, $api = false, $status = false)
	{
		if ($api && Configuration::get('POWATAG_API_LOG'))
		{
			$log = new PowaTagLogs();
			$log->subject = $subject;
			$log->message = $message;
			$log->status = $status;
			return $log->save();
		}
		else if (Configuration::get('POWATAG_REQUEST_LOG'))
		{
			$module = Module::getInstanceByName('powatag');

			$handle = fopen($module->getLocalPath().'error.txt', 'a+');
			fwrite($handle, '['.strftime('%Y-%m-%d %H:%M:%S').'] '.$subject.' : '.print_r($message, true));
			fclose($handle);
		}
	}

	static public function initAPILog($subject, $status, $message)
	{
		return self::initLog($subject, $message, true, $status);
	}

	static public function initRequestLog($subject, $message)
	{
		return self::initLog($subject, $message);
	}

}