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


class PowaTagOrdersCosts extends PowaTagAbstract
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
	public $address;

	/**
	 * Cart Prestashop
	 * @var \Cart
	 */
	public $cart;

	public function __construct(stdClass $datas)
	{
		parent::__construct($datas);

		if (isset($this->datas->order))
			$order = $this->datas->order;
		else
			$order = current($this->datas->orders);

		$this->datas->customer         = $order->customer;
		$this->datas->customer->shippingAddress->phone = isset($order->customer->phone) ? $order->customer->phone : '000000000';
		if (isset($this->datas->customer->billingAddress))
			$this->datas->customer->billingAddress->phone = isset($order->customer->phone) ? $order->customer->phone : '000000000';
		
		$this->datas->orderLineItems   = $order->orderLineItems;
		if (isset($order->vouchers)) {
			$this->datas->vouchers = $order->vouchers;
		}

		if (isset($order->device))
			$this->datas->device       = $order->device;

		if (isset($this->datas->paymentResult))
			$this->datas->paymentResult = $this->datas->paymentResult;

		if (isset($order->orderCostSummary))
			$this->datas->orderCostSummary = $order->orderCostSummary;

		$this->checkProductsAreShippable($this->datas->orderLineItems);

		$this->initObjects();
	}

	/**
	 * Init objects necessary for orders
	 */
	private function initObjects()
	{
		$invoice_address = false;

		$this->customer = $this->getCustomerByEmail($this->datas->customer->emailAddress, true, $this->datas->customer->lastName, $this->datas->customer->firstName, $this->datas->customer->emailAddress);

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
		else
			$address = $this->createAddress($this->datas->customer->shippingAddress, $address);
		
		if (isset($this->datas->customer->billingAddress))
			$invoice_address = $this->createAddress($this->datas->customer->billingAddress);


		if (Validate::isLoadedObject($address))
		{
			$this->address = $address;
			$this->invoice_address = $address;
		}
		else
		{
			$this->address = false;
			return false;
		}

		if (Validate::isLoadedObject($invoice_address))
			$this->invoice_address = $invoice_address;

	}	

	public function validateOrder()
	{
		$id_cart = $this->createCart();
		$id_order = false;

		if (isset($this->datas->paymentResult) && $id_cart)
		{
			$order_state = (int)Configuration::get('PS_OS_PAYMENT');

			$this->datas->customer = $this->datas->customer;

			$payment = new PowaTagPayment($this->datas, $id_cart);
			$id_order = $payment->confirmPayment(true);
			if ($id_order)
				$message = Configuration::get('POWATAG_SUCCESS_MSG', $this->context->language->id) != '' ? Configuration::get('POWATAG_SUCCESS_MSG', $this->context->language->id) : 'Success';
			else
				$message = 'Error on order creation';
		}

		if ($id_cart)
		{
			$transaction              = new PowaTagTransaction();
			$transaction->id_cart     = (int)$this->cart->id;
			$transaction->id_order    = (int)$id_order;
			$transaction->id_customer = (int)$this->customer->id;
			if (isset($this->datas->device))
			{
				$transaction->id_device   = isset($this->datas->device->deviceID) ? $this->datas->device->deviceID : '';
				$transaction->ip_address  = isset($this->datas->device->ipAddress) ? $this->datas->device->ipAddress : '';
			}
			$transaction->order_state = isset($order_state) ? (int)$order_state : 0;
			$transaction->save();
			$message = Configuration::get('POWATAG_SUCCESS_MSG', $this->context->language->id) != '' ? Configuration::get('POWATAG_SUCCESS_MSG', $this->context->language->id) : 'Success';
			
		} else {
			$message = 'Cart has not been created';
		}

		return array($id_cart, $id_order, $message);
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
		$cart->id_address_invoice  = (int)$this->invoice_address->id;
		$cart->id_currency         = (int)$currency->id;
		$cart->id_customer         = (int)$this->customer->id;
		$cart->secure_key          = $this->customer->secure_key;

		if (!$cart->save())
		{
			$this->addError($this->module->l('Impossible to save cart'), PowaTagErrorType::$INTERNAL_ERROR);

			if (PowaTagAPI::apiLog())
				PowaTagLogs::initAPILog('Create cart', PowaTagLogs::ERROR, $this->error['message']);

			return false;
		}

		if (PowaTagAPI::apiLog())
			PowaTagLogs::initAPILog('Create cart', PowaTagLogs::SUCCESS, 'Cart ID : '.$cart->id);

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

		$country = $this->getCountry($codeCountry);
		
		$address = Address::initialize();
		$address->id_country = $country->id;

		if ($products && count($products))
		{
			foreach ($products as $p)
			{
				if (PowaTagAPI::apiLog())
					PowaTagLogs::initAPILog('Add product to cart', PowaTagLogs::IN_PROGRESS, 'Product : '.$p->product->code);

				$product = PowaTagProductHelper::getProductByCode($p->product->code, $this->context->language->id);

				if (!Validate::isLoadedObject($product))
				{
					$this->addError(sprintf($this->module->l('This product does not exists : %s'), $p->product->code), PowaTagErrorType::$SKU_NOT_FOUND);

					if (PowaTagAPI::apiLog())
						PowaTagLogs::initAPILog('Add product to cart', PowaTagLogs::ERROR, 'Product : '.$this->error['message']);

					return false;
				}

				$variants = $p->product->productVariants;

				$product_rate = 1 + ($product->getTaxesRate($address) / 100);

				foreach ($variants as $variant)
				{

					$variantCurrency = $this->getCurrencyByIsoCode($variant->finalPrice->currency);

					if (!PowaTagValidate::currencyEnable($variantCurrency))
					{
						$this->addError(sprintf($this->module->l('Currency not found : %s'), $variant->code), PowaTagErrorType::$CURRENCY_NOT_SUPPORTED);

						if (PowaTagAPI::apiLog())
							PowaTagLogs::initAPILog('Add product to cart', PowaTagLogs::ERROR, 'Product : '.$this->error['message']);
						return false;
					}

					$variantAmount = $variant->finalPrice->amount;

					$id_product_attribute = false;
					$combination = false;

					if ($id_product_attribute = PowaTagProductAttributeHelper::getCombinationByCode($product->id, $variant->code))
					{
						$combination = new Combination($id_product_attribute);
						$priceAttribute   = $product->getPrice($this->display_taxes, $id_product_attribute);
						$qtyInStock = PowaTagProductQuantityHelper::getProductQuantity($product, $id_product_attribute);
					}
					else if ($product)
					{
						$priceAttribute   = $product->getPrice($this->display_taxes);
						$qtyInStock = PowaTagProductQuantityHelper::getProductQuantity($product);
					}
					else
					{
						$this->addError(sprintf($this->module->l('This variant does not exist : %s'), $variant->code), PowaTagErrorType::$SKU_NOT_FOUND);

						if (PowaTagAPI::apiLog())
							PowaTagLogs::initAPILog('Add product to cart', PowaTagLogs::ERROR, 'Product : '.$this->error['message']);

						return false;
					}


					if ($qtyInStock == 0)
					{
						$this->addError(sprintf($this->module->l('No Stock Available')), PowaTagErrorType::$SKU_OUT_OF_STOCK);

						if (PowaTagAPI::apiLog())
							PowaTagLogs::initAPILog('Add product to cart', PowaTagLogs::ERROR, 'Product : '.$this->error['message']);


						return false;
					}

					if ($qtyInStock < $p->quantity)
					{
						$this->addError(sprintf($this->module->l('Quantity > Stock Count')), PowaTagErrorType::$INSUFFICIENT_STOCK);

						if (PowaTagAPI::apiLog())
							PowaTagLogs::initAPILog('Add product to cart', PowaTagLogs::ERROR, 'Product : '.$this->error['message']);

						return false;
					}
					if ($p->quantity < $product->minimal_quantity || ($combination && $combination->minimal_quantity > $p->quantity))
					{
						$this->addError(sprintf($this->module->l('Quantity < minimal quantity for product')), PowaTagErrorType::$OTHER_STOCK_ERROR);

						if (PowaTagAPI::apiLog())
							PowaTagLogs::initAPILog('Add product to cart', PowaTagLogs::ERROR, 'Product : '.$this->error['message']);

						return false;
					}


					$cart->updateQty($p->quantity, $product->id, $id_product_attribute);

					if (PowaTagAPI::apiLog())
						PowaTagLogs::initAPILog('Add product to cart', PowaTagLogs::SUCCESS, 'Cart ID : '.$cart->id.' - Product ID : '.$product->id);
					
					break;
				}

			}
		}
		else
		{
			$this->addError($this->module->l('No product found in request'), PowaTagErrorType::$SKU_NOT_FOUND);
			return false;
		}


		// add vouchers
		
		if (isset($this->datas->vouchers)) {
			$this->context->cart = $cart;
			$vouchers = $this->datas->vouchers;
			if ($vouchers && count($vouchers)) {
				foreach ($vouchers as $voucher) {
					$ci = CartRule::getIdByCode($voucher);
					if (!$ci) continue;
					$cr = new CartRule($ci);
					if (!$cr) continue;
					if ($error = $cr->checkValidity($this->context, false, true)) continue;
					$this->context->cart->addCartRule($cr->id);
					if (PowaTagAPI::apiLog()) {
						PowaTagLogs::initAPILog('Added voucher', PowaTagLogs::SUCCESS, 'Cart ID : '.$cart->id.' - Voucher : '.$voucher);
					}						
				}
			}
		}
		
		return true;
	}
}

?>