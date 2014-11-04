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

class PowaTagProductHelper {

	public static function getProductSKU($product)
	{
		$powatag_sku = Configuration::get('POWATAG_SKU');
		switch ($powatag_sku) 
		{
			case Powatag::EAN :
				$product_sku = $product->ean13;	
				break;
			case Powatag::UPC :
				$product_sku = $product->upc;	
			case Powatag::REFERENCE :
				$product_sku = $product->reference;	
			default:
				$product_sku = $product->id;
				break;
		}
		if($product_sku == '')
			$product_sku = $product->id;

		return $product_sku;
	}

	/**
	 * Get Product object by code
	 * @param  string $code Code
	 * @return Product      Product object
	 */
	public static function getProductByCode($code, $id_lang)
	{
		$powatag_sku = Configuration::get('POWATAG_SKU');
		
		switch ($powatag_sku) 
		{
			case Powatag::EAN :
				$id_product = (int)self::getProductIdByEan13($code);	
				break;
			case Powatag::UPC :
				$id_product = (int)self::getProductIdByUPC($code);
			case Powatag::REFERENCE :
				$id_product = (int)self::getProductIdByReference($code);
			default:
				$id_product = (int)self::getProductIdByIdProduct($code);
				break;
		}
		if($id_product == 0)
			$id_product = (int)self::getProductIdByIdProduct($code);

		
		$product = new Product($id_product, true, (int)$id_lang);

		return $product;
	}

	private static function getProductIdByIdProduct($code)
	{
		return $code;
	}

	private static function getProductIdByReference($reference)
	{
		if (empty($reference))
			return 0;
		
		if(!Validate::isReference($reference))
			return 0;

		$query = new DbQuery();
		$query->select('p.id_product');
		$query->from('product', 'p');
		$query->where('p.reference = \''.pSQL($reference).'\'');

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
	}

	private static function getProductIdByEan13($code)
	{
		return Product::getIdByEan13($code);
	}

	private static function getProductIdByUPC($upc)
	{
		if (empty($upc))
			return 0;
		
		if(!Validate::isUpc($upc))
			return 0;

		$query = new DbQuery();
		$query->select('p.id_product');
		$query->from('product', 'p');
		$query->where('p.upc = \''.pSQL($upc).'\'');

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
	}
}