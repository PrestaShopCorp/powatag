<?php

class AdminPowaTagConfigurationController extends ModuleAdminController
{
	public function __construct()
	{
		$this->table = 'powatag_configuration';
		$this->lang = false;

		$this->_select = null; //If needed you can add informations to select issued from other databases
		$this->_join = null; //Join the databases here

		parent::__construct();

		$this->bootstrap = true;
						
		$this->fields_options = array(
			'api_settings' => array(
				'title'  =>	$this->l('API Settings'),
				'image'  => '../img/admin/prefs.gif',
				'fields' =>	array(
		 			'POWATAG_API_KEY' => array(
		 				'title'      => $this->l('API Key'),
		 				'validation' => 'isString',
		 				'type'       => 'text',
		 				'size'       => '80',
						'visibility' => Shop::CONTEXT_SHOP,
						'required'   => true
					),
		 			'POWATAG_HMAC_KEY' => array(
		 				'title'      => $this->l('HMAC Key'),
		 				'validation' => 'isString',
		 				'type'       => 'text',
		 				'size'       => '80',
						'visibility' => Shop::CONTEXT_SHOP,
						'required'   => true
					),
		 			'POWATAG_SANDBOX' => array(
		 				'title'      => $this->l('Live'),
		 				'validation' => 'isUnsignedId',
		 				'type'       => 'select',
		 				'identifier' => 'key', 
						'list'       => array(
							array('key' => 0, 'name' => $this->l('Sandbox')),
							array('key' => 1, 'name' => $this->l('Live'))
						),
						'visibility' => Shop::CONTEXT_SHOP,
						'required' => true
					),
				),
				'submit' => array('title' => $this->l('Save'))
			),
			'other_settings' => array(
				'title'  =>	$this->l('Other Settings'),
				'image'  => '../img/admin/tab-tools.gif',
				'fields' =>	array(
		 			'POWATAG_SHIPPING' => array(
		 				'title'      => $this->l('Shipping Method : This will be used to calculate shipping costs'),
		 				'validation' => 'isInt',
		 				'type'       => 'select',
		 				'identifier' => 'id_carrier',
		 				'list'       => Carrier::getCarriers($this->context->language->id),
						'visibility' => Shop::CONTEXT_SHOP,
						'required'   => true
					),
		 			'POWATAG_SUCCESS_MSG' => array(
		 				'title'      => $this->l('Sucess message'),
		 				'validation' => 'isString',
		 				'type'       => 'textLang',
		 				'size'       => '80',
						'visibility' => Shop::CONTEXT_SHOP
					),
		 			'POWATAG_FAIL_MSG' => array(
		 				'title'      => $this->l('Failure message'),
		 				'validation' => 'isString',
		 				'type'       => 'textLang',
		 				'size'       => '80',
						'visibility' => Shop::CONTEXT_SHOP
					),
		 			'POWATAG_SHIP_MSG' => array(
		 				'title'      => $this->l('Failure message on shipping'),
		 				'validation' => 'isString',
		 				'type'       => 'textLang',
		 				'size'       => '80',
						'visibility' => Shop::CONTEXT_SHOP
					),
		 			'POWATAG_IMG_TYPE' => array(
		 				'title'      => $this->l('Image type to send'),
		 				'validation' => 'isInt',
		 				'type'       => 'select',
		 				'identifier' => 'id_image_type',
		 				'list'       => ImageType::getImagesTypes(),
						'visibility' => Shop::CONTEXT_SHOP,
						'required'   => true
					),
				),
				'submit' => array('title' => $this->l('Save'))
			),
			'product_settings' => array(
				'title'  =>	$this->l('Product Settings'),
				'image'  => $this->module->getPathUri() . 'views/img/qr_code.png',
				'fields' =>	array(
					'POWATAG_QR' => array(
						'title'      => $this->l('QR Code enabled'),
						'validation' => 'isBool',
						'cast'       => 'intval',
						'type'       => 'bool',
						'visibility' => Shop::CONTEXT_SHOP
					),
		 			'POWATAG_QR_POS' => array(
		 				'title'      => $this->l('QR code Position'),
		 				'validation' => 'isString',
		 				'type'       => 'select',
		 				'identifier' => 'key', 
						'list'       => array(
							array('key' => 'displayRightColumnProduct', 'name' => $this->l('displayRightColumnProduct')),
							array('key' => 'displayLeftColumnProduct', 'name' => $this->l('displayLeftColumnProduct')),
							array('key' => 'displayFooterProduct', 'name' => $this->l('displayFooterProduct'))
						),
						'visibility' => Shop::CONTEXT_SHOP
					)
				),
				'submit' => array('title' => $this->l('Save'))
			),
			'logs' => array(
				'title'  =>	$this->l('Logs'),
				'image'  => '../img/t/AdminLogs.gif',
				'fields' =>	array(
					'POWATAG_API_LOG' => array(
						'title'      => $this->l('Enable applicative logging'),
						'validation' => 'isBool',
						'cast'       => 'intval',
						'type'       => 'bool',
						'visibility' => Shop::CONTEXT_SHOP
					),
					'POWATAG_REQUEST_LOG' => array(
						'title'      => $this->l('Enable request logging'),
						'validation' => 'isBool',
						'cast'       => 'intval',
						'type'       => 'bool',
						'visibility' => Shop::CONTEXT_SHOP
					),
				),
				'submit' => array('title' => $this->l('Save'))
			)
		);

	}

	public static function install($menu_id, $module_name)
	{
		powatagTotAdminTabHelper::addAdminTab(array(
			'id_parent'    => $menu_id,
			'className'    => 'AdminPowaTagConfiguration',
			'default_name' => 'Configuration',
			'name'         => 'Configuration',
			'position'     => 0, 
			'active'       => true,
			'module'       => $module_name,
		));
	}

}