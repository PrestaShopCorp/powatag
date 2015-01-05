<?php
/**
* 2007-2015 PrestaShop 
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

	public function setBantAuthorizationCode()
	{
		$this->bankAuthorizationCode = isset($this->datas->paymentResult->bankAuthorizationCode) ? $this->datas->paymentResult->bankAuthorizationCode : '';
	}
	
	public function validateOrder($orderState, $id_cart, $amountPaid, $message = null)
	{
		if (PowaTagAPI::apiLog())
			PowaTagLogs::initAPILog('Create order', PowaTagLogs::IN_PROGRESS, 'Cart ID : '.$id_cart);

		$module = Module::getInstanceByName('powatag');

		$cart = new Cart($id_cart);
		$customer = new Customer($cart->id_customer);

		if ($module->validateOrder((int)$id_cart, (int)$orderState, $amountPaid, $module->name, $message.$this->error['message'], array('transaction_id' => $this->bankAuthorizationCode), null, false, $customer->secure_key))
		{
			if (PowaTagAPI::apiLog())
				PowaTagLogs::initAPILog('Create order', PowaTagLogs::SUCCESS, 'Order ID : '.$module->currentOrder);

			return $module->currentOrder;
		}
		else
		{
			if (PowaTagAPI::apiLog())
				PowaTagLogs::initAPILog('Create order', PowaTagLogs::ERROR, 'FAIL');

			return false;
		}
	}

	public function confirmPayment($twoSteps = false)
	{
		$orderState = Configuration::get('PS_OS_PAYMENT');

		if (!$this->cartEnabled())
			$orderState = Configuration::get('PS_OS_ERROR');

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

		if (!$twoSteps)
		{
			if (!$idTransaction = $this->transactionExists())
				$orderState = Configuration::get('PS_OS_ERROR');
		}

		$amountPaid = $this->datas->paymentResult->amountTotal->amount;

		if (!$this->error)
		{
			if (!$this->checkTotalToPaid($amountPaid, $this->datas->paymentResult->amountTotal->currency))
				$orderState = (int)Configuration::get('PS_OS_ERROR');
		}

		if (!$this->bankAuthorizationCode)
			$this->setBantAuthorizationCode();
		
		if (!$twoSteps)
		{
			$transaction = new PowaTagTransaction((int)$idTransaction);
			$transaction->orderState = $orderState;
		}

		$currentOrderId = $this->validateOrder($orderState, $this->idCart, $amountPaid);

		if (!$twoSteps)
		{
			$transaction->id_order = $currentOrderId;
			$transaction->save();
		}

		return $currentOrderId;
	}

	private function cartEnabled()
	{
		if (!Validate::isLoadedObject($this->cart))
		{
			$this->addError(sprintf($this->module->l('Cart not exists : %s'), $this->idCart), PowaTagAbstract::$INVALID_PAYMENT);
			return false;
		}

		if ($this->cart->orderExists())
		{
			$this->addError(sprintf($this->module->l('Cart has already associated with order : %s'), $this->idCart), PowaTagAbstract::$INVALID_PAYMENT);
			return false;
		}

		return true;
	}

	private function compareCustomer()
	{
		$customerDatas = $this->getCustomerByEmail($this->datas->customer->emailAddress);

		if (!Validate::isLoadedObject($customerDatas))
		{
			$this->addError(sprintf($this->module->l('The customer does not exists : %s'), $this->datas->customer->emailAddress), PowaTagAbstract::$INVALID_PAYMENT);
			return false;
		}

		$cartCustomer = new Customer((int)$this->cart->id_customer);

		if ($customerDatas->id != $cartCustomer->id)
		{
			$this->addError(sprintf($this->module->l('The information sent in the request are not identical to the one saved : %s != %s'), $customerDatas->id, $cartCustomer->id), PowaTagAbstract::$INVALID_PAYMENT);
			return false;
		}

		return true;
	}

	private function transactionExists()
	{
		$transactions = PowaTagTransaction::getTransactions((int)$this->idCart);
		
		if (!$transactions || !count($transactions))
		{
			$this->addError(sprintf($this->module->l('No transaction found for, Cart ID : %s, Device ID : %s & IP : %s'), $this->idCart, $this->datas->device->deviceID, $this->datas->device->ipAddress), PowaTagAbstract::$INVALID_PAYMENT);
			return false;
		}

		if (count($transactions) > 1)
		{
			$this->addError(sprintf($this->module->l('Too many transaction for, Cart ID : %s, Device ID : %s & IP : %s'), $this->idCart, $this->datas->device->deviceID, $this->datas->device->ipAddress), PowaTagAbstract::$INVALID_PAYMENT);
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
			{
				$currencyCode = $currency;
				if (!$currency = PowaTagPayment::getCurrencyByIsoCode($currency))
					$currency = $currencyCode;
			}

		}


		if (!PowaTagValidate::currencyEnable($currency))
		{
			$this->addError(sprintf($this->module->l('Currency is not enable : %s'), (isset($currency->iso_code) ? $currency->iso_code : $currency)), PowaTagAbstract::$INVALID_PAYMENT);
			return false;
		}

		//We change context currency to be sure that calculs are made with correct currency
		$context = Context::getContext();
		$context->currency = $currency;
		$context->country = $this->getCountry($this->datas->customer->shippingAddress->country->alpha2Code);
		
		if ($this->cart->getOrderTotal(true, Cart::BOTH, null, Configuration::get('POWATAG_SHIPPING')) != $amountPaid)
		{
			$this->addError($this->module->l('Amount paid is not same as the cart'), PowaTagAbstract::$INVALID_PAYMENT);
			return false;
		}

		return true;
	}

}

?>