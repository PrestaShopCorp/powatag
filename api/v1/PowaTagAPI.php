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

require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'PowaTagAPIAbstract.php';

class PowaTagAPI extends PowaTagAPIAbstract
{
	public function __construct($request, $origin)
	{
		$this->loadClasses();

		parent::__construct($request);

		// Abstracted out for example
		$APIKey = new PowaTagAPIKey();

		if (!Module::isInstalled('powatag') || !Module::isEnabled('powatag'))
			throw new Exception('Module not enable');
/*
		if (!array_key_exists('HTTP_HMAC', $_SERVER))
			throw new Exception('No API Key provided');
		else if (!$APIKey->verifyKey($_SERVER['HTTP_HMAC'], $this->data))
			throw new Exception('Invalid API Key');
*/		
		$this->data = Tools::jsonDecode($this->data);
	}

	/**
	 * Load classes
	 */
	private function loadClasses()
	{
		$path = dirname(__FILE__).DIRECTORY_SEPARATOR;

		foreach (scandir($path) as $file)
			if (is_file($path.$file) && preg_match('#.php$#isD', $file) && $file != 'index.php')
				require_once $path.$file;

		$path .= 'classes'.DIRECTORY_SEPARATOR;

		foreach (scandir($path) as $file)
			if (is_file($path.$file) && preg_match('#.php$#isD', $file) && $file != 'index.php')
				require_once $path.$file;
	}

	/**
	 * Products endpoint
	 */
	protected function products($args)
	{
		if ($args || $this->verb)
		{
			if ($args)
				$idProduct = current($args);
			else if ($this->verb)
				$idProduct = $this->verb;

			if (PowaTagAPI::apiLog())
				PowaTagLogs::initAPILog('Process get products', PowaTagLogs::IN_PROGRESS, 'Id product : '.$idProduct);

			if (PowaTagAPI::requestLog())
				PowaTagLogs::initRequestLog('Process get products', PowaTagLogs::IN_PROGRESS, $args);

			$stdClass = new stdClass();
			$stdClass->id_product = $idProduct;

			$powatagProduct = new PowaTagProduct($stdClass);

			//Handle to get one specific products
			if ($value = $powatagProduct->setJSONRequest())
			{
				if (PowaTagAPI::apiLog())
					PowaTagLogs::initAPILog('Process get products', PowaTagLogs::SUCCESS, 'Id product : '.$idProduct);

				if (PowaTagAPI::requestLog())
					PowaTagLogs::initRequestLog('Process get products', PowaTagLogs::SUCCESS, $value);

				return array("products" => array($value));
			}
			else
			{
				$error = $powatagProduct->getError();

				if (PowaTagAPI::apiLog())
					PowaTagLogs::initAPILog('Process get products', PowaTagLogs::ERROR, $error['message']);
				return PowaTagAPI::powaError($error);
			}
		}
		else 
		{
			$msg = 'No product mentionned';

			if (PowaTagAPI::apiLog())
				PowaTagLogs::initAPILog('Process get products', PowaTagLogs::ERROR, $msg);

			return $msg;
		}
	}

