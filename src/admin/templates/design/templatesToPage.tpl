{* 16.09.0-79-gb63e5d8 *}
{$adminMsgs}
{include file="design/parts/workingOn.tpl"}

{if !$forceAddon}
	<fieldset{if $needsDefaultCopy} style="display: none;"{/if}>
		<legend>Template(s) to Page Attachments</legend>
		<div class="table-responsive template-Set-table">
			<table class="templatePageAttachments table table-hover table-striped table-bordered">
				<thead>
					<tr class="col_hdr_top">
						<th class="col_hdr">&nbsp;</th>
						<th class="col_hdr_left">Page</th>
						<th class="col_hdr_left">Default Template Attached</th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$pages item=page}
						<tr class="{cycle values='row_color1,row_color2'}">
							<td align=center><a href="index.php?page=page_attachments_edit&amp;pageId={$page.page_id}" class="btn btn-info btn-xs"><i class="fa fa-pencil"></i> Edit</a></td>
							<td>
								{if $advMode}
									{$page.page_id} - 
								{/if}
								{$page.name}
								{if $page.admin_label} - {$page.admin_label}{/if}
							</td>
							<td>
								{if $page.t_set&&$page.templates.1.0}
									{$page.templates.1.0}
									[{foreach from=$templates[$page.templates.1.0] item=tset name=subpageTSets}
										<a href="index.php?page=design_edit_file&amp;location={$tset|escape}main_page/&amp;file={$tset|escape}/main_page/{$page.templates.1.0|escape}">{$tset}</a>{if !$smarty.foreach.subpageTSets.last},{/if}
									{foreachelse}
										<strong style="color: red;">Template Not Found!</strong>
										{if $default_templates[$page.templates.1.0]}
											<a href="index.php?page=page_attachments_restore_template&amp;file={$page.templates.1.0|escape}" class="mini_button lightUpLink">Restore</a>
										{/if}
									{/foreach}]
								{else}
									<strong style="color: red;">None Attached!</strong>
									{if $page.defaults.1.0}
										<a href="index.php?page=page_attachments_apply_defaults&amp;pageId={$page.page_id}" class="lightUpLink mini_button">Apply Default (<strong>{$page.defaults.1.0}</strong>)</a>
									{/if}
								{/if}
							</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
	</fieldset>
{/if}

<div class="page-title1">Addon Pages for: <span class='color-primary-two'>{$addonPages[$forceAddon].title}</span> </div>

<fieldset{if $needsDefaultCopy} style="display: none;"{/if}>
	<legend>Template(s) to Addon Page Attachments</legend>
	<div class="table-responsive template-Set-table">
		<table class="templatePageAttachments table table-hover table-striped table-bordered">
			<thead>
				<tr class="col_hdr_top">
					<th class="col_hdr">&nbsp;</th>
					{if !$forceAddon}
						<th class="col_hdr">Addon</th>
					{/if}
					<th class="col_hdr_left">Page</th>
					<th class="col_hdr_left">Default Template Attached</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$addonPages item=info key=addon}
					{if ($forceAddon&&$forceAddon==$addon)||!$forceAddon}
						{foreach from=$info.pages item=page key=pageIndex}
							<tr class="{cycle values='row_color1,row_color2'}">
								<td align=center>
									<a href="index.php?page=page_attachments_edit&amp;pageId=addons/{$addon|escape}/{$pageIndex|escape}" class="btn btn-info btn-xs"><i class="fa fa-pencil"></i> Edit</a>
									<a href="{$classifieds_url}?a=ap&amp;addon={$addon|escape}&amp;page={$pageIndex|escape}" class="btn btn-primary btn-xs" onclick="window.open(this.href); return false;"><i class="fa fa-laptop"></i> View</a>
								</td>
								{if !$forceAddon}
									<td>{$info.title}</td>
								{/if}
								<td>
									{if $info.pages_info.$pageIndex.title}
										{$info.pages_info.$pageIndex.title}
									{else}
										{$pageIndex}
									{/if}
								</td>
								<td>
									{if $page.t_set&&$page.templates.1.0}
										{$page.templates.1.0}
										[{foreach from=$templates[$page.templates.1.0] item=tset name=subpageTSets}
											<a href="index.php?page=design_edit_file&amp;location={$tset|escape}main_page/&amp;file={$tset|escape}/main_page/{$page.templates.1.0|escape}">{$tset}</a>{if !$smarty.foreach.subpageTSets.last},{/if}
										{foreachelse}
											<strong style="color: red;">Template Not found!</strong>
											{if $default_templates[$page.templates.1.0]}
												<a href="index.php?page=page_attachments_restore_template&amp;file={$page.templates.1.0|escape}" class="mini_button lightUpLink">Restore</a>
											{/if}
										{/foreach}]
									{else}
										<strong style="color: red;">None Attached!</strong>
										{if $page.defaults.1.0}
											<a href="index.php?page=page_attachments_apply_defaults&amp;pageId=addons/{$addon|escape}/{$pageIndex|escape}" class="lightUpLink mini_button">Apply Default (<strong>{$page.defaults.1.0}</strong>)</a>
										{/if}
									{/if}
								</td>
							</tr>
						{/foreach}
					{/if}
				{foreachelse}
					<tr><td colspan="5">No addon pages found.</td></tr>
				{/foreach}
			</tbody>
		</table>
	</div>
</fieldset>
<div class="clearColumn"></div>