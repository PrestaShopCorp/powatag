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

if( !defined ('_PS_VERSION_') )
	exit;

class PowaTag extends PaymentModule {

	/**
	 * Module link in BO
	 * @var String
	 */
	private $_link;
	const EAN = 1;
	const UPC = 2;
	const PRODUCT_ID = 3;
	const REFERENCE = 4;

	/**
	 * Constructor of module
	 */
	public function __construct()
	{

		$this->name = 'powatag';
		$this->tab = 'payments_gateways';
		$this->version = '0.1.0.5';
		$this->author = '202-ecommerce';


		parent::__construct();

		$this->includeFiles();

		$this->displayName = $this->l('PowaTag Payment');
		$this->description = $this->l('PowaTag payment');

		if (self::isInstalled($this->name) && self::isEnabled($this->name))
			$this->upgrade();

	}

	private function includeFiles()
	{
		$path = $this->getLocalPath().'classes/';

		foreach (scandir($path) as $class)
		{
			if(is_file($path.$class))
			{
				$class_name = Tools::substr($class, 0, -4);
				//Check if class_name is an existing Class or not
				if(!class_exists($class_name) && $class_name != 'index')
					require_once($path.$class_name.'.php');
			}
		}

		$path .= 'helper/';

		foreach (scandir($path) as $class)
		{
			if(is_file($path.$class))
			{
				$class_name = Tools::substr($class, 0, -4);
				//Check if class_name is an existing Class or not
				if(!class_exists($class_name) && $class_name != 'index')
					require_once($path.$class_name.'.php');
			}
		}
	}

	/**
	 * Module install
	 * @return boolean if install was successfull
	 */
	public function install()
	{
		// Install default
		if (!parent::install())
			return false;

		// Uninstall DataBase
		if (!$this->installSQL())
			return false;

		// Install tabs
		if(!$this->installTabs())
			return false;

		// Registration hook
		if (!$this->registrationHook())
			return false;


		return true;
	}

	/**
	 * Upgrade if necessary
	 */
	public function upgrade()
	{
		$cfgName = Tools::strtoupper($this->name . '_version');
		$version = Configuration::get($cfgName);

		if ($version === false || version_compare($version, $this->version, '<'))
		{
			Configuration::updateValue($cfgName, $this->version);
		}
	}

	/**
	 * Module uninstall
	 * @return boolean if uninstall was successfull
	 */
	public function uninstall()
	{

		// Uninstall DataBase
		if (!$this->uninstallSQL())
			return false;

		// Delete tabs
		if(!$this->uninstallTabs())
			return false;

		// Uninstall default
		if (!parent::uninstall())
			return false;

		return true;
	}

	/**
	 * Initialisation to install / uninstall
	 */
	private function installTabs() 
	{
		
		$menu_id = -1;

		$controllers = scandir(dirname(__FILE__).'/controllers/admin');
		foreach ($controllers as $controller)
		{
			if(is_file(dirname(__FILE__).'/controllers/admin/'.$controller) && $controller != 'index.php')
			{
				require_once(dirname(__FILE__).'/controllers/admin/'.$controller);
				$controller_name = Tools::substr($controller, 0, -4);
				if(class_exists($controller_name))
				{
					if(method_exists($controller_name, 'install'))
						call_user_func(array($controller_name, 'install'), $menu_id, $this->name);
				}
			}
		}

		return true;

	}

	/**
	 * Delete tab
	 * @return  boolean if successfull
	 */
	public function uninstallTabs()
	{
		powatagTotAdminTabHelper::deleteAdminTabs($this->name);
		return true;
	}

	/**
	 * Install DataBase table
	 * @return boolean if install was successfull
	 */
	private function installSQL()
	{
		$classes = scandir(dirname(__FILE__).'/classes');
		foreach ($classes as $class)
		{
			if(is_file(dirname(__FILE__).'/classes/'.$class))
			{
				$class_name = Tools::substr($class, 0, -4);
				if(class_exists($class_name))
				{
					if(method_exists($class_name, 'install'))
						call_user_func(array($class_name, 'install'));
				}
			}
		}
	
		return true;
	}

	/**
	 * Uninstall DataBase table
	 * @return boolean if install was successfull
	 */
	private function uninstallSQL()
	{
		$classes = scandir(dirname(__FILE__).'/classes');
		foreach ($classes as $class)
		{
			if(is_file(dirname(__FILE__).'/classes/'.$class))
			{
				$class_name = Tools::substr($class, 0, -4);
				if(class_exists($class_name))
				{
					if(method_exists($class_name, 'uninstall'))
						call_user_func(array($class_name, 'uninstall'));
				}
			}
		}
		
		return true;
	}

	/**
	 * [registrationHook description]
	 * @return [type] [description]
	 */
	private function registrationHook()
	{
		
		if (!$this->registerHook('displayHeader') 
			|| !$this->registerHook('displayRightColumnProduct') 
			|| !$this->registerHook('displayLeftColumnProduct') 
			|| !$this->registerHook('displayFooterProduct') 
			|| !$this->registerHook('displayFooterProduct')
			|| !$this->registerHook('actionCarrierUpdate'))
			return false;
		
		return true;
	}

	public function hookDisplayHeader()
	{
		if ($this->context->smarty->getTemplateVars('page_name') == 'product')
		{

			$live = (bool) Configuration::get('POWATAG_SANDBOX');

			$this->context->controller->addCSS(Configuration::get('POWATAG_CSS_URL'));
			$this->context->controller->addJS(Configuration::get('POWATAG_JS_URL'));
			$this->context->controller->addJS($this->getPathUri().'js/powatag.js');
		}
	}

	public function hookdisplayRightColumnProduct()
	{
		if (!Configuration::get('POWATAG_QR') || Configuration::get('POWATAG_QR_POS') != 'displayRightColumnProduct')
			return false;

		return $this->generateTag();
	}

	public function hookdisplayLeftColumnProduct()
	{
		if (!Configuration::get('POWATAG_QR') || Configuration::get('POWATAG_QR_POS') != 'displayLeftColumnProduct')
			return false;

		return $this->generateTag();
	}

	public function hookdisplayFooterProduct()
	{
		if (!Configuration::get('POWATAG_QR') || Configuration::get('POWATAG_QR_POS') != 'displayFooterProduct')
			return false;

		return $this->generateTag();
	}

	public function hookactionCarrierUpdate($params)
	{
		if ($params['carrier'] instanceof Carrier && Validate::isLoadedObject($params['carrier']))
		{
			if (Configuration::get('POWATAG_SHIPPING') == $params['id_carrier'])
				Configuration::updateValue('POWATAG_SHIPPING', $params['carrier']->id);
		}
	}

	private function generateTag()
	{

		$product = new Product((int)Tools::getValue('id_product'), true, (int)$this->context->language->id);

		if($product_sku = PowaTagProductHelper::getProductSKU($product))
		{
			$datas = array(
				'powatagApi'     => Configuration::get('POWATAG_API_KEY'),
				'productSku'     => PowaTagProductHelper::getProductSKU($product),
				'powatagGeneratorURL' => Configuration::get('POWATAG_GENERATOR_URL'),
			);

			$this->context->smarty->assign($datas);

			return $this->display(__FILE__, 'product.tpl');
		}
	}


	/**
	 * Admin display
	 * @return String Display admin content
	 */
	public function getContent()
	{
		Tools::redirectAdmin($this->context->link->getAdminLink('AdminPowaTagConfiguration'));
	}

}

?>