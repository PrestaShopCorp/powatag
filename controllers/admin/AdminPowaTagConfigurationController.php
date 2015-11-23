<?php
/**
* 2007-2015 PrestaShop.
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
*
*  @version  Release: $Revision: 7776 $
*
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

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
                'title' => $this->l('API Settings'),
                'image' => '../img/admin/prefs.gif',
                'fields' => array(
                    'POWATAG_API_KEY' => array(
                        'title' => $this->l('API Key'),
                        'validation' => 'isString',
                        'type' => 'text',
                        'size' => '80',
                        'visibility' => Shop::CONTEXT_SHOP,
                        'required' => true,
                    ),
                    'POWATAG_HMAC_KEY' => array(
                        'title' => $this->l('HMAC Key'),
                        'validation' => 'isString',
                        'type' => 'text',
                        'visibility' => Shop::CONTEXT_SHOP,
                        'size' => 80,
                        'required' => true,
                    ),
                    'POWATAG_GENERATOR_URL' => array(
                        'title' => $this->l('Powatag Endpoint URL'),
                        'validation' => 'isString',
                        'type' => 'text',
                        'size' => 80,
                    ),
                    'POWATAG_JS_URL' => array(
                        'title' => $this->l('Head JavaScript URL'),
                        'validation' => 'isString',
                        'type' => 'text',
                        'size' => 80,
                    ),
                    'POWATAG_CSS_URL' => array(
                        'title' => $this->l('Head CSS URL'),
                        'validation' => 'isString',
                        'type' => 'text',
                        'size' => 80,
                    ),
                    'POWATAG_LEGACY_ERRORS' => array(
                        'title' => $this->l('Legacy error codes enabled'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'type' => 'bool',
                        'visibility' => Shop::CONTEXT_SHOP,
                    ),

                ),
                'submit' => array('title' => $this->l('Save')),
            ),
            'other_settings' => array(
                'title' => $this->l('Other Settings'),
                'image' => '../img/admin/tab-tools.gif',
                'fields' => array(
                    'POWATAG_SHIPPING' => array(
                        'title' => $this->l('Shipping Method'),
                        'validation' => 'isInt',
                        'type' => 'select',
                        'identifier' => 'id_carrier',
                        'desc' => $this->l('This will be used to calculate shipping costs'),
                        'list' => Carrier::getCarriers($this->context->language->id, false, false, false, null, Carrier::ALL_CARRIERS),
                        'visibility' => Shop::CONTEXT_SHOP,
                        'required' => true,
                    ),
                    'POWATAG_SUCCESS_MSG' => array(
                        'title' => $this->l('Sucess message'),
                        'validation' => 'isString',
                        'type' => 'textLang',
                        'size' => '80',
                        'visibility' => Shop::CONTEXT_SHOP,
                    ),
                    'POWATAG_IMG_TYPE' => array(
                        'title' => $this->l('Image type to send'),
                        'validation' => 'isInt',
                        'type' => 'select',
                        'identifier' => 'id_image_type',
                        'list' => ImageType::getImagesTypes(),
                        'visibility' => Shop::CONTEXT_SHOP,
                        'required' => true,
                    ),
                ),
                'submit' => array('title' => $this->l('Save')),
            ),
            'product_settings' => array(
                'title' => $this->l('Product Settings'),
                'image' => $this->module->getPathUri().'views/img/qr_code.png',
                'fields' => array(
                    'POWATAG_QR' => array(
                        'title' => $this->l('QR Code enabled'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'type' => 'bool',
                        'visibility' => Shop::CONTEXT_SHOP,
                    ),
                    'POWATAG_QR_POS' => array(
                        'title' => $this->l('QR code Position'),
                        'validation' => 'isString',
                        'type' => 'select',
                        'identifier' => 'key',
                        'list' => array(
                            array('key' => 'displayRightColumnProduct', 'name' => $this->l('displayRightColumnProduct')),
                            array('key' => 'displayLeftColumnProduct', 'name' => $this->l('displayLeftColumnProduct')),
                            array('key' => 'displayFooterProduct', 'name' => $this->l('displayFooterProduct')),
                            array('key' => 'displayProductButtons', 'name' => $this->l('displayProductButtons')),
                        ),
                        'visibility' => Shop::CONTEXT_SHOP,
                    ),
                    'POWATAG_SKU' => array(
                        'title' => $this->l('Which SKU field to use '),
                        'validation' => 'isInt',
                        'type' => 'select',
                        'identifier' => 'key',
                        'list' => array(
                            array('key' => Powatag::EAN, 'name' => $this->l('EAN13 or JAN')),
                            array('key' => Powatag::UPC, 'name' => $this->l('UPC')),
                            array('key' => Powatag::PRODUCT_ID, 'name' => $this->l('Product ID')),
                            array('key' => Powatag::REFERENCE, 'name' => $this->l('REFERENCE')),
                        ),
                    ),
                    'POWATAG_REDIRECT' => array(
                        'title' => $this->l('URL Redirect'),
                        'validation' => 'isString',
                        'type' => 'text',
                        'size' => 250,
                    ),
                    'POWATAG_OFFER' => array(
                        'title' => $this->l('Promotional area'),
                        'validation' => 'isString',
                        'type' => 'text',
                        'size' => 250,
                    ),
                    'POWATAG_LANG' => array(
                        'title' => $this->l('Language'),
                        'validation' => 'isString',
                        'type' => 'select',
                        'identifier' => 'key',
                        'list' => array(
                            array('key' => '',      'name' => $this->l('Default')),
                            array('key' => 'site',  'name' => $this->l('Use site language')),
                            array('key' => 'en_GB', 'name' => $this->l('en_GB')),
                            array('key' => 'es_ES', 'name' => $this->l('es_ES')),
                            array('key' => 'fr_FR', 'name' => $this->l('fr_FR')),
                            array('key' => 'it_IT', 'name' => $this->l('it_IT')),
                        ),
                    ),
                    'POWATAG_TYPE' => array(
                        'title' => $this->l('Type'),
                        'validation' => 'isString',
                        'type' => 'select',
                        'identifier' => 'key',
                        'list' => array(
                            array('key' => '',              'name' => $this->l('Default')),
                            array('key' => 'bag',           'name' => $this->l('Bag')),
                            array('key' => 'mobile-button', 'name' => $this->l('Mobile button')),
                            array('key' => 'tablet-bag',    'name' => $this->l('Tablet bag')),
                        ),
                    ),
                    'POWATAG_STYLE' => array(
                        'title' => $this->l('Style'),
                        'validation' => 'isString',
                        'type' => 'select',
                        'identifier' => 'key',
                        'list' => array(
                            array('key' => '',              'name' => $this->l('Default')),
                            array('key' => 'act-left',      'name' => $this->l('act-left')),
                            array('key' => 'act-right',     'name' => $this->l('act-right')),
                            array('key' => 'buy-left',      'name' => $this->l('buy-left')),
                            array('key' => 'buy-right',     'name' => $this->l('buy-right')),
                            array('key' => 'give-left',     'name' => $this->l('give-left')),
                            array('key' => 'give-right',    'name' => $this->l('give-right')),
                            array('key' => 'bg-act-left',   'name' => $this->l('bg-act-left')),
                            array('key' => 'bg-act-right',  'name' => $this->l('bg-act-right')),
                            array('key' => 'bg-buy-left',   'name' => $this->l('bg-buy-left')),
                            array('key' => 'bg-buy-right',  'name' => $this->l('bg-buy-right')),
                            array('key' => 'bg-give-left',  'name' => $this->l('bg-give-left')),
                            array('key' => 'bg-give-right', 'name' => $this->l('bg-give-right')),
                        ),
                    ),
                    'POWATAG_COLORSCHEME' => array(
                        'title' => $this->l('Color scheme'),
                        'validation' => 'isString',
                        'type' => 'select',
                        'identifier' => 'key',
                        'list' => array(
                            array('key' => '',      'name' => $this->l('Default')),
                            array('key' => 'light', 'name' => $this->l('Light')),
                            array('key' => 'dark',  'name' => $this->l('Dark')),
                        ),
                    ),
                    'POWATAG_DISPLAY' => array(
                        'title' => $this->l('Desktop / mobile'),
                        'validation' => 'isString',
                        'type' => 'select',
                        'identifier' => 'key',
                        'list' => array(
                            array('key' => '',             'name' => $this->l('Default')),
                            array('key' => 'both',         'name' => $this->l('Both')),
                            array('key' => 'desktop-only', 'name' => $this->l('Desktop only')),
                            array('key' => 'mobile-only',  'name' => $this->l('Mobile only')),
                        ),
                    ),
                    'POWATAG_VIDEO' => array(
                        'title' => $this->l('Video'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'type' => 'bool',
                    ),
                    'POWATAG_DEBUG' => array(
                        'title' => $this->l('Developer mode'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'type' => 'bool',
                    ),
                ),
                'submit' => array('title' => $this->l('Save')),
            ),
            'logs' => array(
                'title' => $this->l('Logs'),
                'image' => '../img/t/AdminLogs.gif',
                'fields' => array(
                    'POWATAG_API_LOG' => array(
                        'title' => $this->l('Enable applicative logging'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'type' => 'bool',
                        'visibility' => Shop::CONTEXT_SHOP,
                    ),
                    'POWATAG_REQUEST_LOG' => array(
                        'title' => $this->l('Enable request logging'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'type' => 'bool',
                        'visibility' => Shop::CONTEXT_SHOP,
                    ),
                ),
                'submit' => array('title' => $this->l('Save')),
            ),
        );
    }

    public function setMedia()
    {
        parent::setMedia();
        $this->addCSS($this->module->getPathUri().'views/css/backoffice.css');
    }

    public function initToolbar()
    {
        $this->toolbar_btn = $this->module->initToolbar();
        parent::initToolbar();
    }

    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_btn = $this->module->initToolbar();
        parent::initPageHeaderToolbar();
    }

    public function renderOptions()
    {
        $this->context->smarty->assign(array(
            'marketing' => !Configuration::get('POWATAG_HMAC_KEY') || !Configuration::get('POWATAG_API_KEY'),
        ));
        $before = $this->module->display(dirname(__FILE__).'/../../'.$this->module->name.'.php', 'powatag_configuration_before.tpl');
        $form = parent::renderOptions();
        $after = $this->module->display(dirname(__FILE__).'/../../'.$this->module->name.'.php', 'powatag_configuration_after.tpl');

        return $before.$form.$after;
    }

    public static function install($menu_id, $module_name)
    {
        PowatagTotAdminTabHelper::addAdminTab(array(
            'id_parent' => $menu_id,
            'className' => 'AdminPowaTagConfiguration',
            'default_name' => 'Configuration',
            'name' => 'Configuration',
            'position' => 0,
            'active' => true,
            'module' => $module_name,
        ));
    }
}
