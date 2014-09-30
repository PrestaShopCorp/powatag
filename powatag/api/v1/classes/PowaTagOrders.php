<?php

class PowaTagOrders extends PowaTagAbstract
{
	/**
	 * Datas customer
	 * @var \stdClass
	 */
	private $customerDatas;

	/**
	 * Datas costs
	 * @var \stdClass
	 */
	private $costsDatas;

	/**
	 * Datas products
	 * @var \stdClass
	 */
	private $productsDatas;

	/**
	 * Datas device
	 * @var \stdClass
	 */
	private $deviceDatas;

	/**
	 * Datas paymentResult
	 * @var \stdClass
	 */
	private $paymentResultDatas;

	/**
	 * Customer Prestashop
	 * @var \Customer
	 */
	private $customer;

	/**
	 * Address Prestashop
	 * @var \Address
	 */
	private $address;

	/**
	 * Cart Prestashop
	 * @var \Cart
	 */
	private $cart;

	public function __construct(stdClass $datas)
	{
		parent::__construct($datas);

		if (isset($this->datas->order))
			$order = $this->datas->order;
		else
			$order = current($this->datas->orders);

		$this->customerDatas = $order->customer;
		$this->costsDatas    = $order->orderCostSummary;
		$this->productsDatas = $order->orderLineItems;
		$this->deviceDatas   = $order->device;

		if (isset($this->datas->paymentResult))
			$this->paymentResultDatas = $this->datas->paymentResult;

		$this->initObjects();
	}

	/**
	 * Init objects necessary for orders
	 */
	private function initObjects()
	{
		
		$this->customer = PowaTagOrders::getCustomerByEmail($this->customerDatas->emailAddress, true, $this->customerDatas->lastName, $this->customerDatas->firstName, $this->customerDatas->emailAddress);


		$addresses = $this->customer->getAddresses((int)$this->context->language->id);

		$find = false;

		foreach ($addresses as $addr)
		{
			if ($addr['alias'] == $this->customerDatas->shippingAddress->friendlyName)
			{
				$find = true;
				$address = new Address((int)$addr['id_address']);
				break;
			}
		}

		if (!$find)
			$address = $this->createAddress($this->customerDatas->shippingAddress);

		if (Validate::isLoadedObject($address))
			$this->address = $address;
		else
			return false;
	}	

	public function validateOrder()
	{
		$createCart = $this->createCart();

		$idOrder = $createCart;

		if ($this->paymentResultDatas)
		{
			$orderState = (int)Configuration::get('PS_OS_PAYMENT');

			if (!$createCart)
				$orderState = (int)Configuration::get('PS_OS_ERROR');

			$addresses = $this->customer->getAddresses((int)$this->context->language->id);

			$find = $address = false;

			foreach ($addresses as $addr)
			{
				if ($addr['alias'] == $this->paymentResultDatas->paymentCart->billingAddress->friendlyName)
				{
					$find = true;
					$address = new Address((int)$addr['id_address']);
					break;
				}
			}

			if ($address || ($address = !$this->createAddress($this->paymentResultDatas->paymentCart->billingAddress)))
			{
				$this->cart->id_address_invoice = $address->id;
				$this->cart->save();
			}
			else
				$orderState = (int)Configuration::get('PS_OS_ERROR');

			$payment = new PowaTagPayment();
			$payment->setBantAuthorizationCode($this->paymentResultDatas->bankAuthorizationCode);
			$idOrder = $payment->validateOrder($orderState, (int)$this->cart->id, $this->paymentResultDatas->amountTotal->amount, $this->error);
		}

		if ($createCart)
		{
			$transaction              = new PowaTagTransaction();
			$transaction->id_cart     = (int)$this->cart->id;
			$transaction->id_order    = (int)$idOrder;
			$transaction->id_customer = (int)$this->customer->id;
			$transaction->id_device   = $this->deviceDatas->deviceID;
			$transaction->ip_address  = $this->deviceDatas->ipAddress;
			$transaction->order_state = isset($orderState) ? (int)$orderState : 0;

			$transaction->save();
		}

		return $idOrder;
	}

