{* 7.4.4-10-g8576128 *}

{$adminMsgs}
{include file="admin/design/parts/workingOn.tpl"}

<fieldset>
	<legend>Edit Template(s) Attached for This Page</legend>
	<div>
		{if !$read_only}<form action="index.php?page=page_attachments_edit&amp;pageId={$pageId|escape}&amp;t_set={$t_set|escape}" method="post">{/if}
			{if $read_only}
				<div class="{cycle values="row_color1,row_color2"}">
					<div class="leftColumn">VIEW ONLY</div>
					<div class="rightColumn">
						Changes to default template set not permitted!
					</div>
					<div class="clearColumn"></div>
				</div>
			{/if}
			{if $advMode}
				<input type="hidden" name="t_set" value="{$t_set}" />
				{if $workWithList.1}
					<div class="{cycle values="row_color1,row_color2"}">
						<div class="leftColumn">Attachment(s) Saved For:</div>
						<div class="rightColumn">
							<span class="text_green">{$t_set}</span>
							<br />
							<strong>Change to:</strong>
							{foreach from=$workWithList item=workWith}
								{if $workWith!=$t_set}
									[<a href="index.php?page=page_attachments_edit&amp;pageId={$pageId|escape}&amp;t_set={$workWith|escape}">{$workWith}</a>] &nbsp;
								{/if}
							{/foreach}
						</div>
						<div class="clearColumn"></div>
					</div>
				{/if}
				{*  Commenting out for now, don't think this is a feature that
					would be used much, and it just makes things more complicated...
					The below would be the alternate to showing "Attachment(s) Saved For:" above

				<div class="{cycle values="row_color1,row_color2"}">
					<div class="leftColumn">Attachments from:</div>
					<div class="rightColumn">
						<strong>{$t_set}</strong>/main_page/attachments/templates_to_page/{$pageId}.php
						{if $workWithList.1}
							<br />
							<strong>Change to:</strong>
							{foreach from=$workWithList item=workWith}
								{if $workWith!=$t_set}
									[<a href="index.php?page=page_attachments_edit&amp;pageId={$pageId|escape}&amp;t_set={$workWith|escape}">{$workWith}</a>] &nbsp;
								{/if}
							{/foreach}
						{/if}
					</div>
					<div class="clearColumn"></div>
				</div>
				{if !$read_only}
					<div class="{cycle values="row_color1,row_color2"}">
						<div class="leftColumn">Save attachment changes to:</div>
						<div class="rightColumn">
							<select name="t_set">
								{foreach from=$workWithList item="workWith"}
									{if $workWith!='default' || $canEditDefault}
										<option{if $t_set==$workWith} selected="selected"{/if}>{$workWith}</option>
									{/if}
								{/foreach}
							</select>
							/main_page/attachments/templates_to_page/{$pageId}.php
						</div>
						<div class="clearColumn"></div>
					</div>
				{/if}
				*}
				<div class="{cycle values="row_color1,row_color2"}">
					<div class="leftColumn">Page ID:</div>
					<div class="rightColumn">
						{$pageId}
					</div>
					<div class="clearColumn"></div>
				</div>
			{else}
				<input type="hidden" name="t_set" value="{$t_set}" />
			{/if}
			{if $addonTitle}
				<div class="{cycle values="row_color1,row_color2"}">
					<div class="leftColumn">Addon:</div>
					<div class="rightColumn">
						{$addonTitle}
					</div>
					<div class="clearColumn"></div>
				</div>
			{/if}
			{if $addon||$pageInfo.extraPage}
				<div class="{cycle values="row_color1,row_color2"}">
					<div class="leftColumn">Page URL:</div>
					<div class="rightColumn">
						{if $addon}
							<a href="{$classifieds_url}?a=ap&amp;addon={$addon}&amp;page={$addonPage}" onclick="window.open(this.href); return false;">{$classifieds_url}?a=ap&amp;addon={$addon}&amp;page={$addonPage}</a>
						{/if}
						{if $pageInfo.extraPage}
							<a href="{$classifieds_url}?a=28&amp;b={$pageId}" onclick="window.open(this.href); return false;">{$classifieds_url}?a=28&amp;b={$pageId}</a>
						{/if}
					</div>
					<div class="clearColumn"></div>
				</div>
			{/if}
			<div class="{cycle values="row_color1,row_color2"}">
				<div class="leftColumn">Default Template:</div>
				<div class="rightColumn">
					{if $attachments.1.0 && !$templates[$attachments.1.0]}
						<div class="error"><strong>Warning:</strong> Could not find the current template attached (<strong>{$attachments.1.0}</strong>) within working-with template sets.
							If you save changes, the default template will be changed to the selected template below.
						</div>
					{/if}
					{if $read_only}
						{$attachments.1.0}
					{else}
						{include g_type="admin" file="design/parts/templateDropdown.tpl" templateSelected=$attachments.1.0 selectName='attachments[1][0]' selectId=defaultTemplate}
					{/if}
					{if $from_defaults}
						<br /><div class="error"><strong>Note:</strong>  Attachments loaded from defaults.  Verify/change template attachment(s) and save changes.</div>
					{elseif !$attachments.1.0}
						<br /><div class="error">Not currently set in the template set!  Choose a default template and save changes.</div>
					{/if}
				</div>
				<div class="clearColumn"></div>
			</div>
			<br /><br />
			<div class="col_hdr_top">Additional Template Choices</div>
			<table>
				<thead>
					<tr class="col_hdr">
						<th style="color: red; width: 5px;">x</th>
						<th>Template Choice</th>
					</tr>
				</thead>
				<tbody>
					{assign var=altCount value=0}
					{foreach from=$attachments item=cats key=languageId}
						{foreach from=$cats item=attachment key=catId}
							{if !($languageId==1&&$catId==0)}
								{assign var=altCount value=1}
								<tr class="{cycle values="row_color1,row_color2"}">
									<td style="text-align: center;"><input type="checkbox" name="delete[{$languageId}][{$catId}]" value="1" /></td>
									<td>
										<input type="hidden" name="attachments[{$languageId}][{$catId}]" value="{$attachment}" />
										{$attachment}
									</td>
								</tr>
							{/if}
						{/foreach}
					{/foreach}
					{if !$altCount}
						<tr>
							<td colspan="4"><div class="page_note_error">No additional template choices for users to select for their storefront.</div></td>
						</tr>
					{/if}
					{if !$read_only}
						<tr class="col_ftr">
							<td style="text-align: center; vertical-align: middle; font-size: 16px; font-weight: bold;" class="text_green">+</td>
							<td>
								<input type="hidden" name="new[cat][languageId]" value="1" />
								<input type="hidden" name="new[cat][catIdNocheck]" value="{$newCatId}" />

								{include g_type="admin" file="design/parts/templateDropdown.tpl" showBlankTemplate=1 selectName='new[cat][template]'}
							</td>
						</tr>
					{/if}
				</tbody>
			</table>
			{if !$read_only}
				<div style="text-align: center;"><input type="submit" name="auto_save" value="Save" /></div>
			{/if}
		{if !$read_only}</form>{/if}
	</div>
</fieldset>
