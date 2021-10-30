{* 7.5.2-29-g8b67c08 *}

{include file="cart_steps.tpl" g_resource="cart"}

<div class="content_box">
	<h1 class="title">{$msgs.placementText1}</h1>
	
	<p class="page_instructions">
		{$msgs.placementText2} <strong class="text_highlight">{$newPass}</strong>
	</p>
	
	{if !$steps_combined}<form action="{$process_form_url}" method="post">{/if}
		{if $use_eula}
				{* user agreement field *}
				
				<div class="anonymous_eula" style="margin-left: 5px;">
					{if $error}<div class="error_message">{$error}</div>{/if}
					<p style="font-weight: bold;"><input type="checkbox" value="1" name="eula" /> {$msgs.eulaLabel}</p>
					
					{if $eula_type == "div"}
						<div class="usage_agreement">{$eula_text}</div> 
					{elseif $eula_type == "area"}
						<textarea name="registration_agreement" class="field usage_agreement" readonly="readonly" onfocus="this.blur();">{$eula_text}</textarea>
					{elseif $eula_type == "hide"}
						{* nothing to print here. probably linking to external Terms page from previous text *}
					{/if} 
				</div>
		{/if}
		{if !$steps_combined}
			<div class="center">
				<input type="submit" value="{$msgs.placementContinueLink}" class="button" />
			</div>
		{/if}
	{if !$steps_combined}</form>{/if}
	<br />
</div>
<br />
<div class="center">
	{if $msgs.placementCancelLink && !$steps_combined}
		<a href="{$cart_url}&amp;action=cancel" class="cancel">{$msgs.placementCancelLink}</a>
	{/if}
</div>