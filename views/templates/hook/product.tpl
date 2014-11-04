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

<div class="powaTagWrapper">
	<div id="powatag">
		<div id="powatag0-powatag" class="powatag-powatag">
			<div id="powatag0-overlay"></div>
			<div id="powatag0-tagline-line-overlay" class="powatag-tagline powatag-label">
				{l s='Processing securely for:' mod='powatag'}
			</div>
			<div id="powatag0-tagline-line-overlay" class="powatag-tagline powatag-message"></div>
		</div>
	</div>
	<a href="#" rel="#powaTagPopup" id="powatagPopupLink"></a>
</div>

<div id="powaTagZoom">
	<div class="powaTagPopupWrapper">
		<div class="powaTagContent"></div>
	</div>
</div>

<div id="powaTagPopup">
	<a href="#" class="powaTagClose">X</a>
	<div class="powaTagHidden"></div>
	<div class="powaTagPopupWrapper">
		<div class="powaTagContent">
			<div class="powaTagLeft">
				<h3>{l s='What is powatag ?' mod='powatag'}</h3>
				<p>
					{l s='Powatag is an easy to use free app that allows you to quickly and securely make a purchase using your phone.' mod='powatag'}
				</p>
				<p>
					{l s='It is not an \'e-wallet\' because the app never holds a balance - it sends orders directly to a merchant who has signed up to use the PowaTag service.' mod='powatag'}
				</p>
				<p>
					{l s='If there is a PowaTag you can buy that item, whether it is on the internet, a printed ad or in audio - without having to do throught the normal checkout experience.' mod='powatag'}
				</p>
				<p>
					<iframe src="http://www.youtube.com/embed/p9VhmPWPzQE?rel=0" frameborder="0"></iframe>
				</p>
				<a href="http://itunes.apple.com/gb/app/powatag/id667504703?ls-l&mt-8" target="_blank" class="powatag-center powatag-download-link-ios">
					{l s='Download from the App Store' mod='powatag'}
				</a>
				<a href="http://play.google.com/store/apps/details?id=com.powatag.android.apps.powatag" target="_blank" class="powatag-center powatag-download-link-android">
					{l s='Download from Google Play' mod='powatag'}
				</a>
			</div>
			<div class="powaTagRight">
				<h3 class="powatag-center">{l s='Scan the tag below to pay' mod='powatag'}</h3>
				<p class="powatag-center">
					{l s='using the PowaTag app on your mobile device' mod='powatag'}
				</p>
				<div class="powaTagWrapper-large">
					<div id="powatag0-powatag-large" class="powatag-powatag-large"></div>
					<div id="powatag0-overlay-large"></div>
					<div id="powatag0-tagline-line1-overlay-large" class="powatag-tagline powatag-label">
						{l s='Processing securely for:' mod='powatag'}
					</div>
					<div id="powatag0-tagline-line2-overlay-large" class="powatag-tagline powatag-message"></div>
				</div>
			</div>
		</div>
	</div>
</div>
{literal}
<script>
	var urlToPowaTag = '{$powatagGeneratorURL}';
	var apiPowaTag = '{/literal}{$powatagApi}{literal}';
	var productSku = '{/literal}{$productSku}{literal}';
</script>
{/literal}
<!-- /PowaTag -->