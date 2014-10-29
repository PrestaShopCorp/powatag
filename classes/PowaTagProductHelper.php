<?php 

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