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

class PowaTagProduct extends PowaTagAbstract
{

	/**
	 * Product Model
	 * @var \Product
	 */
	private $product;

	/**
	 * List of combinations
	 * @var array
	 */
	private $combinations;

	public function __construct(stdClass $datas)
	{
		parent::__construct($datas);

		$product = PowaTagProductHelper::getProductByCode($datas->id_product, $this->context->language->id);
		$this->product = $product;
	}

	public function setJSONRequest()
	{
		if (Validate::isLoadedObject($this->product)) {
			$product = $this->getProductWithoutOptions();

			return $product;
		}
		else
		{
			$this->addError($this->module->l("Product not found"), PowaTagAbstract::$SKU_NOT_FOUND);
			return false;
		}
	}

	private function getProductWithoutOptions()
	{
		$hasOptions = $this->hasOptions();

		$array = array(
			'name'                => $this->product->name,
			'type'                => 'PRODUCT',
			'availableCurrencies' => $this->getCurrencies(),
			'code'                => PowaTagProductHelper::getProductSKU($this->product),
			'description'         => preg_replace("#\r\n#isD", " ", strip_tags($this->product->description)),
			'currency'            => $this->context->currency->iso_code,
			'language'            => $this->context->language->iso_code,
			'productImages'       => $this->getImages(),
			'productVariants'     => $this->getVariants(),
		);


		if ($attributes = $this->getAttributes())
			$array['productAttributes'] = $attributes;

		if ($fields = $this->getCustomFields())
			$array['customFields'] = $fields;
		
		if ($hasOptions)
			$array['productOptions'] = $this->getOptions();

		return array($array);
	}

	private function getCurrencies()
	{
		$currencies = array();

		$shopCurrencies = Currency::getCurrencies();

		if ($shopCurrencies && count($shopCurrencies))
		{
			foreach ($shopCurrencies as $currency)
				$currencies[] = $currency['iso_code'];
		}

		return $currencies;
	}

	private function getImages()
	{
		$images = $this->product->getImages((int)$this->context->language->id);

		$link = $this->context->link;

		$jsonImages = array();

		if ($images && count($images))
		{
			foreach ($images as $image)
				$jsonImages[] = array('name' => $image['legend'], 'url' => $link->getImageLink($this->product->link_rewrite, $this->product->id.'-'.$image['id_image']));
		}

		return $jsonImages;
	}

	private function getAttributes()
	{
		$productAttributes = array();

		$attributes = $this->product->getFeatures();
		$id_lang = (int)$this->context->language->id;

		if ($attributes && count($attributes))
		{
			foreach ($attributes as $attribute)
			{
				$feature = new Feature($attribute['id_feature'], $id_lang);
				$featureValue = new FeatureValue($attribute['id_feature_value'], $id_lang);
				$productAttributes[$feature->name] = $featureValue->value;
			}
		}

		return $productAttributes;
	}

	private function hasOptions()
	{
		$this->combinations = $this->product->getAttributeCombinations($this->context->language->id);
		return (bool) $this->combinations;
	}

	private function getOptions()
	{
		$combinations = array();
		if ($this->combinations && count($this->combinations))
		{
			foreach ($this->combinations as $combination)
			{
				if (!array_key_exists($combination['group_name'], $combinations)) {
					$combinations[$combination['group_name']] = array(
						'id' => $combination['id_attribute_group'],
						'values' => array()
					);
				}

				if (!in_array($combination['attribute_name'], $combinations[$combination['group_name']]['values']))
					$combinations[$combination['group_name']]['values'][] = $combination['attribute_name'];
			}
		}

		return $combinations;
	}

	private function getVariants()
	{
		$groups = array();

		if ($this->combinations && count($this->combinations))
		{

			foreach ($this->combinations as $combination)
			{
				if (!array_key_exists($combination['id_product_attribute'], $groups))
				{
					$groups[$combination['id_product_attribute']] = array(

						'code'          =>  PowatagProductAttributeHelper::getVariantCode($combination),
						'numberInStock' => PowaTagProductQuantityHelper::getCombinationQuantity($combination),
						'originalPrice' => array(
							'amount'   => $this->formatNumber($this->product->getPrice(true, null), 2),
							'currency' => $this->context->currency->iso_code
						),
						'finalPrice'    => array(
							'amount'   =>  $this->formatNumber($this->product->getPrice(true, $combination['id_product_attribute']), 2),
							'currency' => $this->context->currency->iso_code
						)
					);
				}

				$groups[$combination['id_product_attribute']]['options'][$combination['group_name']] = $combination['attribute_name'];
			}

			sort($groups);
		}
		else
		{
			$variant = array(
				'code'          => PowaTagProductHelper::getProductSKU($this->product),
				'numberInStock' => PowaTagProductQuantityHelper::getProductQuantity($this->product),
				'finalPrice'    => array(
					'amount'   =>  $this->formatNumber($this->product->getPrice(true, null), 2),
					'currency' => $this->context->currency->iso_code
				)
			);

			$groups = array($variant);
		}

		return $groups;
	}

	private function getCustomFields()
	{
		return array();
	}

}

?>