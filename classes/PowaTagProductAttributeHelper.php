<?php 

class PowaTagProductAttributeHelper {

	public static function getCombinationByCode($id_product, $code)
	{
		$powatag_sku = Configuration::get('POWATAG_SKU');
		switch ($powatag_sku) 
		{
			case Powatag::EAN :
				$id_pa = (int)self::getCombinationIdByEan13($id_product, $code);	
				break;
			case Powatag::UPC :
				$id_pa = (int)self::getCombinationIdByUPC($id_product, $code);
			case Powatag::REFERENCE :
				$id_pa = (int)self::getCombinationIdByReference($id_product, $code);
			default:
				$id_pa = (int)self::getCombinationIdByIdCombination($id_product, $code);
				break;
		}
		if($id_pa == 0)
			$id_pa = (int)self::getCombinationIdByIdCombination($id_product, $code);

		return $id_pa;
	}

	private static function getCombinationIdByEan13($id_product, $ean13)
	{
		if (empty($ean13))
			return 0;

		$query = new DbQuery();
		$query->select('pa.id_product_attribute');
		$query->from('product_attribute', 'pa');
		$query->where('pa.ean13 = \''.pSQL($ean13).'\'');
		$query->where('pa.id_product = '.(int)$id_product);

		return Db::getInstance()->getValue($query);
	}

	private static function getCombinationIdByUpc($id_product, $upc)
	{
		if (empty($upc))
			return 0;

		$query = new DbQuery();
		$query->select('pa.id_product_attribute');
		$query->from('product_attribute', 'pa');
		$query->where('pa.upc = \''.pSQL($upc).'\'');
		$query->where('pa.id_product = '.(int)$id_product);

		return Db::getInstance()->getValue($query);
	}

	private static function getCombinationIdByReference($id_product, $reference)
	{
		if (empty($reference))
			return 0;

		$query = new DbQuery();
		$query->select('pa.id_product_attribute');
		$query->from('product_attribute', 'pa');
		$query->where('pa.reference = \''.pSQL($reference).'\'');
		$query->where('pa.id_product = '.(int)$id_product);

		return Db::getInstance()->getValue($query);
	}

	private static function getCombinationIdByIdCombination($id_product, $id_combination)
	{
		$datas = explode('-', $id_combination);

		if (empty($datas[1]))
			return 0;

		$query = new DbQuery();
		$query->select('pa.id_product_attribute');
		$query->from('product_attribute', 'pa');
		$query->where('pa.id_product_attribute = \''.pSQL($datas[1]).'\'');
		$query->where('pa.id_product = '.(int)$id_product);

		return Db::getInstance()->getValue($query);
	}



	public static function getVariantCode($combination)
	{	
		$powatag_sku = Configuration::get('POWATAG_SKU');
		switch ($powatag_sku) 
		{
			case Powatag::EAN :
				$combination_sku = $combination['ean13'];	
				break;
			case Powatag::UPC :
				$combination_sku = $combination['upc'];	
			case Powatag::REFERENCE :
				$combination_sku = $combination['reference'];	
			default:
				$combination_sku = self::constructCombinationSKU($combination);
				break;
		}
		if($combination_sku == '')
			$combination_sku = self::constructCombinationSKU($combination);

		return $combination_sku;
		
	}

	private static function constructCombinationSKU($combination)
	{
		return $combination['id_product'].'-'.$combination['id_product_attribute'];
	}


}