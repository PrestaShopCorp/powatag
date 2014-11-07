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

class PowaTagLogs extends ObjectModel
{

	const IN_PROGRESS = 'in progress';
	const SUCCESS = 'success';
	const WARNING = 'warning';
	const ERROR = 'error';

	public $subject, $status, $message, $date_add, $date_upd;

	public static $definition = array(
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

	public static function getIds()
	{
		$sql = "SELECT `".self::$definition['primary']."` FROM "._DB_PREFIX_.self::$definition['table']."";
		$objsIDs = Db::getInstance()->ExecuteS($sql);
		return $objsIDs;
	}

	public static function install()
	{
		// Create Category Table in Database
		$sql = array();
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

	public static function uninstall()
	{
		// Create Category Table in Database
		$sql = array();
		$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.self::$definition['table'].'`';
		

		foreach ($sql as $q) 
			Db::getInstance()->Execute($q);
	}

	public static function initLog($subject, $message, $api = false, $status = false)
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

	public static function initAPILog($subject, $status, $message)
	{
		return self::initLog($subject, $message, true, $status);
	}

	public static function initRequestLog($subject, $message)
	{
		return self::initLog($subject, $message);
	}

}