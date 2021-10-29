{* 16.09.0-79-gb63e5d8 *}

<style type="text/css">

/* Specify URL locations in tpl file so that external tag can be used to generate.
Rest of styles will be in css/cart.css file. */

{literal}
h1.title,
div.body_html h1.title {
	background: #7ca93a url('../{/literal}{external file="images/backgrounds/c_bar_secondary.gif" forceDefault=1}{literal}');
}

h2.title,
div.body_html h2.title {
	background: #4174a6 url('../{/literal}{external file="images/backgrounds/c_bar_primary.gif" forceDefault=1}{literal}');
}

input.field,
textarea.field,
select.field,
div.field, input.editor_field {
	background: #ffffff url('../{/literal}{external file="images/backgrounds/form_input.gif" forceDefault=1}{literal}') repeat-x top left;
}

.order_left_column ul li a:link,
.order_left_column ul li a:visited
{
	background: #fefefe url('../{/literal}{external file="images/backgrounds/bullet_list_arrow_1.gif" forceDefault=1}{literal}') no-repeat left center;
}

.order_left_column ul li a:hover,
.order_left_column ul li a:active
{
	background: #f3f3f3 url('../{/literal}{external file="images/backgrounds/bullet_list_arrow_2.gif" forceDefault=1}{literal}') no-repeat left center;
}

.cart_item {
	background: #f7f7f7 url('../{/literal}{external file="images/backgrounds/cart_item_arrow_1.gif" forceDefault=1}{literal}') no-repeat 2px center;
}

.cart_item:hover {
	background: #ffffe1 url('../{/literal}{external file="images/backgrounds/cart_item_arrow_2.gif" forceDefault=1}{literal}') no-repeat 2px center;
}

#cart_steps li {
	background: url('../{/literal}{external file="images/backgrounds/cart_step.gif" forceDefault=1}{literal}') no-repeat top right;
}

.price {
	font-size: inherit;
}

.show_instructions_button {
	/* hide these buttons because they're site-text and set relative to the front-end, so they just show broken images here */
	display: none;
}

{/literal}
</style>

{$adminMsgs}

<div class="order_left_column col-xs-12 col-lg-2">
	<fieldset>
		<legend>Order Tools</legend>
		<div>
			<br />
			<strong>Order For: </strong> <span class="blue_color">{$cartUsername} ({$cartUserId})</span>
			<br /><br />
			<div class="center">
				<a href="index.php?page=admin_cart_delete&amp;userId={$cartUserId}&amp;auto_save=1" class="mini_cancel lightUpLink">Delete This Entire Order</a>
			</div>
			<br />
			<div class="left_cart_content_box">
				<h2 class="title">
					<a href="{$cart_url}">
						Admin-Side Order Cart
					</a>
				</h2>
				<ul>
					<li	class="my_account_links_{if $cartLink.active}active{else}inactive{/if}">
						<h1 class="subtitle">
							{if !$allFree}
								<span class="alignright price">{$cartTotal|displayPrice}</span>
							{/if}
							{$cartItemCount} Items
						</h1>
						<div style="clear: both;"></div>
					</li>
					<li><a href="{$cart_url}">Refresh Cart</a></li>
				</ul>
				{* Cart actions *}
				{if $cartAction}
					<h1 class="subtitle">In Progress: <span class="sub_note">{$cartAction}</span></h1>
					<ul>
						{*  This is the cancel button *}
						<li class="my_account_links_inactive">
							<a href="{$cart_url}&amp;action=cancel">Cancel &amp; Remove</a>
						</li>
					</ul>
				{/if}
		
				{if $cartLinks}
					<h1 class="subtitle">
						Add Items:
					</h1>
					<ul>
						{foreach from=$cartLinks item=listItem}
							<li	class="my_account_links_{if $listItem.active}active{else}inactive{/if}">
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
						{/foreach}
					</ul>
				
					{if $cartUserId && $cartStepIndex=='cart' && !$cartAction}
						<h1 class="subtitle">Swap with Client's Cart</h1>
						<ul>
							{if $cartItemCount}
								<li>
									<a href="index.php?page=admin_cart_swap&amp;userId={$cartUserId}&amp;direction=to" class="lightUpLink">Send Items to Client's Cart</a>
								</li>
							{/if}
							<li>
								<a href="index.php?page=admin_cart_swap&amp;userId={$cartUserId}&amp;direction=from" class="lightUpLink">Get Items from Client's Cart</a>
							</li>
						</ul>
					{/if}
				{/if}
			</div>
			<br />
			<div class="center">
				<a href="index.php?page=orders_list&narrow_order_status=all&narrow_gateway_type=all&narrow_admin={$user.id}&sortBy=order_id&sortOrder=down" class="mini_button">
					Recently Created Orders
				</a>
			</div>
		</div>
	</fieldset>
	
	{if $ordersInProgress}
		{include file='cart/orders_in_progress.tpl' showLinkWarning=1}
	{/if}
	<div class="clearColumn"></div>
</div>

<div class="col-xs-12 col-lg-10">
	<fieldset>
		<legend>Admin-Side Cart for {$cartUsername} ({$cartUserId})</legend>
		<div style="position: relative;">
			{if $cart_tpl_files.body_html_system}
				{include file=$cart_tpl_files.body_html g_type='system' g_resource=$cart_tpl_files.body_html_system}
			{elseif $cart_tpl_files.body_html_addon}
				{include file=$cart_tpl_files.body_html g_type='addon' g_resource=$cart_tpl_files.body_html_addon}
			{elseif $cart_tpl_files.body_html}
				{include file=$cart_tpl_files.body_html}
			{elseif !$cart_body}
				Error:  No cart template??  This should not happen!
			{/if}
			{$cart_body}
		</div>
	</fieldset>
</div>