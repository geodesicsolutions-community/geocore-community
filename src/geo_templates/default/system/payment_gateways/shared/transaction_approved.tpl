{* 7.6.3-113-gedc166d *}

<div class="content_box">
	<h1 class="title">{$page_title}</h1>

	{if $success_failure_message}
		<p class="page_instructions">{$page_desc}</p>
		<div class="success_box">
			{$success_failure_message}
		</div>
	{else}
		<div class="success_box">
			{$page_desc}
		</div>
	{/if}

	<br />

	{if $cart_items}
		<div>
			<h3 class="title">{$messages.500896}</h3>
			{include file='system/cart/display_cart/index.tpl' view_only=1 items=$cart_items}
			<div class="clear"></div>
		</div>
		<br />
	{/if}
	{if $invoice_url}
		<div class="center">
			<a href="{$invoice_url}" class="button{if $in_admin} lightUpLink{/if}"{if $in_admin} onclick="return false;"{/if}>
				{$messages.500949}
			</a>
		</div>
	{/if}
	{if $edited_listing_id}
		<div class="center">
			<a href="{$classifieds_url}?a=2&amp;b={$edited_listing_id}" class="button">
				{$messages.502385}
			</a>
		</div>
	{/if}
	{if $logged_in&&!$in_admin}
		<div class="center">
			<a href="{$my_account_url}" class="button">
				{$my_account_link}
			</a>
		</div>
	{/if}
	{if $in_admin && $user.id}
		<div class="center">
			<a href="index.php?page=orders_list&narrow_order_status=all&narrow_gateway_type=all&narrow_admin={$user.id}&sortBy=order_id&sortOrder=down" class="button">
				Recently Created Orders
			</a>
		</div>
	{/if}
</div>
