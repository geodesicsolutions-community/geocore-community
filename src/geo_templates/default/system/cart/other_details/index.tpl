{* 7.5.3-125-gf5f0a9a *}

{if !$full_step}
	{include file="system/cart/cart_steps.tpl"}
	{include file='system/cart/inline_preview_box.tpl'}
{/if}

{if !$steps_combined}
	{foreach $error_msgs as $err_name => $err_msg}
		<div class="field_error_box">
			{$err_msg}
		</div>
	{/foreach}
{/if}
{if !$full_step && !$steps_combined}<form action="{$form_url}" method="post">{/if}

	<div class="content_box">
		{if $steps_combined}
			<h1 class="title">{$page_title2}</h1>
			<p class="page_instructions">{$page_desc}</p>
		{elseif !$full_step}
			<h1 class="title">{$page_title1}</h1>
			<h3 class="subtitle">{$page_title2}</h3>
			<p class="page_instructions">{$page_desc}</p>
		{/if}

		{foreach from=$items item=item key=item_key}
			{* ::Allow a specific order item to over-write parts of the template:: *}
			{if $item.entire_box ne ''}
				{$item.entire_box}
			{else}
				{include file='system/cart/other_details/item_box.tpl' left=$item.left right=$item.right checkbox=$item.checkbox checkbox_hidden=$item.checkbox_hidden checked=$item.checked checkbox_name=$item.checkbox_name price_display=$item.price_display title=$item.title display_help_link=$item.display_help_link}
			{/if}
		{/foreach}

		<div class='clear'></div>
	</div>
	{if !$full_step && !$steps_combined}
		<div class="center">
			{if $showPreviewButton}
				<br /><br />
				<input type="submit" name="forcePreview" value="{$preview_button_txt}" class="button" />
			{/if}
			{if $forcePreviewButtonOnly}
				{* Use hidden main submit, that way can only be "clicked" using JS *}
				<input type="submit" value="1" style="display: none;" class="mainSubmit" />
			{else}
				<br /><br />
				<input type="submit" value="{$submit_button_text}" class="button mainSubmit" />
			{/if}
			<br /><br />
			<a href="{$cancel_url}" class="cancel">{$cancel_text}</a>
		</div>
	{/if}

{if !$full_step && !$steps_combined}</form>{/if}
