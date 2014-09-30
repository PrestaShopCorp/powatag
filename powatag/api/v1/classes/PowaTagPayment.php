<?php


class PowaTagPayment extends PowaTagAbstract
{

	/**
	 * Transaction ID
	 * @var string
	 */
	private $bankAuthorizationCode;

	/**
	 * Prestashop Cart
	 * @var Cart
	 */
	private $cart;

	/**
	 * Cart ID
	 * @var int
	 */
	private $idCart;

	public function __construct(stdClass $datas, $idCart)
	{
		parent::__construct($datas);
		$this->idCart = (int)$idCart;
		$this->cart = new Cart((int)$idCart);
	}

	public function setBantAuthorizationCode($bankAuthorizationCode)
	{
		$this->bankAuthorizationCode = $bankAuthorizationCode;
	}
	
	public function validateOrder($orderState, $id_cart, $amountPaid, $message = null)
	{

		if (PowaTagAPI::apiLog())
			PowaTagLogs::initAPILog('Create order', PowaTagLogs::IN_PROGRESS, "Cart ID : ".$id_cart);

		$module = Module::getInstanceByName('powatag');

		$cart = new Cart($id_cart);
		$customer = new Customer($cart->id_customer);

		if ($module->validateOrder((int)$id_cart, (int)$orderState, $amountPaid, $module->name, $message.$this->error, array('transaction_id' => $this->bankAuthorizationCode), null, false, $customer->secure_key))
		{

			if (PowaTagAPI::apiLog())
				PowaTagLogs::initAPILog('Create order', PowaTagLogs::SUCCESS, "Order ID : ".$module->currentOrder);

			return $module->currentOrder;
		}
		else
		{

			if (PowaTagAPI::apiLog())
				PowaTagLogs::initAPILog('Create order', PowaTagLogs::ERROR, "FAIL");

			return false;
		}
	}

	public function confirmPayment()
	{

		$orderState = Configuration::get('PS_OS_PAYMENT');

		if (!$this->cartEnabled())
		{
			$orderState = Configuration::get('PS_OS_ERROR');
		}

		$this->customer = PowaTagPayment::getCustomerByEmail($this->datas->customer->emailAddress);;
		$addresses = $this->customer->getAddresses((int)$this->context->language->id);

		$address = false;

		foreach ($addresses as $addr)
		{
			if ($addr['alias'] == $this->datas->paymentRequest->paymentCard->billingAddress->friendlyName)
			{
				$address = new Address((int)$addr['id_address']);
				break;
			}
		}

		if (!$this->error)
		{
			if (!Validate::isLoadedObject($address) && (!$address = !$this->createAddress($this->datas->paymentRequest->paymentCard->billingAddress)))
				$orderState = Configuration::get('PS_OS_ERROR');
		}

		if (Validate::isLoadedObject($address))
		{
			$this->cart->id_address_invoice = $address->id;
			$this->cart->save();
		}

		if (!$this->error)
		{
			if (!$this->compareCustomer())
				$orderState = Configuration::get('PS_OS_ERROR');
		}

		if (!$this->error)
		{
			if (!$this->ifCarrierDeliveryZone(Configuration::get('POWATAG_SHIPPING'), false, $this->datas->customer->shippingAddress->country->alpha2Code))
				$orderState = Configuration::get('PS_OS_ERROR');
		}

		if (!$idTransaction = $this->transactionExists())
		{
			$orderState = Configuration::get('PS_OS_ERROR');
		}

		$amountPaid = $this->datas->paymentRequest->amountTotal->amount;

		if (!$this->error)
		{
			if (!$this->checkTotalToPaid($amountPaid, $this->datas->paymentRequest->amountTotal->currency))
				$orderState = (int)Configuration::get('PS_OS_ERROR');
		}

		$this->setBantAuthorizationCode($this->datas->paymentRequest->bankAuthorizationCode);
		
		$transaction = new PowaTagTransaction((int)$idTransaction);
		$transaction->orderState = $orderState;

		$currentOrderId = $this->validateOrder($orderState, $this->idCart, $amountPaid);
		$transaction->id_order = $currentOrderId;

		$transaction->save();

		return $currentOrderId;
	}

	private function cartEnabled()
	{

		if (!Validate::isLoadedObject($this->cart))
		{
			$msg = 'Cart not exists : '.$this->idCart;
			$this->error = $msg;

			return false;
		}

		if ($this->cart->orderExists())
		{
			$msg = 'Cart has already associated with order : '.$this->idCart;

			$this->error = $msg;
			return false;
		}

		return true;
	}

	private function compareCustomer()
	{
		$customerDatas = PowaTagPayment::getCustomerByEmail($this->datas->customer->emailAddress);

		if (!Validate::isLoadedObject($customerDatas))
		{
			$this->error = "The customer does not exists : ".$this->datas->customer->emailAddress;
			return false;
		}

		$cartCustomer = new Customer((int)$this->cart->id_customer);

		if ($customerDatas->id != $cartCustomer->id)
		{
			$this->error = "The information sent in the request are not identical to the one saved : $customerDatas->id != $cartCustomer->id";
			return false;
		}

		return true;
	}

	private function transactionExists()
	{
		$transactions = PowaTagTransaction::getTransactions((int)$this->idCart, $this->datas->device->deviceID, $this->datas->device->ipAddress);

		if (!$transactions || !count($transactions))
		{
			$this->error = "No transaction found for, Cart ID : ".$this->idCart.", Device ID : ".$this->datas->device->deviceID." & IP : ".$this->datas->device->ipAddress;
			return false;
		}

		if (count($transactions) > 1)
		{
			$this->error = "Too many transactions for, Cart ID : ".$this->idCart.", Device ID : ".$this->datas->device->deviceID." & IP : ".$this->datas->device->ipAddress;
			return false;
		}

		$transaction = current($transactions);

		return (int)$transaction['id_powatag_transaction'];
	}

	private function checkTotalToPaid($amountPaid, $currency)
	{

		if (!$currency instanceof Currency)
		{
			if (Validate::isInt($currency))
				$currency = new Currency((int)$currency);
			else
				$currency = PowaTagPayment::getCurrencyByIsoCode($currency);
		}

		if (!PowaTagValidate::currencyEnable($currency))
		{
			$this->error = "Currency is not enable : ".(isset($currency->iso_code) ? $currency->iso_code : $currency);
			return false;
		}

		if ($this->cart->getOrderTotal(true, Cart::BOTH, null, Configuration::get('POWATAG_SHIPPING')) != $amountPaid)
		{
			$this->error = "Amount paid is not same as the cart";
			return false;
		}

		return true;
	}

}

?>