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

class PowaTagTransaction extends ObjectModel
{

	public $id_cart, $id_order, $id_customer, $ip_address, $id_device, $order_state, $date_add, $date_upd;

	public static $definition = array(
		'table' => 'powatag_transaction',
		'primary' => 'id_powatag_transaction', 
		'multilang' => false,
		'fields' => array(
			'id_cart'     => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
			'id_order'    => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
			'id_customer' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
			'ip_address'  => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
			'id_device'   => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
			'order_state' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
			'date_add'    => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
			'date_upd'    => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
		),
	);

	public static function getTransactions($idCart = null, $idDevice = null, $ipAddress = null)
	{
		$sql = "
			SELECT `".self::$definition['primary']."` 
			FROM `"._DB_PREFIX_.self::$definition['table']."` 
			WHERE 1 ";

		if ($idCart && Validate::isInt($idCart))
			$sql .= ' AND `id_cart` = "'.$idCart.'" ';

		if ($idDevice && Validate::isInt($idDevice))
			$sql .= ' AND `id_device` = "'.$idDevice.'" ';

		if ($ipAddress && Validate::isInt($ipAddress))
			$sql .= ' AND `ip_address` = "'.$ipAddress.'" ';

		$results = Db::getInstance()->ExecuteS($sql);
		
		return $results;
	}

	public static function install()
	{
		// Create Category Table in Database
		$sql = array();
		$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.self::$definition['table'].'` (
					`'.self::$definition['primary'].'` INT(11) NOT NULL AUTO_INCREMENT,
					`id_cart` INT(11) unsigned NOT NULL,
					`id_order` INT(11) unsigned NOT NULL,
					`id_customer` INT(11) unsigned NOT NULL,
					`id_device` VARCHAR(255) NOT NULL,
					`ip_address` VARCHAR(255) NOT NULL,
					`order_state` INT(11) unsigned NOT NULL,
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

	public function getCart()
	{
		return new Cart((int)$this->id_cart);
	}

}