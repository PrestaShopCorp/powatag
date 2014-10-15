<?php

abstract class PowaTagValidate
{

	public static function currencyEnable($currency)
	{
		return Validate::isLoadedObject($currency) && $currency->active;
	}

	public static function countryEnable($country)
	{
		return Validate::isLoadedObject($country) && $country->active;
	}

}

?>