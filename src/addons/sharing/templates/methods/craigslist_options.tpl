{* 7.5.3-36-gea36ae7 *}

{if $templateChoices}
	{$msgs.craigslist_options_tpl_choice} 
	<select name="selectedTemplate" class="field">
		{foreach from=$templateChoices item=tpl}
			<option value="{$tpl.id}">{$tpl.name}</option>
		{/foreach}
	</select>
{else}
	<input type="hidden" value="{$singleTemplate.id}" name="selectedTemplate" />
{/if}

<input type="hidden" name="responseType" id="responseType" value="" />
<ul class="button_list">
	<li><input type="submit" class="button" value="{$msgs.craigslist_preview_btn}" onclick="jQuery('#responseType').val('preview');" /></li>
	<li><input type="submit" class="button" value="{$msgs.craigslist_html_btn}" onclick="jQuery('#responseType').val('html');" /></li>
</ul>