	private function createCart()
	{

		if (!$currency = $this->getCurrencyByIsoCode($this->costsDatas->total->currency))
			return false;

		$subTotal = $this->getSubTotal($this->productsDatas, (int)$this->address->id_country);

		if (!$subTotal)
			return false;

		$totalAmount       = (float)$this->costsDatas->total->amount;
		$totalSubTotal     = (float)$this->costsDatas->subTotal->amount;
		$totalShippingCost = (float)$this->costsDatas->shippingCost->amount;
		$totalTax          = (float)$this->costsDatas->tax->amount;

		$this->convertToCurrency($totalSubTotal, $currency, false);
		$this->convertToCurrency($totalShippingCost, $currency, false);
		$this->convertToCurrency($totalTax, $currency, false);
		$this->convertToCurrency($totalAmount, $currency, false);

		$totalAmount       = $this->formatNumber($totalAmount, 2);

		$sum = $this->formatNumber($totalSubTotal + $totalShippingCost + $totalTax, 2);

		if ($totalAmount != $sum)
		{
			$this->error = sprintf($this->module->l('The total amount is not correct with the others total : %s != %s'), $totalAmount, $sum);
			return false;
		}

		$totalSubTotal     = $this->formatNumber($totalSubTotal, 2);
		$totalShippingCost = $this->formatNumber($totalShippingCost, 2);
		$totalTax          = $this->formatNumber($totalTax, 2);

		if ($totalSubTotal != $subTotal)
		{
			$this->error = sprintf($this->module->l('The subtotal is not correct : %s != %s'), $totalSubTotal, $subTotal);
			return false;
		}
		
		$this->shippingCost = $this->getShippingCost($this->productsDatas, $currency, (int)$this->address->id_country, false);
		$this->shippingCostWt = $this->getShippingCost($this->productsDatas, $currency, (int)$this->address->id_country, true);

		if (!$this->shippingCost || !Validate::isFloat($this->shippingCost))
		{
			$this->error = sprintf($this->module->l('Error with shippingCost : %s'), $this->shippingCost);
			return false;
		}

		if ($totalShippingCost != $this->shippingCost)
		{
			$this->error = sprintf($this->module->l('The total shipping cost is not correct : %s != %s'), $totalShippingCost, $this->shippingCost);
			return false;
		}

		if ($totalTax != ($tax = $this->getTax($this->productsDatas, $currency, (int)$this->address->id_country)))
		{
			$this->error = sprintf($this->module->l('The total tax is not correct : %s != %s'), $totalTax, $tax);
			return false;
		}

		$totalWithShipping = $this->formatNumber($this->subTotalWt + $this->shippingCost + ($this->shippingCostWt - $this->shippingCost), 2);

		if ($totalAmount != $totalWithShipping)
		{
			$this->error = sprintf($this->module->l('The total amount is not correct : %s != %s'), $totalAmount, $totalWithShipping);
			return false;
		}

		if (PowaTagAPI::apiLog())
			PowaTagLogs::initAPILog('Create cart', PowaTagLogs::IN_PROGRESS, $this->customerDatas->shippingAddress->lastName.' '.$this->customerDatas->shippingAddress->firstName);


		$cart = new Cart();
		$cart->id_carrier          = (int)Configuration::get('POWATAG_SHIPPING');
		$cart->delivery_option     = serialize(array($this->address->id => $cart->id_carrier.','));
		$cart->id_lang             = (int)$this->context->language->id;
		$cart->id_address_delivery = (int)$this->address->id;
		$cart->id_address_invoice  = (int)$this->address->id;
		$cart->id_currency         = (int)$currency->id;
		$cart->id_customer         = (int)$this->customer->id;
		$cart->secure_key          = $this->customer->secure_key;

		if (!$cart->save())
		{
			$this->error = $this->module->l("Impossible to save cart");

			if (PowaTagAPI::apiLog())
				PowaTagLogs::initAPILog('Create cart', PowaTagLogs::ERROR, $this->error);

			return false;
		}

		if (PowaTagAPI::apiLog())
			PowaTagLogs::initAPILog('Create cart', PowaTagLogs::SUCCESS, "Cart ID : ".$cart->id);


		$this->cart = $cart;

		if (!$this->addProductsToCart($cart, $this->address->id_country)) 
			return false;

		return $this->cart->id;
	}

