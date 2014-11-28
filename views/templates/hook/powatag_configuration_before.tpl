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
<div id="powatag_container">
    
    <div id="logo">
        <h1>{l s='PowaTag' mod='powatag'}</h1>
    </div>

    <div id="powatag_content">

        <div id="sidebar">
            <h2>{l s='PowaTag Features' mod='powatag'}</h2>

            <ul id="features">
                <li>{l s='Increase mobile & online conversion rates' mod='powatag'}</li>
                <li>{l s='Sell anywhere, anytime' mod='powatag'}</li>
                <li>{l s='Enable one touch purchasing' mod='powatag'}</li>
                <li>{l s='Omni-channel solution that opens up new sales channels' mod='powatag'}</li>
                <li>{l s='Engage, incentivise and reward customer loyalty' mod='powatag'}</li>
            </ul>

            <iframe width="413" height="210"
                    src="{l s='http://www.youtube.com/embed/or0L7UnaP6g?controls=0&amp;showinfo=0&amp;rel=0' mod='powatag'}"
                    frameborder="0" allowfullscreen></iframe>

            <p class="user-manual">
                {l s='For further support' mod='powatag'},
                <a href="{l s='http://fe-dev.powaweb.io/pdf/usermanual.pdf' mod='powatag'}" target="_blank">{l s='click here to download the PowaTag User Manual' mod='powatag'}​
                </a>
            </p>
        </div>

        <div id="powatag_main">
            <h2>
            <span class="header-main">
                {l s='PowaTag' mod='powatag'}
            </span>
            <span class="header-note">
                {l s='business freedom is knowing your customer' mod='powatag'}
            </span>
            </h2>

            <p>
                {l s='PowaTag is an exciting new mobile business solution that seamlessly integrates the physical and online experience to transform the future of shopping and your business.' mod='powatag'}
            </p>

            <p>
                {l s='As retail\'s missing link, PowaTag combines the instant gratification of real world shopping with the speed, convenience and informative nature of internet retailing.' mod='powatag'}
            </p>

            <p>
                {l s='PowaTag acts as a mobile payment enabler, integrating directly with PrestaShop\'s platform and existing payment infrastructures. ​' mod='powatag'}
            </p>

            <h4>{l s='The advantages of PowaTag' mod='powatag'}:</h4>

            <ul id="adventages">
                <li>
                    {l s='Offer a seamless consumer experience across all purchase channels' mod='powatag'}
                </li>
                <li>
                    {l s='Allow customers to purchase straight from your product pages' mod='powatag'}
                </li>
                <li>
                    {l s='Enable customers to download your catalogue and purchase at their
                    leisure from their mobile' mod='powatag'}
                </li>
                <li>
                    {l s='Experience an omni-channel solution that truly opens up new sales channels,
                    generating new revenue for you' mod='powatag'}
                </li>
                <li>
                    {l s='Sell directly and instantly from online advertising, broadcast and print' mod='powatag'}
                </li>
                <li>
                    {l s='Interact with customers – engaging, incentivising, rewarding
                    loyalty and generating action' mod='powatag'}
                </li>
                <li>
                    {l s='Drive and support impulse purchases, by simplifying the buying experience
                    within existing e-commerce workflows' mod='powatag'}
                </li>
            </ul>

            <div id="register-wrapper">
                <div class="register">
                    <a class="button_powa" href="{l s='http://www.powatag.com/page/prestashop' mod='powatag'}">
                        {l s='Register now' mod='powatag'}
                    </a>
                </div>

                <div class="questions">
                    {l s='Got questions?' mod='powatag'} <br/>
                    {l s='Visit our website' mod='powatag'}
                    <a href="{l s='http://powatag.com' mod='powatag'}" target="_blank">{l s='powatag.com' mod='powatag'}</a>
                    or email
                    <a href="mailto:{l s='powatagmerchantsupport@powa.com' mod='powatag'}">{l s='PrestaShop Enquiry' mod='powatag'}</a>
                </div>
            </div>

            <div class="clearfix clear"></div>
        </div>
    </div>
</div>


{else}

<fieldset> 
	{l s='To start transacting with your customers, switch to a live PowaTag account by' mod='powatag'}<a href="{l s='http://www.powatag.com/page/prestashop' mod='powatag'}" target="_blank"> {l s='contacting us' mod='powatag'}</a>
</fieldset>

<br/>
{/if}
</div>
<script>
	$(document).ready(function(){
		$('#powatag_marketing').insertAfter('.toolbar-placeholder');
	})
</script>