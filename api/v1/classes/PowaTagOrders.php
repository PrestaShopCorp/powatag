<?php

class PowaTagOrders extends PowaTagAbstract
{

	/**
	 * Customer Prestashop
	 * @var \Customer
	 */
	protected $customer;

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

		$this->datas->customer         = $order->customer;

		
		$this->datas->orderLineItems   = $order->orderLineItems;

		if (isset($order->device))
			$this->datas->device       = $order->device;

		if (isset($this->datas->paymentResult))
			$this->datas->paymentResult = $this->datas->paymentResult;

		$this->initObjects();
	}

	/**
	 * Init objects necessary for orders
	 */
	private function initObjects()
	{
		
		$this->customer = PowaTagOrders::getCustomerByEmail($this->datas->customer->emailAddress, true, $this->datas->customer->lastName, $this->datas->customer->firstName, $this->datas->customer->emailAddress);

		$addresses = $this->customer->getAddresses((int)$this->context->language->id);

		$find = false;

		if (!isset($this->datas->customer->shippingAddress->friendlyName))
			$friendlyName = $this->module->l('My address');
		else
			$friendlyName = $this->datas->customer->shippingAddress->friendlyName;

		foreach ($addresses as $addr)
		{
			if ($addr['alias'] == $friendlyName)
			{
				$find = true;
				$address = new Address((int)$addr['id_address']);
				break;
			}
		}

		if (!$find)
			$address = $this->createAddress($this->datas->customer->shippingAddress);

		if (Validate::isLoadedObject($address))
			$this->address = $address;
		else
			return false;
	}	

	public function validateOrder()
	{
		$createCart = $this->createCart();

		$idOrder = $createCart;

		if (isset($this->datas->paymentResult))
		{
			$orderState = (int)Configuration::get('PS_OS_PAYMENT');

			if (!$createCart)
				$orderState = (int)Configuration::get('PS_OS_ERROR');

			$this->datas->customer = $this->datas->customer;

			$payment = new PowaTagPayment($this->datas, $createCart);
			$idOrder = $payment->confirmPayment(true);
		}

		if ($createCart)
		{
			$transaction              = new PowaTagTransaction();
			$transaction->id_cart     = (int)$this->cart->id;
			$transaction->id_order    = (int)$idOrder;
			$transaction->id_customer = (int)$this->customer->id;
			if (isset($this->datas->device))
			{
				$transaction->id_device   = $this->datas->device->deviceID;
				$transaction->ip_address  = $this->datas->device->ipAddress;
			}
			$transaction->order_state = isset($orderState) ? (int)$orderState : 0;

			$transaction->save();
		}

		return $idOrder;
	}

	private function createCart()
	{
		$firstItem = current($this->datas->orderLineItems);
		$firstVariant = current($firstItem->product->productVariants);

		if (!$currency = $this->getCurrencyByIsoCode($firstVariant->finalPrice->currency))
			return false;

		$this->shippingCost = $this->getShippingCost($this->datas->orderLineItems, $currency, (int)$this->address->id_country, false);
		$this->shippingCostWt = $this->getShippingCost($this->datas->orderLineItems, $currency, (int)$this->address->id_country, true);

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
			$this->addError($this->module->l("Impossible to save cart"));

			if (PowaTagAPI::apiLog())
				PowaTagLogs::initAPILog('Create cart', PowaTagLogs::ERROR, $this->error['message']);

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
		$products = $this->datas->orderLineItems;

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
					$this->addError(sprintf($this->module->l('This product does not exists : %s'), $p->product->code), PowaTagAbstract::$SKU_NOT_FOUND);

					if (PowaTagAPI::apiLog())
						PowaTagLogs::initAPILog('Add product to cart', PowaTagLogs::ERROR, "Product : ".$this->error['message']);

					return false;
				}

				$variants = $p->product->productVariants;

				$product_rate = 1 + ($product->getTaxesRate($address) / 100);

				foreach ($variants as $variant)
				{
					$variantCurrency = $this->getCurrencyByIsoCode($variant->finalPrice->currency);

					if (!PowaTagValidate::currencyEnable($variantCurrency))
					{
						$this->addError(sprintf($this->module->l('Currency not found : %s'), $variant->code));

						if (PowaTagAPI::apiLog())
							PowaTagLogs::initAPILog('Add product to cart', PowaTagLogs::ERROR, "Product : ".$this->error['message']);
						return false;
					}

					$variantAmount = $variant->finalPrice->amount;

					$idProductAttribute = false;

					if ($idProductAttribute = $this->getCombinationByEAN13($product->id, $variant->code))
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

						if (PowaTagAPI::apiLog())
							PowaTagLogs::initAPILog('Add product to cart', PowaTagLogs::ERROR, "Product : ".$this->error['message']);

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
						$this->addError(sprintf($this->module->l('Price variant is different with the price shop : %s != %s'), $priceAttribute, $variantAmount));

						if (PowaTagAPI::apiLog())
							PowaTagLogs::initAPILog('Add product to cart', PowaTagLogs::ERROR, "Product : ".$this->error['message']);

						return false;
					}

					if ($qtyInStock == 0)
					{
						$this->addError(sprintf($this->module->l('No Stock Available')), PowaTagAbstract::$NOT_IN_STOCK);

						if (PowaTagAPI::apiLog())
							PowaTagLogs::initAPILog('Add product to cart', PowaTagLogs::ERROR, "Product : ".$this->error['message']);


						return false;
					}

					if ($qtyInStock < $p->quantity)
					{
						$this->addError(sprintf($this->module->l('Quantity > Stock Count')), PowaTagAbstract::$NOT_IN_STOCK);

						if (PowaTagAPI::apiLog())
							PowaTagLogs::initAPILog('Add product to cart', PowaTagLogs::ERROR, "Product : ".$this->error['message']);

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
			$this->addError($this->module->l("No product found in request"), PowaTagAbstract::$BAD_REQUEST);
			return false;
		}

		return true;
	}
}

?>