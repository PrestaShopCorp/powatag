<?php
/**
* 2007-2014 PrestaShop 
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
		$this->checkProductsAreShippable($this->products);
		

		if ($this->error)
			return false;

		$this->shippingCost = (float)$this->getShippingCost($this->products, $this->currency, $country, false);

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