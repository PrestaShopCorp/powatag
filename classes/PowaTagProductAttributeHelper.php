<?php 

class PowaTagProductAttributeHelper {

	public static function getCombinationByCode($id_product, $code)
	{
		$id_pa = (int)self::getCombinationIdByIdCombination($id_product, $code);
		return $id_pa;
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
		$combination_sku = self::constructCombinationSKU($combination);
		return $combination_sku;
		
	}

	private static function constructCombinationSKU($combination)
	{
		return $combination['id_product'].'-'.$combination['id_product_attribute'];
	}


}