{* 7.1.2-41-gc6918fc *}
{* 
	Note: This template used in cart checkout process anywhere a splash page is used.
	If you need it, the item type will be stored in $item_name (such as classified, auction,
	renew_upgrade, etc.)
 *}

{include file="cart_steps.tpl" g_resource="cart"}

{$splash}
{if !$steps_combined}
	<div class="center">
		<a href="{$process_form_url}" class="button">
			{$next_text}
		</a>
	</div>
{/if}
