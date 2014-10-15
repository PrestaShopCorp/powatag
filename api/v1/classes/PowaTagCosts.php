<?php

class PowaTagCosts extends PowaTagAbstract
{

	/**
	 * Currency
	 * @var Currency
	 */
	private $currency;

	/**
	 * Product list
	 * @var array
	 */
	private $products;

	public function __construct(stdClass $datas)
	{
		parent::__construct($datas);
		$this->products = $this->datas->order->orderLineItems;
	}

	/**
	 * Get currency of request
	 */
	private function getCurrency()
	{
		$variants = current(current($this->products)->product->productVariants);
		
		$this->currency = $this->getCurrencyByIsoCode($variants->finalPrice->currency);
	}

	/**
	 * Get datas for return of request
	 * @return array Datas
	 */
	public function getSummary()
	{

		$this->getCurrency();

		if (!$this->currency)
			return false;

		$country = PowaTagCosts::getCountryByCode($this->datas->order->customer->shippingAddress->country->alpha2Code);

		$this->getSubTotal($this->products, (int)$country->id, false);

		if ($this->error)
			return false;

		$this->shippingCost = (float)$this->getShippingCost($this->products, $this->currency, $country);

		if ($this->error)
			return false;

		$tax = (float)$this->getTax($this->products, $this->currency, $country);

		if ($this->error)
			return false;

		$this->convertToCurrency($this->subTotal, $this->currency, true);
		$this->convertToCurrency($this->shippingCost, $this->currency, true);
		$this->convertToCurrency($tax, $this->currency, true);

		$datas = array(
			"orderCostSummary" => array(
				"subTotal" => array(
					"amount"   => $this->formatNumber($this->subTotal, 2),
					"currency" => $this->currency->iso_code
				),
				"shippingCost" => array(
					"amount"   => $this->formatNumber($this->shippingCost, 2),
					"currency" => $this->currency->iso_code
				),
				"tax" => array(
					"amount"   => $this->formatNumber($tax, 2),
					"currency" => $this->currency->iso_code
				),
				"total" => array(
					"amount"   => $this->formatNumber($this->subTotal + $this->shippingCost + $tax, 2),
					"currency" => $this->currency->iso_code
				)
			)
		);

		return $datas;
	}


}

?>