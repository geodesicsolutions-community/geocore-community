{* Used as main_body of another page, so no head/footer! *} 
{include file="common/invoice_body.tpl"}
{if $showOrderDetails}
	{include file="common/order_details.tpl"}
{/if}
{if !$in_admin}
	<br />
	<div class="center">
		<a href="{$classifieds_file_name}?a=4" class="button">{$messages.3185}</a>
	</div>
{/if}