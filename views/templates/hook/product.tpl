{*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<!-- PowaTag -->
<div class="powatag"
	data-endpoint="{$powatagGeneratorURL|escape:'htmlall':'UTF-8'}"
	data-key="{$powatagApi|escape:'htmlall':'UTF-8'}"
	data-sku="{$productSku|escape:'htmlall':'UTF-8'}"
{if isset($powatagRedirect) }
	data-redirect="{$powatagRedirect|escape:'htmlall':'UTF-8'}"
{/if}{if isset($powatagOffer) }
	data-offer="{$powatagOffer|escape:'htmlall':'UTF-8'}"
{/if}{if isset($powatagLang) }
	data-lang="{$powatagLang|escape:'htmlall':'UTF-8'}"
{/if}{if isset($powatagType) }
	data-type="{$powatagType|escape:'htmlall':'UTF-8'}"
{/if}{if isset($powatagStyle) }
	data-style="{$powatagStyle|escape:'htmlall':'UTF-8'}"
{/if}{if isset($powatagColorscheme) }
	data-colorscheme="{$powatagColorscheme|escape:'htmlall':'UTF-8'}"
{/if}{if isset($powatagDisplay) }
	data-display="{$powatagDisplay|escape:'htmlall':'UTF-8'}"
{/if}
	data-video="{$powatagVideo|escape:'htmlall':'UTF-8'}"
	data-debug="{$powatagDebug|escape:'htmlall':'UTF-8'}"
></div>
<!-- /PowaTag -->
