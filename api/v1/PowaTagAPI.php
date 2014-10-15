<?php

require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'PowaTagAPIAbstract.php';

class PowaTagAPI extends PowaTagAPIAbstract
{

	public function __construct($request, $origin)
	{

		$this->loadClasses();

		parent::__construct($request);

		// Abstracted out for example
		$APIKey = new PowaTagAPIKey();

		if (!Module::isInstalled('powatag')|| !Module::isEnabled('powatag'))
			throw new Exception('Module not enable');

		if (!array_key_exists('HTTP_HMAC', $_SERVER))
			throw new Exception('No API Key provided');
		else if (!$APIKey->verifyKey($_SERVER['HTTP_HMAC'], $this->data))
			throw new Exception('Invalid API Key');
		
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

		if($args)
		{

			$idProduct = current($args);

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
					PowaTagLogs::initAPILog('Process get products', PowaTagLogs::SUCCESS, $value);

				return $value;
			}
			else
			{
				$error = $powatagProduct->getError();

				if (PowaTagAPI::apiLog())
					PowaTagLogs::initAPILog('Process get products', PowaTagLogs::ERROR, $error['message']);

				$this->setResponse($error['error']['response']);

				$array = array(
					'code'             => $error['error']['code'],
					'validationErrors' => null,
					'message'          => $error['message']
				);

				return $array;
			}
		}
		else 
		{

			$msg = "No product mentionned";

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
			$error = PowaTagAbstract::$BAD_REQUEST;

			$this->setResponse($error['response']);

			$data = array(
				"code"              => $error['code'],
				"validationErrors" => "",
				"message"           => "No body of request",
			);

			return $data;
		}
		
		if($this->verb == 'costs')
		{
			if (isset($datas->order))
				$customer = $datas->order->customer;
			else
				$customer = current($datas->orders)->customer;

			if (PowaTagAPI::apiLog())
				PowaTagLogs::initAPILog('Process calculate Costs', PowaTagLogs::IN_PROGRESS, 'Customer : '.$customer->firstName . ' ' . $customer->lastName);

			if (PowaTagAPI::requestLog())
				PowaTagLogs::initRequestLog('Process calculate Costs', PowaTagLogs::IN_PROGRESS, $datas);

			$powatagcosts = new PowaTagCosts($datas);

			if ($value = $powatagcosts->getSummary())
			{

				if (PowaTagAPI::apiLog())
					PowaTagLogs::initAPILog('Process calculate Costs', PowaTagLogs::SUCCESS, 'Customer : '.$customer->firstName . ' ' . $customer->lastName);

				if (PowaTagAPI::requestLog())
					PowaTagLogs::initAPILog('Process calculate Costs', PowaTagLogs::SUCCESS, $value);

				return $value;
			}
			else
			{

				$error = $powatagcosts->getError();

				if (PowaTagAPI::apiLog())
					PowaTagLogs::initAPILog('Process calculate Costs', PowaTagLogs::ERROR, $error['message']);

				$this->setResponse($error['error']['response']);

				$array = array(
					'code'             => $error['error']['code'],
					'validationErrors' => null,
					'message'          => $error['message']
				);

				return $array;
			}
		}
		else if (count($args) == 2 && Validate::isInt($args[0]) && $args[1] = "confirm-payment")
		{

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
					PowaTagLogs::initAPILog('Process payment', PowaTagLogs::SUCCESS, $id_order);

				return $id_order;

			}
			else
			{

				$error = $payment->getError();

				if (PowaTagAPI::apiLog())
					PowaTagLogs::initAPILog('Process payment', PowaTagLogs::ERROR, $error['message']);

				$this->setResponse($error['error']['response']);

				$array = array(
					'code'             => $error['error']['code'],
					'validationErrors' => null,
					'message'          => $error['message']
				);

				return $array;
			}

		}
		else if (!count($args))
		{
			if (isset($datas->order))
				$customer = $datas->order->customer;
			else
				$customer = current($datas->orders)->customer;

			if (PowaTagAPI::apiLog())
				PowaTagLogs::initAPILog('Process order', PowaTagLogs::IN_PROGRESS, 'Customer : '.$customer->firstName . ' ' . $customer->lastName);

			if (PowaTagAPI::requestLog())
				PowaTagLogs::initRequestLog('Create order', PowaTagLogs::IN_PROGRESS, $datas);

			$order = new PowaTagOrders($datas);

			$idOrder = $order->validateOrder();

			if ($idOrder)
			{

				if (PowaTagAPI::apiLog())
					PowaTagLogs::initAPILog('Process order', PowaTagLogs::SUCCESS, 'Order has been created : '.$idOrder);

				return array("orderResults" => array("orderId" => $idOrder, "message" => $order->getError(), "redirectUrl" => ""));
			}
			else
			{

				$error = $order->getError();

				if (PowaTagAPI::apiLog())
					PowaTagLogs::initAPILog('Process order', PowaTagLogs::ERROR, $error['message']);

				$this->setResponse($error['error']['response']);

				$array = array(
					'code'             => $error['error']['code'],
					'validationErrors' => null,
					'message'          => $error['message']
				);

				return $array;
			}
		}
	}

}

?>