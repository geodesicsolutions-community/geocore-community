{* 7.6.3-149-g881827a *}

{include file='system/cart/cart_steps.tpl'}

<div class="content_box">
	{if !$steps_combined}
		<h1 class="title">{$title1}</h1>
	{/if}
	<h3 class="subtitle">{$title2} {$help_link}</h3>
	<p class="page_instructions">
		{$desc1}
	</p>

	<div class="leveled_cat{if $steps_combined} combined_update_fields{/if}{if $error_msgs.category} field_error_row{/if}">
		{if $error_msgs.category}
			<div class="error_message">{$error_msgs.category}</div>
		{/if}
		{$lev_field=$cats}
		{foreach $cats.levels as $info}
			{include file='system/order_items/shared/leveled_fields/level.tpl'}
		{/foreach}
	</div>
</div>

{if !$steps_combined}
	<div class="center">
		<a href="{$cart_url}&amp;action=cancel" class="cancel">{$cancel_txt}</a>
	</div>
{/if}