	/**
	 * Add Products to cart
	 * @param Cart $cart Cart object
	 */
	private function addProductsToCart($cart, $codeCountry)
	{
		$products = $this->productsDatas;

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
				if (PowaTagAPI::apiLog())
					PowaTagLogs::initAPILog('Add product to cart', PowaTagLogs::IN_PROGRESS, "Product : ".$p->product->code);

				$product = $this->getProductByCode($p->product->code);

				if (!Validate::isLoadedObject($product))
				{
					$this->error = sprintf($this->module->l('This product does not exists : %s'), $p->product->code);

					if (PowaTagAPI::apiLog())
						PowaTagLogs::initAPILog('Add product to cart', PowaTagLogs::ERROR, "Product : ".$this->error);

					return false;
				}

				$variants = $p->product->productVariants;

				$product_rate = 1 + ($product->getTaxesRate($address) / 100);

				foreach ($variants as $variant)
				{
					$variantCurrency = $this->getCurrencyByIsoCode($variant->finalPrice->currency);

					if (!PowaTagValidate::currencyEnable($variantCurrency))
					{
						$this->error = sprintf($this->module->l('Currency not found : %s'), $variant->code);

						if (PowaTagAPI::apiLog())
							PowaTagLogs::initAPILog('Add product to cart', PowaTagLogs::ERROR, "Product : ".$this->error);
						return false;
					}

					$variantAmount = $variant->finalPrice->amount;

					$idProductAttribute = false;

					if ($idProductAttribute = $this->getCombinationByEAN13($product->id, $variant->code))
					{
						$priceAttribute   = $product->getPrice(false, $idProductAttribute);
						$qtyInStock = Product::getQuantity($product->id, $idProductAttribute);
					}
					else if (Validate::isInt($variant->code))
					{
						$priceAttribute   = $product->getPrice(false);
						$qtyInStock = Product::getQuantity($product->id);
					}
					else
					{
						$this->error = sprintf($this->module->l('This variant does not exist : %s'), $variant->code);

						if (PowaTagAPI::apiLog())
							PowaTagLogs::initAPILog('Add product to cart', PowaTagLogs::ERROR, "Product : ".$this->error);

						return false;
					}

					$priceAttributeWt = $priceAttribute * $product_rate;

					$priceAttribute   = $this->formatNumber($priceAttribute, 2);
					$variantAmount    = $this->formatNumber($variantAmount, 2);

					$this->convertToCurrency($variantAmount, $variantCurrency, false);

					$priceAttribute   = $this->formatNumber($priceAttribute, 2);
					$variantAmount    = $this->formatNumber($variantAmount, 2);
					$priceAttributeWt = $this->formatNumber($priceAttributeWt, 2) * $p->quantity;

					$this->subTotalWt += $priceAttributeWt;

					if ($priceAttribute != $variantAmount)
					{
						$this->error = sprintf($this->module->l('Price variant is different with the price shop : %s != %s'), $priceAttribute, $variantAmount);

						if (PowaTagAPI::apiLog())
							PowaTagLogs::initAPILog('Add product to cart', PowaTagLogs::ERROR, "Product : ".$this->error);

						return false;
					}

					if ($qtyInStock < $p->quantity)
					{
						$this->error = sprintf($this->module->l('Quantity > Stock Count : %s'), $variant->code);

						if (PowaTagAPI::apiLog())
							PowaTagLogs::initAPILog('Add product to cart', PowaTagLogs::ERROR, "Product : ".$this->error);

						return false;
					}

					$cart->updateQty($p->quantity, $product->id, $idProductAttribute);

					if (PowaTagAPI::apiLog())
						PowaTagLogs::initAPILog('Add product to cart', PowaTagLogs::SUCCESS, "Cart ID : ".$cart->id." - Product ID : ".$product->id);
				}

			}
		}
		else
		{
			$this->error = $this->module->l("No product found in request");
			return false;
		}

		return true;
	}
}

?>