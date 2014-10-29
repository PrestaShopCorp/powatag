<?php

abstract class PowaTagAbstract
{

	public static $BAD_REQUEST      = array('code' => 'BAD_REQUEST',      'response' => 400);
	public static $SHOP_NOT_FOUND   = array('code' => 'SHOP_NOT_FOUND',   'response' => 404);
	public static $SKU_NOT_FOUND    = array('code' => 'SKU_NOT_FOUND',    'response' => 404);
	public static $NOT_IN_STOCK     = array('code' => 'NOT_IN_STOCK',     'response' => 400);
	public static $INVALID_PAYMENT  = array('code' => 'INVALID_PAYMENT',  'response' => 400);
	public static $UNEXPECTED_ERROR = array('code' => 'UNEXPECTED_ERROR', 'response' => 500);

	/**
	 * Request datas
	 * @var array
	 */
	protected $datas;

	/**
	 * Current context
	 * @var Context
	 */
	protected $context;

	/**
	 * Module
	 * @var Module
	 */
	protected $module;

	/**
	 * Errors
	 * @var array
	 */
	protected $error = array();

	/**
	 * Total without tax
	 * @var integer
	 */
	protected $subTotal = 0;

	/**
	 * Total with tax
	 * @var integer
	 */
	protected $subTotalWt = 0;

	/**
	 * Total tax for products
	 * @var integer
	 */
	protected $subTax = 0;

	public function __construct(stdClass $datas)
	{
		$this->datas = $datas;
		$this->context = Context::getContext();
		$this->module = Module::getInstanceByName('powatag');
		$this->initLang();
	}
	
	public function initLang()
	{
		
		if($iso = Tools::getValue('lang'))
		{
			$lang = substr($iso, 0, 2);
			if($language_id = Language::getIdByIso($lang))
				$this->context->language = new Language($language_id);

		}
	}

	/**
	 * Get error
	 * @return string Error
	 */
	public function getError()
	{
		
		return $this->error;
	}

	public function addError($message, $error = null)
	{

		if (is_null($error))
			$error = PowaTagAbstract::$UNEXPECTED_ERROR;

		if (count($this->error))
			return;

		$this->error = array(
			"error"   => $error,
			"message" => $message
		);
	}

	/**
	 * Get currency object by iso_code
	 * @param  string $iso_code ISO code
	 * @return Currency         Currency Object
	 */
	protected function getCurrencyByIsoCode($iso_code)
	{
		$idCurrency = (int)Currency::getIdByIsoCode($iso_code);
		$currency = new Currency($idCurrency);


		if (!PowaTagValidate::currencyEnable($currency))
		{
			$this->addError(sprintf($this->module->l('Currency not found : %s'), $iso_code));
			return false;
		}

		return $currency;
	}


	/**
	 * Get Country object by code
	 * @param  string $code Code
	 * @return Country      Country object
	 */
	protected function getCountryByCode($code)
	{
		$idCountry = (int)Country::getByIso($code);
		$country = new Country($idCountry, (int)$this->context->language->id);

		return $country;
	}

