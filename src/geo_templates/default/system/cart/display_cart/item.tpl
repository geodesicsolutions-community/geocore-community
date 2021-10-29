{* 7.3beta5-6-gf108e28 *}

<div class="{if $item.css_class}{$item.css_class}{else}cart_item{if $is_sub eq 1}_child{/if}{/if} clearfix">
	<div class="cart_item_label">
		{$item.title}
		{if $in_admin && !$view_only && $item.needAdminApproval}
			<br />
			<label>
				<input type="checkbox" name="needAdminApproval_skip[{$k}]" value="1"{if $item.needAdminApproval_skip} checked="checked"{/if} class="pre_approve_checkbox" />
				{$messages.500947} {$admin_auto_approve_help}
			</label>
		{/if}
	</div>
	{if !$allFree}
		<div class="cart_item_cost">	
			{if !$view_only}		
				{$item.priceDisplay}
			{else}
				{$item.total|displayPrice:false:false:'cart'}
			{/if}
		</div>
	{/if}

	{if !$view_only}
		<div class="cart_item_buttons">
			{if !$allFree && $item.canAdminEditPrice && $in_admin}
				<a href="index.php?page=admin_cart_edit_price&amp;userId={$cart_user_id}&amp;item={$k}" class="edit lightUpLink" onclick="return false;">Edit <em class="text_blue">Price</em></a>
			{/if}
			{if $item.canEdit}
				<a href="{$cart_url}&amp;action=edit&amp;item={$k}" class="edit">{$messages.500260}</a>
			{/if}
			{if $item.canDelete}
				<a href="{$cart_url}&amp;action=delete&amp;item={$k}" class="delete" onclick="return confirm('{$messages.502168|escape_js}');">{$messages.500261}</a>
			{/if}
			{if $item.canPreview}
				<a href="{$cart_url}&amp;action=preview&amp;item={$k}" onclick="window.open(this.href,'previewWindow','scrollbars=yes,status=no,width=800,height=600'); return false;" class="preview">{$messages.500262}</a>
			{/if}
		</div>
	{/if}
</div>

{if $item.children ne false}
	{if !$view_only}
		{foreach from=$item.children key=sk item=sub_item}
			{include file="display_cart/item.tpl" k=$sk item=$sub_item is_sub=1}
		{/foreach}
		{if !$allFree && !$is_sub}
			<div class="cart_item_subtotal">
				{$messages.500263} &nbsp; {$item.total|displayPrice}
			</div>
		{/if}
	{else}
		<div class="cart_item_child_mini">
			<ul>
				{foreach from=$item.children key=sk item=sub_item}
					<li>&raquo; {$sub_item.title}</li>
				{/foreach}
			</ul>
		</div>
	{/if}
{/if}
