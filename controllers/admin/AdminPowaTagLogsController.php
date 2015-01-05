<?php
/**
* 2007-2015 PrestaShop 
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

class AdminPowaTagLogsController extends ModuleAdminController
{
	public function __construct()
	{
		$this->table = 'powatag_logs';
		$this->className = 'PowaTagLogs';
		$this->lang = false;

		$this->_select = null; //If needed you can add informations to select issued from other databases
		$this->_join = null; //Join the databases here

		parent::__construct();
						
		$this->fields_list = array(
			
			'subject' => array(
				'title' => $this->l('Subject'),
				'width' => 120,
			),
		
			'status' => array(
				'title' => $this->l('Status'),
				'width' => 120,
			),
		
			'message' => array(
				'title' => $this->l('Message'),
				'width' => 120,
			),
		
			'date_add' => array(
				'title' => $this->l('Date'),
				'width' => 120,
				'type' => 'datetime'
			),
			
		);

		$this->bootstrap = true;

		$this->fieldImageSettings = array(
		);
		
		$this->list_no_link = true;

	}

	public static function install($menu_id, $module_name)
	{
		PowatagTotAdminTabHelper::addAdminTab(array(
			'id_parent' => $menu_id,
			'className' => 'AdminPowaTagLogs',
			'default_name' => 'Logs',
			'name' => 'Logs',
			'position' => 0, 
			'active' => true,
			'module' => $module_name,
		));
	}

	public function renderList()
	{
		$this->toolbar_btn = $this->module->initToolbar();
		return parent::renderList();
	}

	private function getSelectedCategories($name)
	{
		return array($this->object->{$name});
	}

	private function getRootCategory()
	{
		$root_category = Category::getRootCategory();
		$root_category = array('id_category' => $root_category->id, 'name' => $root_category->name);
		return $root_category;
	}

	public function initPageHeaderToolbar()
	{
		$this->page_header_toolbar_btn = $this->module->initToolbar();
		parent::initPageHeaderToolbar();
	}
}