	/**
	 * Calculate total of products without tax
	 * @return float Total of products
	 */
	protected function getSubTotal($products, $codeCountry, $check = true)
	{

		if (Validate::isInt($codeCountry))
			$country = new Country($codeCountry);
		else if (!$codeCountry instanceof Country)
			$country = $this->getCountryByCode($codeCountry);

		$address = Address::initialize();
		$address->id_country = $country->id;
		if ($products && count($products))
		{
			foreach ($products as $p)
			{

				$product = PowaTagProductHelper::getProductByCode($p->product->code, $this->context->language->id);

				if (!Validate::isLoadedObject($product))
				{
					$this->addError(sprintf($this->module->l('This product does not exists : %s'), $p->product->code), PowaTagAbstract::$SKU_NOT_FOUND);
					return false;
				}



				$variants = $p->product->productVariants;

				$product_rate = 1 + ($product->getTaxesRate($address) / 100);

				foreach ($variants as $variant)
				{
					$variantCurrency = $this->getCurrencyByIsoCode($variant->finalPrice->currency);

					if (!PowaTagValidate::currencyEnable($variantCurrency))
					{
						$this->addError(sprintf($this->module->l('Currency not found : %s'), $variantCurrency));
						return false;
					}

					$variantAmount = $variant->finalPrice->amount;

					if ($idProductAttribute = PowaTagProductAttributeHelper::getCombinationByCode($product->id, $variant->code))
					{
						$priceAttribute   = $product->getPrice(true, $idProductAttribute);
						$qtyInStock = Product::getQuantity($product->id, $idProductAttribute);
					}
					else if (Validate::isInt($variant->code))
					{
						$priceAttribute   = $product->getPrice(true);
						$qtyInStock = Product::getQuantity($product->id);
					}
					else
					{
						$this->addError(sprintf($this->module->l('This variant does not exist : %s'), $variant->code), PowaTagAbstract::$SKU_NOT_FOUND);
						return false;
					}

					$priceAttributeWt = $priceAttribute * $product_rate;

					$priceAttribute   = Tools::ps_round($priceAttribute, 2);
					$variantAmount    = Tools::ps_round($variantAmount, 2);

					$this->convertToCurrency($variantAmount, $variantCurrency, false);

					$priceAttribute   = Tools::ps_round($priceAttribute, 2);
					$variantAmount    = Tools::ps_round($variantAmount, 2);
					$priceAttributeWt = Tools::ps_round($priceAttributeWt, 2);

					if ($check && $priceAttribute != $variantAmount)
					{
						$this->addError(sprintf($this->module->l('Price variant is different with the price shop : %s %s != %s'), $variant->code, $priceAttribute, $variantAmount));
						return false;
					}


					if ($qtyInStock == 0)
					{
						$this->addError(sprintf($this->module->l('No Stock Available'), $variant->code), PowaTagAbstract::$NOT_IN_STOCK);
						return false;
					}

					if ($qtyInStock < $p->quantity)
					{
						$this->addError(sprintf($this->module->l('Quantity > Stock Count'), $variant->code), PowaTagAbstract::$NOT_IN_STOCK);
						return false;
					}

					$totalPriceAttribute = ($priceAttribute * $p->quantity);
					$totalPriceAttributeWt = ($priceAttributeWt * $p->quantity);

					$this->subTotal   += $totalPriceAttribute;
					$this->subTotalWt += $totalPriceAttributeWt;
					$this->subTax     += ($totalPriceAttributeWt - $totalPriceAttribute);

				}

			}
			return true;
		}
		else
			return false;

	}

	/**
	 * Calculate shipping costs without tax
	 * @return float Shipping costs
	 */
	protected function getShippingCost($products, Currency $currency, $country, $useTax = true)
	{

		$idCarrier = (int)Configuration::get('POWATAG_SHIPPING');

		if (!$country instanceof Country)
		{
			if (Validate::isInt($country))
				$country = new Country((int)$country, (int)$this->context->language->id);
			else
				$country = $this->getCountryByCode($country);
		}

		if (!PowaTagValidate::countryEnable($country))
		{
			$this->addError(sprintf($this->module->l('Country is does not exists or does not enable for this shop : %s'), $country->iso_code));
			return false;
		}

		$shippingCost = $this->getShippingCostByCarrier($products, $currency, $idCarrier, $country, $useTax);

		if (Validate::isFloat($shippingCost))
			return $shippingCost;
		else
			return false;
	}

