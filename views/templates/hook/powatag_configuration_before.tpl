{*
* 2007-2014 PrestaShop
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
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}



<div id="powatag_marketing">
{if $marketing}
<div class="powatag_container">
    <div id="logo">
        <h1>{l s='PowaTag' mod='powatag'}</h1>
    </div>

    <div class="powatag_content">

        <div id="main">
            <h2>
            <span class="header-main">
                {l s='PowaTag' mod='powatag'}
            </span>
            <span class="header-note">
                {l s='business freedom is knowing your customer' mod='powatag'}
            </span>
            </h2>

            <p>
            {l s='PowaTag is an exciting new mobile business solution that seamlessly integrates the physical
                and online experience to transform the future of shopping and your business.' mod='powatag'}
            </p>

            <p>
            	{l s='PowaTag is retail\'s missing link combining the instant gratification of real world shopping with the
                speed,
                convenience and informative nature of internet retailing.' mod='powatag'}
            </p>

            <ul>
                <li> {l s='Easy to implement and integrate with existing retail system' mod='powatag'}</li>
                <li> {l s='Uses existing merchant checkout. PowaTag is not a new payment method' mod='powatag'}</li>
                <li> {l s='Reduce both online and mobile basket abandonment' mod='powatag'}</li>
                <li> {l s='Know your customer intimately' mod='powatag'}</li>
                <li> {l s='Reward loyalty seamlessly' mod='powatag'}</li>
                <li> {l s='Track demographics and geographics' mod='powatag'}</li>
            </ul>

            <p>
                {l s='Take away the barriers to mobile business and create a truly open market.' mod='powatag'}

            </p>
        </div>

        <div id="sidebar">
            <div class="sidebar-item sidebar-item-first">
                <h2>{l s='Create PowaTag Account' mod='powatag'}</h2>

                <p>
                    {l s='Pre-register to integrate PowaTag into your existing system' mod='powatag'}
                </p>

                <div class="button-wrapper">
                    <a class="button_powa" href="www.powatag.com/page/prestashop">
                        {l s='Click here to pre-register now' mod='powatag'}
                    </a>
                </div>
            </div>
            <div class="sidebar-item sidebar-item-last">
                <h2>{l s='Documentation & Support' mod='powatag'}</h2>

                <p>
                    {l s='Click' mod='powatag'} <a href="{l s='http://fe-dev.powaweb.io/pdf/usermanual.pdf' mod='powatag'}">{l s='here' mod='powatag'}</a> {l s='to download the user manual' mod='powatag'}
                </p>

                <p>
                    {l s='If you have any questions please visit our website,' mod='powatag'}
                    <a href="{l s='http://powatag.com' mod='powatag'}">{l s='powatag.com' mod='powatag'}</a>
                    {l s='or email' mod='powatag'}
                    <a href="mailto:{l s='powatagmerchantsupport@powa.com' mod='powatag'}">{l s='PrestaShop Enquiry' mod='powatag'}</a>
                </p>
            </div>
            <!--div class="sidebar-item sidebar-item-placeholder"></div-->
        </div>

		
		
    </div>
</div>


{else}

<fieldset> 
	{l s='To start transacting with your customers, switch to a live PowaTag account by' mod='powatag'}<a href="{l s='www.powatag.com/page/prestashop' mod='powatag'}"> {l s='contacting us' mod='powatag'}</a>
</fieldset>

<br/>
{/if}
</div>
<script>
	$(document).ready(function(){
		$('#powatag_marketing').insertAfter('.toolbar-placeholder');
	})
</script>