{* 16.09.0-79-gb63e5d8 *}
{$adminMsgs}

{include file="design/parts/workingOn.tpl"}

<div class="page-title1">Template(s) Attached to:
	<span class="color-primary-two">
		{if $addonTitle}
			{$addonTitle} - 
		{/if}
		{$pageName}
		{if $advMode}
			(Page ID: {$pageId})
		{/if}
		{if $pageInfo.admin_label} - {$pageInfo.admin_label}{/if}
	</span>
</div>

<fieldset>
	<legend>Edit Template(s) Attached to this Page</legend>
	<div class='x_content'>
		{if !$read_only}<form action="index.php?page=page_attachments_edit&amp;pageId={$pageId|escape}&amp;t_set={$t_set|escape}" class="form-horizontal form-label-left" method="post">{/if}
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
			{else}
				<input type="hidden" name="t_set" value="{$t_set}" />
			{/if}
			{if $addonTitle}
				<div class='form-group'>
				<label class='control-label col-md-5 col-sm-5 col-xs-12'>Addon: </label>
				  <div class='col-md-6 col-sm-6 col-xs-12'>
				  	<span class='vertical-form-fix'>{$addonTitle}</span>
				  </div>
				</div>
			{/if}
			{if $addon||$pageInfo.extraPage}
				<div class='form-group'>
				<label class='control-label col-md-5 col-sm-5 col-xs-12'>Page URL: </label>
				  <div class='col-md-6 col-sm-6 col-xs-12'>
				  	<span class='vertical-form-fix'>						
				  		{if $addon}
							<a href="{$classifieds_url}?a=ap&amp;addon={$addon}&amp;page={$addonPage}" onclick="window.open(this.href); return false;">{$classifieds_url}?a=ap&amp;addon={$addon}&amp;page={$addonPage}</a>
						{/if}
						{if $pageInfo.extraPage}
							<a href="{$classifieds_url}?a=28&amp;b={$pageId}" onclick="window.open(this.href); return false;">{$classifieds_url}?a=28&amp;b={$pageId}</a>
						{/if}						
					</span>
				  </div>
				</div>
			{/if}
			
			<div class='form-group'>
			<label class='control-label col-md-4 col-sm-4 col-xs-12'>Default Template Attachment: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
				{if $attachments.1.0 && !$templates[$attachments.1.0]}
					<div class="error"><strong>Warning:</strong> Could not find the current template attached (<strong>{$attachments.1.0}</strong>) within working-with template sets.
						If you save changes, the default template will be changed to the selected template below.
					</div>
				{/if}
				{if $read_only}
					{$attachments.1.0}
				{else}
					<span style="font-weight: bold;">{include file="design/parts/templateDropdown.tpl" templateSelected=$attachments.1.0 selectName='attachments[1][0]' selectId=defaultTemplate}</span>
				{/if}
				{if $from_defaults}
					<br /><div class="error"><strong>Note:</strong>  Attachments loaded from defaults.  Verify/change template attachment(s) and save changes.</div>
				{elseif !$attachments.1.0}
					<br /><div class="error">Not currently set in the template set!  Choose a default template and save changes.</div>
				{/if}
			  </div>
			</div>
					
			<div class='header-color-primary-one'>{if $pageInfo.categoryPage && $is_ent}Category &amp; {/if}Language-Specific Template Attachments</div>
			<p class="page_note">
				You can attach templates on a language 
				{if $pageInfo.categoryPage && $is_ent}and/or category{/if} specific basis 
				by adding such attachments below.  The <strong>default template</strong> 
				attachment set above will be used if there is no template 
				attached for a specific language {if $pageInfo.categoryPage && $is_ent}or category{/if}.
			</p>

			<div class="table-responsive">
			<table class="table table-hover table-striped table-bordered">
				<thead>
					<tr class="col_hdr_top">
						<th style="color: red; width: 5px; font-size: 1.4em;"><i class="fa fa-remove"></i></th>
						<th>Language</th>
						{if $pageInfo.categoryPage && ($is_ent||$has_cat_tpls)}
							<th>Category</th>
						{/if}
						<th>Attached Template</th>
					</tr>
				</thead>
				<tbody>
					{assign var=categoryCount value=0}
					{foreach from=$attachments item=cats key=languageId}
						{if is_numeric($languageId)}
							{foreach from=$cats item=attachment key=catId}
								{if !($languageId==1&&$catId==0) && !is_array($attachment)}
									{assign var=categoryCount value=1}
									<tr class="{cycle values="row_color1,row_color2"}">
										<td style="text-align: center;"><input type="checkbox" name="delete[{$languageId}][{$catId}]" value="1" /></td>
										<td>
											{if $languages.$languageId}
												{if $languageId!=1}{$languageId} - {/if}{$languages.$languageId}
											{else}
												{$languageId} (Language not found!)
											{/if}
										</td>
										{if $pageInfo.categoryPage && ($is_ent||$has_cat_tpls)}
											<td>{$catId}{if $catNames.$catId} - {$catNames.$catId}{/if}</td>
										{/if}
										<td style="padding:5px;">
											{if $catId && !$is_ent}
												{* Only display and allow to remove *}
												<input type="hidden" name="attachments[{$languageId}][{$catId}]"  value="{$attachment|escape}" />
												{$attachment} <span style="color: red;">**Attachment not Used</span>
											{else}
												{capture assign=selectName}attachments[{$languageId}][{$catId}]{/capture}
												{include file="design/parts/templateDropdown.tpl" templateSelected=$attachment}
											{/if}
										</td>
									</tr>
								{/if}
							{/foreach}
						{/if}
					{/foreach}
					{if !$categoryCount}
						<tr>
							<td colspan="4"><div class="page_note_error">No {if $pageInfo.categoryPage&&$is_ent}Category or {/if}Language-Specific templates currently attached to this page.</div></td>
						</tr>
					{/if}
					{if !$read_only}
						<tr class="col_ftr">
							<td></td>
							<td>
								
								<select name="new[cat][languageId]" class="form-control col-md-7 col-xs-12">
									{foreach from=$languages item=lang key=langId}
										<option value="{$langId}">{if $langId!=1}{$langId} - {/if}{$lang}</option>
									{/foreach}
								</select>
								{if !$pageInfo.categoryPage||!$is_ent}
									<input type="hidden" name="new[cat][category]" value="0" />
								{/if}
							</td>
							{if $pageInfo.categoryPage && ($is_ent||$has_cat_tpls)}
								<td style="text-align:center;">
									{if $is_ent}
										{$catDropdown}
										<label>- OR -</label> <input type="text" size="3" name="new[cat][catId]" class="form-control" style="width: 110px; margin: 0 auto;" placeholder="Category ID" />
									{else}
										<input type="hidden" name="new[cat][category]" value="0" class="form-control" style="width: 110px;" />
									{/if}
								</td>
							{/if}
							<td>
								{include file="design/parts/templateDropdown.tpl" showBlankTemplate=1 selectName='new[cat][template]'}
							</td>
						</tr>
					{/if}
				</tbody>
			</table>
			</div>
			
			{if $has_cat_tpls}
				<br />
				<p class="page_note">
					<strong style="color: red;">** Note:</strong> You have at least one category-specific template attachment that
					will not be used, because category specific templates is an Enterprise only feature.
					
					You can view and remove category specific template attachments, but you will not be able to add new ones or
					edit existing category attachments.  Note that you CAN have language-specific template attachments
					on any edition.
				</p>
			{/if}
			{if $is_ent && $pageInfo.affiliatePage && $attachments.affiliate_group}
				<div class="header-color-primary-one">Group-Specific Affiliate Template Attachments</div>
				<p class="header-color-primary-one">
					Note: If a group has affiliate Privileges turned off, attachments for that
					group are never used by the system.
				</p>
				<div class="table-responsive">
				<table class="table table-hover table-striped table-bordered">
					<thead>
						<tr class="col_hdr_top">
							<th style="color: red; width: 5px; font-size: 1.4em;"><i class="fa fa-remove"></i></th>
							<th>Language</th>
							<th>Group</th>
							<th>Attached Template</th>
						</tr>
					</thead>
					<tbody>
						{assign var=groupCount value=0}
						{foreach from=$attachments.affiliate_group item=groups key=languageId}
							{foreach from=$groups item=attachment key=groupId}
								{if $languages.$languageId && $groupNames.$groupId}
									{assign var=groupCount value=1}
									<tr class="{cycle values="row_color1,row_color2"}">
										<td style="text-align: center;"><input type="checkbox" name="delete[affiliate_group][{$languageId}][{$groupId}]" value="1" /></td>
										<td>
											{if $languageId!=1}{$languageId} - {/if}{$languages.$languageId}
										</td>
										<td>{$groupId} - {$groupNames.$groupId}</td>
										<td>
											{capture assign=selectName}attachments[affiliate_group][{$languageId}][{$groupId}]{/capture}
											{include file="design/parts/templateDropdown.tpl" templateSelected=$attachment}
										</td>
									</tr>
								{/if}
							{/foreach}
						{/foreach}
						{if !$categoryCount}
							<tr>
								<td colspan="4"><div class="page_note_error">No group specific templates currently attached to this page.</div></td>
							</tr>
						{/if}
					</tbody>
				</table>
				</div>
			{/if}
			{if $pageInfo.extraPage}
				<div class="header-color-primary-one">Extra Page {$pageInfo.page_id-134} {ldelim}body_html} Attachments</div>
				<div class="table-responsive">
				<table class="table table-hover table-striped table-bordered">
					<thead>
						<tr class="col_hdr_top">
							<th style="color: red; width: 5px; font-size: 1.4em;"><i class="fa fa-remove"></i></th>
							<th>Language</th>
							<th>Attached Template</th>
						</tr>
					</thead>
					<tbody>
						{assign var=extraCount value=0}
						{foreach from=$attachments.extra_page_main_body item=row key=languageId}
							{if $languages.$languageId && $row.0}
								{assign var=extraCount value=1}
								<tr class="{cycle values="row_color1,row_color2"}">
									<td style="text-align: center;"><input type="checkbox" name="delete[extra_page_main_body][{$languageId}]" value="1" /></td>
									<td>
										{if $languageId!=1}{$languageId} - {/if}{$languages.$languageId}
									</td>
									<td>
										{capture assign=selectName}attachments[extra_page_main_body][{$languageId}]{/capture}
										{include file="design/parts/templateDropdown.tpl" templateSelected=$row.0}
									</td>
								</tr>
							{/if}
						{/foreach}
						{if !$extraCount}
							<tr>
								<td colspan="4"><div class="page_note_error">No Extra Page {$pageInfo.page_id-134} {ldelim}body_html} Attachments!</div></td>
							</tr>
						{/if}
						{if !$read_only}
							<tr class="col_ftr">
								<td></td>
								<td>
									
									<select name="new[extra][languageId]">
										{foreach from=$languages item=lang key=langId}
											<option value="{$langId}">{if $langId!=1}{$langId} - {/if}{$lang}</option>
										{/foreach}
									</select>
								</td>
								<td>
									{include file="design/parts/templateDropdown.tpl" showBlankTemplate=1 selectName='new[extra][template]'}
								</td>
							</tr>
						{/if}
					</tbody>
				</table>
				</div>
			{/if}
			
			
			{if !$read_only}
				<br /><div style="text-align: center;"><input type="submit" name="auto_save" value="Save" /></div>
			{/if}
		{if !$read_only}</form>{/if}		

	</div>
</fieldset>

		<div style='padding: 5px;'><a href="index.php?page=page_attachments" class='back_to'>
			<i class='fa fa-backward'> </i> Back to Page Attachments</a></div>
		
		{if $addonTitle}
			<div style='padding: 5px;'><a href="index.php?page=page_attachments&amp;addon={$addon|escape}" class='back_to'>
			<i class='fa fa-backward'> </i> List All {$addonTitle} Pages</a></div>
		{/if}
<div class="clearColumn"></div>