	/**
	 * Get Shipping By barrier
	 * @param  int     $idCarrier ID Carrier
	 * @param  Country $country    Country
	 * @param  float   $subTotal   Total Products
	 * @param  boolean $useTax    If use tax
	 * @return float               Shipping Costs
	 */
	private function getShippingCostByCarrier($products, Currency $currency, $idCarrier, Country $country, $useTax = false)
	{
		$productLists = $products;

		$shippingCost = 0;

		$idZone = (int)$country->id_zone;

		$carrier = new Carrier($idCarrier, (int)$this->context->language->id);

		if (!$this->ifCarrierDeliveryZone($carrier, $idZone))
			return false;

		$address = new Address();
		$address->id_country = (int)$country->id;
		$address->id_state = 0;
		$address->postcode = 0;

		if ($useTax && !Tax::excludeTaxeOption())
			$carrier_tax = $carrier->getTaxesRate($address);

		$configuration = Configuration::getMultiple(array(
			'PS_SHIPPING_FREE_PRICE',
			'PS_SHIPPING_HANDLING',
			'PS_SHIPPING_METHOD',
			'PS_SHIPPING_FREE_WEIGHT'
		));

		$shippingMethod = $carrier->getShippingMethod();

		// Get shipping cost using correct method
		if ($carrier->range_behavior)
		{

			if (($shippingMethod == Carrier::SHIPPING_METHOD_WEIGHT && !Carrier::checkDeliveryPriceByWeight($carrier->id, 0, (int)$idZone))
			|| ($shippingMethod == Carrier::SHIPPING_METHOD_PRICE && !Carrier::checkDeliveryPriceByPrice($carrier->id, $this->subTotalWt, $idZone, (int)$this->id_currency)
			))
				$shippingCost += 0;
			else
			{
				if ($shippingMethod == Carrier::SHIPPING_METHOD_WEIGHT)
					$shippingCost += $carrier->getDeliveryPriceByWeight(0, $idZone);
				else // by price
					$shippingCost += $carrier->getDeliveryPriceByPrice($this->subTotalWt, $idZone, (int)$currency->id);
			}
		}
		else
		{
			if ($shippingMethod == Carrier::SHIPPING_METHOD_WEIGHT)
				$shippingCost += $carrier->getDeliveryPriceByWeight(0, $idZone);
			else
				$shippingCost += $carrier->getDeliveryPriceByPrice($this->subTotalWt, $idZone, (int)$currency->id);
		}

		if (isset($configuration['PS_SHIPPING_HANDLING']) && $carrier->shipping_handling)
			$shippingCost += (float)$configuration['PS_SHIPPING_HANDLING'];

		foreach ($productLists as $p)
		{
			$product = new Product($p->product->code);
			$shippingCost += $product->additional_shipping_cost;
		}

		// Apply tax
		if ($useTax && isset($carrier_tax))
			$shippingCost *= 1 + ($carrier_tax / 100);

		$shippingCost = (float)Tools::ps_round((float)$shippingCost, 2);

		return $shippingCost;
	}
	
	private function isCarrierInRange($carrier, $idZone)
	{

		if (!$carrier->range_behavior)
			return true;

		$shipping_method = $carrier->getShippingMethod();

		if ($shipping_method == Carrier::SHIPPING_METHOD_FREE)
			return true;

		$check_delivery_price_by_weight = Carrier::checkDeliveryPriceByWeight(
			(int)$idCarrier,
			null,
			$idZone
		);

		if ($shipping_method == Carrier::SHIPPING_METHOD_WEIGHT && $check_delivery_price_by_weight)
			return true;

		$check_delivery_price_by_price = Carrier::checkDeliveryPriceByPrice(
			(int)$idCarrier,
			$this->subTotal,
			$idZone,
			(int)$this->id_currency
		);

		if ($shipping_method == Carrier::SHIPPING_METHOD_PRICE && $check_delivery_price_by_price)
			return true;

		return false;
	}

	/**
	 * Calculate tax (Shipping + Products)
	 * @return float Total tax
	 */
	protected function getTax($products, Currency $currency, $country)
	{

		$idCarrier = (int)Configuration::get('POWATAG_SHIPPING');

		if (!$country instanceof Country)
			$country = new Country($country);

		$tax = $this->subTax;
		$shippingCostWt = $this->getShippingCostByCarrier($products,  $currency, $idCarrier, $country, $this->subTotal, true);
		$tax += ($shippingCostWt - $this->shippingCost);

		return (float)Tools::ps_round($tax, 2);
	}

	/**
	 * Check if customer has tax
	 * @param  mix $customer Customer information (id|email|object)
	 * @return boolean       Tax enable
	 */
	protected function taxEnableByCustomer($customer)
	{
		
		if (!Validate::isLoadedObject($customer))
		{
			if (Validate::isEmail($customer))
				$customer = self::getCustomerByEmail($customer);
			else if (Validate::isInt($customer))
				$customer = new Customer((int)$customer);
		}

		return !Group::getPriceDisplayMethod((int)$customer->id_default_group);
	}

	protected static function getCustomerByEmail($email, $register = false, $lastName = null, $firstName = null, $emailAddress = null)
	{
		$customer = new Customer();
		$customer->getByEmail($email);

		if (!Validate::isLoadedObject($customer) && $register)
		{
			if (PowaTagAPI::apiLog())
				PowaTagLogs::initAPILog('Create customer', PowaTagLogs::IN_PROGRESS, 'Customer : '.$lastName . ' ' . $firstName);

			$customer->lastname  = $lastName;
			$customer->firstname = $firstName;
			$customer->email     = $emailAddress;
			$customer->setWsPasswd(substr($customer->lastname, 0, 1).$firstName);

			if (!$customer->save())
			{
				$this->addError($this->module->l("Impossible to save customer"));

				if (PowaTagAPI::apiLog())
					PowaTagLogs::initAPILog('Create customer', PowaTagLogs::ERROR, $this->error['message']);

				return false;
			}

			if (PowaTagAPI::apiLog())
				PowaTagLogs::initAPILog('Create customer', PowaTagLogs::SUCCESS, 'Customer ID : '.$customer->id);

		}

		return $customer;
	}