	/**
	 * Orders endpoint
	 */
	protected function orders($args)
	{
		// Manage informations
		$datas = $this->data;

		if (is_null($datas))
		{
			$error = PowaTagErrorType::$BAD_REQUEST;
			return PowaTagAPI::powaError($error);
		}
		
		if ($this->verb == 'costs')
		{
			if (isset($datas->order))
				$customer = $datas->order->customer;
			else
				$customer = current($datas->orders)->customer;

			if (PowaTagAPI::apiLog())
				PowaTagLogs::initAPILog('Process calculate Costs', PowaTagLogs::IN_PROGRESS, 'Customer : '.$customer->firstName.' '.$customer->lastName);

			if (PowaTagAPI::requestLog())
				PowaTagLogs::initRequestLog('Process calculate Costs', PowaTagLogs::IN_PROGRESS, $datas);

			$powatagcosts = new PowaTagCosts($datas);
			if ($error = $powatagcosts->getError())
			{
				$message = $error['message'];

				if (PowaTagAPI::apiLog())
					PowaTagLogs::initAPILog('Process order', PowaTagLogs::ERROR, $message);

				return PowaTagAPI::powaError($error);
			}
			
			if ($value = $powatagcosts->getSummary())
			{
				if (PowaTagAPI::apiLog())
					PowaTagLogs::initAPILog('Process calculate Costs', PowaTagLogs::SUCCESS, 'Customer : '.$customer->firstName.' '.$customer->lastName);

				if (PowaTagAPI::requestLog())
					PowaTagLogs::initRequestLog('Process calculate Costs', PowaTagLogs::SUCCESS, $value);

				return $value;
			}
			else
			{
				$error = $powatagcosts->getError();

				if (PowaTagAPI::apiLog())
					PowaTagLogs::initAPILog('Process calculate Costs', PowaTagLogs::ERROR, $error['message']);

				return PowaTagAPI::powaError($error);
			}
		}
		else if (count($args) == 2 && Validate::isInt($args[0]) && $args[1] = 'confirm-payment')
		{//Three step payment confirmation
			if (PowaTagAPI::apiLog())
				PowaTagLogs::initAPILog('Process payment', PowaTagLogs::IN_PROGRESS, 'Order ID : '.$args[0]);

			if (PowaTagAPI::requestLog())
				PowaTagLogs::initRequestLog('Create payment', PowaTagLogs::IN_PROGRESS, $datas);
			$payment = new PowaTagPayment($datas, (int)$args[0]);
			
			if ($id_order = $payment->confirmPayment())
			{
				if (PowaTagAPI::apiLog())
					PowaTagLogs::initAPILog('Process payment', PowaTagLogs::SUCCESS, 'ID Order : '.$id_order);

				if (PowaTagAPI::requestLog())
					PowaTagLogs::initRequestLog('Process payment', PowaTagLogs::SUCCESS, $id_order);

				$data = array(
					'providerTxCode' => isset($datas->paymentResult->providerTxCode) ? $datas->paymentResult->providerTxCode : 'providerTxCode Empty',
					'message' => 'Authorization success order '.$id_order.' created',
				);

				if ($payment->checkOrderState($id_order, $data) == 'error')
					$this->setResponse($data['response']);

				return $data;

			}
			else
			{
				$error = $payment->getError();

				if (PowaTagAPI::apiLog())
					PowaTagLogs::initAPILog('Process payment', PowaTagLogs::ERROR, $error['message']);

				return PowaTagAPI::powaError($error);
			}

		}
		else if (!count($args))
		{//Two step payment or three step payment
			if (isset($datas->order))
				$customer = $datas->order->customer;
			else
				$customer = current($datas->orders)->customer;

			if (PowaTagAPI::apiLog())
				PowaTagLogs::initAPILog('Process order 123', PowaTagLogs::IN_PROGRESS, 'Customer : '.$customer->firstName.' '.$customer->lastName);

			if (PowaTagAPI::requestLog())
				PowaTagLogs::initRequestLog('Create order', PowaTagLogs::IN_PROGRESS, $datas);

			$order = new PowaTagOrders($datas);

			if ($error = $order->getError())
			{
				$message = $error['message'];

				if (PowaTagAPI::apiLog())
					PowaTagLogs::initAPILog('Process order', PowaTagLogs::ERROR, $message);

				return PowaTagAPI::powaError($error);
			}

			list($id_cart, $id_order, $message) = $order->validateOrder();
			if ($id_order || $id_cart)
			{
				if (PowaTagAPI::apiLog())
					PowaTagLogs::initAPILog('Process order', PowaTagLogs::SUCCESS, 'Order has been created : '.$id_order);

				$link = new Link();
				
				$cart = new Cart($id_cart);
				$data = array(
					'orderResults' => array(
						array(
							'orderId' => $id_order ? $id_order : $id_cart, 
							'message' => $message, 
							'redirectUrl' => $link->getModuleLink('powatag', 'confirmation', array('id_cart' => (int)$id_cart, 'id_customer' => (int)$cart->id_customer))
						)
					)
				);

				if ($error = $order->getError()) {
					return PowaTagAPI::powaError($error);
				}

				if ($order->checkOrderState($id_order, $data))
					$this->setResponse($data['response']);
				
				return $data;
			}
			else
			{	
				$message = '';
				
				$errorCode = '';
				if ($error = $order->getError())
				{
					$message = $error['message'];
				}

				if (PowaTagAPI::apiLog())
					PowaTagLogs::initAPILog('Process order', PowaTagLogs::ERROR, $message);



				return PowaTagAPI::powaError($error);
			}
		}
	}

	/**
	 * getproducts endpoint - multiple SKU getProduct
	 */
	protected function getproducts($args)
	{
		$sku = $_GET["sku"];
	
		if (PowaTagAPI::requestLog())
			PowaTagLogs::initRequestLog('Process get multiple products', PowaTagLogs::SUCCESS, $sku);
		if ($sku == '') {
			return PowaTagAPI::powaError(array(
				'error' => PowaTagErrorType::$SKU_NOT_FOUND,
				'message' =>"Missing SKU value"
			));
		}
		
		$asku = explode(",",$sku);
		$reply = array();
		foreach ($asku as $idProduct) {

			$stdClass = new stdClass();
			$stdClass->id_product = $idProduct;

			$powatagProduct = new PowaTagProduct($stdClass);
			$detail = $powatagProduct->setJSONRequest();
			if ($detail === false) {
				$detail = array(
					"code" => $idProduct,
					"availability" => "false"
				);
			} else {
				$detail["availability"] = "true";
			}
			$reply[] = $detail;
		}
		return array("products" => $reply);
	}

}

?>
