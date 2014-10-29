<h2>{l s='Order confirmed' mod='powatag'}</h2>

<p>
{l s='An order has been successfully created.' mod='powatag'}<br/>
{l s='If you need to contact the merchant because of the order please use the following reference : ' mod='powatag'} {$order->reference}.<br/>
{l s='The order state is : ' mod='powatag'} {$state->name}. <br/>
</p>

<p>
	{l s='You can visit the shop by clicking' mod='powatag'} <a href="{$link->getPageLink('index')}"> {l s='here' mod='powatag'}</a>
</p>