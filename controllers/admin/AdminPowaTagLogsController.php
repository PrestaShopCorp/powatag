<?php

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
		powatagTotAdminTabHelper::addAdminTab(array(
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
		$this->toolbar_btn = array();
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

}