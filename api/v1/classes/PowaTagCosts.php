<?php
/**
* 2007-2015 PrestaShop.
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
*
*  @version  Release: $Revision: 7776 $
*
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

require_once 'PowaTagOrdersCosts.php';

class PowaTagCosts extends PowaTagOrdersCosts
{
    /**
     * Currency.
     *
     * @var Currency
     */
    private $currency;

    /**
     * Product list.
     *
     * @var array
     */
    private $products;

    public function __construct(stdClass $datas)
    {
        parent::__construct($datas);
        $this->products = $this->datas->order->orderLineItems;
    }

    /**
     * Get currency of request.
     */
    private function getCurrency()
    {
        $this->currency = $this->getCurrencyByIsoCode($this->context->currency->iso_code);
    }

    /**
     * Get datas for return of request.
     *
     * @return array Datas
     */
    public function getSummary()
    {
        $this->getCurrency();
        list($id_cart, $id_order, $message) = $this->validateOrder();
        if (!$id_cart) {
            return false;
        }
        $id_order = $id_order . $message; // this code prevents validator report unused variable

        $det = $this->cart->getSummaryDetails();

        $detail = array();
        $total_discount = 0;
        foreach ($det['products'] as &$product) {
            $product['price_without_quantity_discount'] = Product::getPriceStatic(
                $product['id_product'],
                false,
                $product['id_product_attribute'],
                6,
                null,
                false,
                false
            );

            // product price without tax

            $discount = $product['price_without_quantity_discount'] - $product['price'];
            $total_discount += $discount * $product['quantity'];

            $detail[] = array(
                'code' => $product['id_product'],
                'unitPrice' => array(
                    'amount' => $this->formatNumber($product['price_without_quantity_discount'], 2),
                    'currency' => $this->currency->iso_code,
                ),
                'unitDiscount' => array(
                    'amount' => $this->formatNumber($discount, 2),
                    'currency' => $this->currency->iso_code,
                ),
                'unitTax' => array(
                    'amount' => $this->formatNumber($product['price_wt'] - $product['price'], 2),
                    'currency' => $this->currency->iso_code,
                ),
                'quantity' => $product['quantity'],
                'total' => array(
                    'amount' => $this->formatNumber($product['total_wt'], 2),
                    'currency' => $this->currency->iso_code,
                ),
            );
        }

        $shipping_tax = $det['total_shipping'] - $det['total_shipping_tax_exc'];

        $response = array(
            'orderCostSummary' => array(
                'subTotal' => array(
                    'amount' => $this->formatNumber($det['total_products'], 2),
                    'currency' => $this->currency->iso_code,
                ),
                'discount' => array(
                    'amount' => $this->formatNumber($total_discount + $det['total_discounts'], 2),
                    'currency' => $this->currency->iso_code,
                ),
                'shippingCost' => array(
                    'amount' => $this->formatNumber($det['total_shipping_tax_exc'], 2),
                    'currency' => $this->currency->iso_code,
                ),
                'shippingDiscount' => array(
                    'amount' => $this->formatNumber(0, 2),
                    'currency' => $this->currency->iso_code,
                ),
                'shippingTax' => array(
                    'amount' => $this->formatNumber($shipping_tax, 2),
                    'currency' => $this->currency->iso_code,
                ),
                'tax' => array(
                    'amount' => $this->formatNumber($det['total_tax'], 2),
                    'currency' => $this->currency->iso_code,
                ),
                'total' => array(
                    'amount' => $this->formatNumber($det['total_price'], 2),
                    'currency' => $this->currency->iso_code,
                ),
            ),
            'orderCostDetails' => $detail,
        );

        return $response;
    }
}
