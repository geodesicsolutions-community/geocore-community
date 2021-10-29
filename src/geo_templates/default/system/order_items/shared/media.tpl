{* 7.5.3-125-gf5f0a9a *}

{if $main_type == 'listing_edit'}
	{* Template designers: if you want to use a totally different template file for edit
	   listings, you would surround it with these if smarty tags.  The same goes for classified,
	   auction, and reverse_auction, just change 'listing_edit' as appropriate in the if stmt *}
	
{/if}

{include file="cart_steps.tpl" g_resource="cart"}
{include file='inline_preview_box.tpl' g_resource='cart'}

{if $error_msgs.cart_error}
	<div class='field_error_box'>{$error_msgs.cart_error}</div>
{/if}
<div class="content_box">
	{if !$steps_combined}
		<h1 class="title">{$title1}</h1>
		<h3 class="subtitle">{$title2}</h3>
		<p class="page_instructions">{$page_description}</p>
	{/if}
	
	{if !$steps_combined}<form method="post" action="{$process_form_url}" enctype="multipart/form-data">{/if}
		{foreach from=$mediaTemplates item=template}
			{include file=$template.file g_type=$template.g_type g_resource=$template.g_resource}
		{/foreach}
		{if $steps_combined}
			<input type="hidden" name="media_submit_form" value="1" />
		{else}
			<div class="center">
				<input type="hidden" name="media_submit_form" value="1" />
				{if $showPreviewButton}
					<br /><br />
					<input type="submit" name="forcePreview" value="{$preview_button_txt}" class="button" />
				{/if}
				{if $forcePreviewButtonOnly}
					{* Use hidden main submit, that way can only be "clicked" using JS *} 
					<input type="submit" value="1" style="display: none;" class="mainSubmit" />
				{else}
					<br /><br />
					<input type="submit" value="{$messages.500757}" class="button mainSubmit" />
				{/if}
				<br /><br />
				<a href="{$cart_url}&amp;action=cancel" class="cancel">{$cancel_txt}</a>
			</div>
		{/if}
	{if !$steps_combined}</form>{/if}
</div>