	protected function formatNumber($number, $precision = 0)
	{

		$number = Tools::ps_round($number, $precision);

		return number_format($number, 2, ".", "");
	}

	
	protected function ifCarrierDeliveryZone($carrier, $idZone = false, $country = false)
	{
		if (!$carrier instanceof Carrier)
		{
			if (Validate::isInt($carrier))
				$carrier = new Carrier((int)$carrier);
			else
			{
				$this->addError($this->module->l("Error since load carrier"));
				return false;
			}
		}

		if (!$idZone && !$country)
		{
			$this->addError($this->module->l("Thanks to fill country or id zone"));
			return false;
		}
		else if (!$idZone && $country)
		{
			if (!$country instanceof Country)
			{
				if (Validate::isInt($country))
					$country = new Country($country);
				else
					$country = self::getCountryByCode($country);
			}

			if (!PowaTagValidate::countryEnable($country))
			{
				$this->addError($this->module->l("Country does not exists or not active"));
				return false;
			}

			$idZone = (int)$country->id_zone;
		}

		if (!$this->isCarrierInRange($carrier, $idZone))
		{
			$this->addError(sprintf($this->module->l('Carrier not delivery in : %s'), $country->name));
			return false;
		}

		if (!$carrier->active)
		{
			$this->addError(sprintf($this->module->l('Carrier is not active : %s'), $carrier->name));
			return false;
		}

		if ($carrier->is_free == 1)
			return 0;

		$shippingMethod = $carrier->getShippingMethod();

		// Get only carriers that are compliant with shipping method
		if (($shippingMethod == Carrier::SHIPPING_METHOD_WEIGHT && $carrier->getMaxDeliveryPriceByWeight($idZone) === false)
			|| ($shippingMethod == Carrier::SHIPPING_METHOD_PRICE && $carrier->getMaxDeliveryPriceByPrice($idZone) === false))
		{
			$this->addError(sprintf($this->module->l('Carrier not delivery for this shipping method in ID Zone : %s'), $idZone));
			return false;
		}

		return true;
	}

	protected function convertToCurrency(&$amount, $currency, $toCurrency = true)
	{
		if ($currency->iso_code != $this->context->currency->iso_code)
			$amount = Tools::convertPrice($amount, $variantCurrency, $toCurrency);	
	}

	/**
	 * Create or Updates Prestashop address
	 * @return Address Address object
	 */
	protected function createAddress($addressInformations, $address = null)
	{

		$country = $this->getCountryByCode($addressInformations->country->alpha2Code);

		if (!$country->active)
		{
			$this->addError(sprintf($this->module->l('This country is not active : %s'), $addressInformations->country->alpha2Code));
			return false;
		}
		
		if (!isset($addressInformations->friendlyName))
			$friendlyName = $this->module->l('My address');
		else
			$friendlyName = $addressInformations->friendlyName;

		if (PowaTagAPI::apiLog())
			PowaTagLogs::initAPILog('Create address', PowaTagLogs::IN_PROGRESS, $addressInformations->lastName.' '.$addressInformations->firstName.' : '.$friendlyName);

		$address = $address != null ? $address : Address::initialize();
		$address->id_customer = (int)$this->customer->id;
		$address->id_country  = (int)$country->id;
		$address->alias       = $friendlyName;
		$address->lastname    = $addressInformations->lastName;
		$address->firstname   = $addressInformations->firstName;
		$address->address1    = $addressInformations->line1;
		$address->address2    = $addressInformations->line2;
		$address->postcode    = $addressInformations->postCode;
		$address->city        = $addressInformations->city;
		$address->phone       = isset($addressInformations->phone) ? $addressInformations->phone : '0000000000' ;
		$address->id_state    = isset($addressInformations->state) ? (int)State::getIdByIso($addressInformations->state, (int)$country->id) : 0;

		if (!$address->save())
		{

			$this->addError($this->module->l("Impossible to save address"));

			if (PowaTagAPI::apiLog())
				PowaTagLogs::initAPILog('Create address', PowaTagLogs::ERROR, $this->error['message']);

			return false;
		}

		if (PowaTagAPI::apiLog())
			PowaTagLogs::initAPILog('Create address', PowaTagLogs::SUCCESS, 'Address ID : '. $address->id);

		return $address;
	}

} 

?>

