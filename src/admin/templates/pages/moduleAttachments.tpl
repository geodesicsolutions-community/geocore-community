{* 16.09.0-79-gb63e5d8 *}

<fieldset>
	<legend>Module Attachments</legend>
	<div class="table-responsive">
		<p class="page_note">The following templates currently have this module attached to them.</p>
		{if $attachments}
			<table cellpadding="0" cellspacing="0" class="table table-hover table-striped table-bordered">
				<thead>
				<tr class="col_hdr_top">
					<td style="text-align: left;">Set In Template Set</td>
					<td style="text-align: left;">Template File in main_page/</td>
					<td style="width: 100px;"></td>
				</tr>
				</thead>
				{foreach from=$attachments key=tset item=tpls}
					{foreach from=$tpls key=tpl item=tplExists}
						<tr class="{cycle values='row_color1,row_color2'}">
							<td style="padding: 10px; padding-right: 50px;">{$tset}</td>
							<td style="padding: 10px; padding-right: 50px;">{$tpl}</td>
							<td class="center" style="padding: 5px;">
								{if $tplExists}
									<a href="index.php?page=design_edit_file&amp;file={$tset|escape}/main_page/{$tpl}" class="btn btn-info btn-xs" style='margin:0;'><i class="fa fa-paint-brush"></i> Edit Template</a>
								{else}
									Template not found in {$tset}.
								{/if}
							</td>
						</tr>
					{/foreach}
				{/foreach}
			</table>
		{else}
			<strong>Not Attached to any templates!</strong>  To start using this module, insert the module's tag into a template.
		{/if}
	</div>
</fieldset>