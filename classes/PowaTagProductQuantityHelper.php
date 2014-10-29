<?php 

class PowaTagProductQuantityHelper {

	static public function getProductQuantity($product, $id_product_attribute = false)
	{
		$allow_oosp = self::isAllowOOSP($product);
		$qty = Product::getQuantity($product->id, $id_product_attribute);
		if($qty > 0)
			return $qty;
		if($allow_oosp)
			return 1000;
		return $qty;
	}

	static public function getCombinationQuantity($combination)
	{
		$product = new Product($combination['id_product']);
		$allow_oosp = self::isAllowOOSP($product);
		$qty = $combination['quantity'];

		if($qty > 0)
			return $qty;
		if($allow_oosp)
			return 1000;
		return $qty;
	}

	static private function isAllowOOSP($product)
	{
		return Product::isAvailableWhenOutOfStock($product->out_of_stock);
	}



}