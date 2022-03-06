{* @git-info@ *}
<h2 class="title section-collapser">
	{if !$allFree}
		<span class="alignright">{$cartTotal|displayPrice:false:false:'cart'}</span>
	{/if}
	{if $allFree}
		<span class="glyphicon glyphicon-shopping-cart"></span>&nbsp;{$messages.500647}
	{else}
		<span class="glyphicon glyphicon-shopping-cart"></span>&nbsp;{$messages.500646}
	{/if}
</h2>
{* Mini-cart information *}
<div class="content_box mini-cart">
	<ul>
		<li class="my_account_links_{if $cartLink.active}active{else}inactive{/if}">
			<h3 class="subtitle">
				{$cartItemCount} {if $cartItemCount == 1}{$messages.501015}{else}{$messages.500648}{/if}
			</h3>
			{if $cartItems}
				<ul>
					{foreach from=$cartItems item='item'}
						<li class="{cycle values='row_odd,row_even'}">
							{$item.title} {if !$allFree}<span class="alignright">{$item.cost}</span>{/if}
						</li>
					{/foreach}
				</ul>
			{/if}
			<div style="clear: both;"></div>
		</li>
	</ul>
	<div class="cntr"><a class="button" href="{$classifieds_file_name}?a=cart">{$messages.500655}</a></div>
	
	{* Cart actions *}
	{if $cartAction}
		<h3 class="subtitle">{$messages.500649}</h3>
		<div style="padding: 5px; font-weight: bold;">
			<span class="item-in-progress"><span class="glyphicon glyphicon-circle-arrow-right"></span>&nbsp;{$cartAction}</span>
			<div class="alignright">
				{if !$inCart || !$cartStepIndex}
					{* This is the resume button - only shown if on a page "ouside" the cart *}
					<a href="{$classifieds_file_name}?a=cart" class="edit">{$messages.500650} {$messages.500651}</a>
				{/if}
				{*  This is the cancel button *}
				<a href="{$classifieds_file_name}?a=cart&amp;action=cancel" class="delete">{$messages.500652} {$messages.500653}</a>
			</div>
		</div>
		
			
		
	{/if}

	{if $cartLinks}
		<h3 class="subtitle">
			{$messages.500654}
		</h3>
		<ul>
			{foreach from=$cartLinks item=listItem}
				{if $listItem.icon||$listItem.label}
					<li class="my_account_links_{if $listItem.active}active{else}inactive{/if}">
						{if $listItem.link}
							<a href="{$listItem.link}" class="user_links{if $listItem.needs_attention} needs_attention{/if}">
						{else}
							<span class="user_links{if $listItem.needs_attention} needs_attention{/if}">
						{/if}
							{if $listItem.icon}
								{$listItem.icon}
							{/if}
							{$listItem.label}
						{if !$listItem.link}
							</span>
						{else}
							</a>
						{/if}
					</li>
				{/if}
			{/foreach}
		</ul>
	{/if}
	
</div>