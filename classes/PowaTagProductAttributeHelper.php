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

class PowaTagProductAttributeHelper
{
    public static function getCombinationByCode($id_product, $code)
    {
        $id_pa = (int) self::getCombinationIdByIdCombination($id_product, $code);

        return $id_pa;
    }

    private static function getCombinationIdByIdCombination($id_product, $id_combination)
    {
        $datas = explode('-', $id_combination);

        if (empty($datas[1])) {
            return 0;
        }

        $query = new DbQuery();
        $query->select('pa.id_product_attribute');
        $query->from('product_attribute', 'pa');
        $query->where('pa.id_product_attribute = \''.pSQL($datas[1]).'\'');
        $query->where('pa.id_product = '.(int) $id_product);

        return Db::getInstance()->getValue($query);
    }

    public static function getVariantCode($combination)
    {
        $combination_sku = self::constructCombinationSKU($combination);

        return $combination_sku;
    }

    private static function constructCombinationSKU($combination)
    {
        return $combination['id_product'].'-'.$combination['id_product_attribute'];
    }
}
