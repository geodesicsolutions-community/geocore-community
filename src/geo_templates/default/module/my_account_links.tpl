{* 7.6.3-149-g881827a *}
{if $mobile_header == 1}
	{if $show_my_account_section} {* intentionally gets its own, nested "if" to prevent cascading *}
		<a href="index.php?a=4" class="menu-link">{$messages.500542}&nbsp;<span class="glyphicon glyphicon-user pc-hide"> </span></a>
		{foreach from=$links item=listItem}
			<a href="{$listItem.link}" class="menu-link submenu-link{if $listItem.needs_attention} needs_attention{/if}">
				{$listItem.label}&nbsp;{if $listItem.badge === 0 || $listItem.badge > 0}<span class="badge">{$listItem.badge}</span>{/if}&nbsp;<span class="glyphicon glyphicon-chevron-right"> </span>
			</a>
		{/foreach}
	{/if}
{elseif $mini_cart_only == 1}
	{if $show_cart}
		{$geo_mini_cart_displayed=1 scope='global'}
		{include file="module/my_account_links_minicart"}
	{/if}
{else}
	{if $show_cart}
		{* Need to let the main cart know, wherever it is, that a mini cart was displayed *}
		{$geo_mini_cart_displayed=1 scope='global'}
		<div id="left_cart">
			{include file="module/my_account_links_minicart"}
		</div>
	{/if}

	{if $show_account_finance_section && ($orderItemLinks || $paymentGatewayLinks)}
		{capture assign=accountFinanceContents}
			{* Capture the links, then put them where we want them. *}
			{if $orderItemLinks}
				<ul>
					{foreach from=$orderItemLinks item=itemLink}
						{if $itemLink.icon||$itemLink.label}
							<li class="my_account_links_{if $itemLink.active}active{else}inactive{/if}">
								{if $itemLink.link}
									<a href="{$itemLink.link}"{if $itemLink.needs_attention} class="needs_attention"{/if}>
								{else}
									<h3 class="subtitle normal_text {if $itemLink.needs_attention}needs_attention{/if}">
								{/if}
									{if $itemLink.icon}
										{$itemLink.icon}
									{/if}
									{$itemLink.label}

								{if !$itemLink.link}</h3>{else}</a>{/if}
							</li>
						{/if}
					{/foreach}
				</ul>
			{/if}
			{if $paymentGatewayLinks}
				<ul>
					{foreach from=$paymentGatewayLinks item=gatewayLink}
						{if $gatewayLink.icon||$gatewayLink.label}
							<li class="my_account_links_{if $gatewayLink.active}active{else}inactive{/if}">
								{if $gatewayLink.link}
									<a href="{$gatewayLink.link}"{if $gatewayLink.needs_attention} class="needs_attention"{/if}>
								{else}
									<h3 class="subtitle normal_text {if $gatewayLink.needs_attention}needs_attention{/if}">
								{/if}
									{if $gatewayLink.icon}{$gatewayLink.icon}{/if}
									{$gatewayLink.label}

								{if !$gatewayLink.link}</h3>{else}</a>{/if}
							</li>
						{/if}
					{/foreach}
				</ul>
			{/if}
		{/capture}
	{/if}
	{if $accountFinanceContents && $messages.500803}
		<br />
		<div class="content_box">
			<h3 class="title section-collapser">
				{$messages.500803}
			</h3>
			<div class="content_box">
				{$accountFinanceContents}
				{assign var=accountFinanceContents value=''}
			</div>
		</div>
	{/if}
	{if $show_my_account_section}
		<br />
		<div class="content_box">
			<h2 class="title section-collapser">{$messages.500542}</h2>
			<div class="content_box">
				<ul>
					{foreach from=$links item=listItem}
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

								{if $listItem.badge === 0 || $listItem.badge > 0}
									<span class="badge">{$listItem.badge}</span>
								{/if}

								{if !$listItem.link}
									</span>
								{else}
									</a>
								{/if}

							</li>
						{/if}
					{/foreach}
				</ul>
				{$accountFinanceContents}
			</div>
		</div>
	{/if}
{/if}
