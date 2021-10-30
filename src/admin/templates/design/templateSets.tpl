{* 16.09.0-79-gb63e5d8 *}
{$adminMsgs}
{include file='design/parts/designModeBox.tpl'}
<form action="index.php?page=design_sets" method="post">
	<fieldset>
		<legend>Template Sets</legend>
		<div class="table-responsive template-Set-table">
			<table class="table table-hover table-striped table-bordered">
				<thead>
					<tr class="col_hdr_top">
						<th class="col_hdr" style="width: 50px;">Active</th>
						<th class="col_hdr" style="width: 80px;">Admin<br />Editing</th>
						{if ($advMode && $t_sets_used|@count > 2)||(!$advMode && $t_sets_used|@count>1)}
							<th style="width: 100px;">Template<br />Seek Order</th>
						{/if}
						<th class="col_hdr">Template Set Name</th>
						<th class="col_hdr" style="width: 80px;">Language</th>
						<th class="col_hdr" style="width: 80px;">Device</th>
						<th class="col_hdr" colspan="5">&nbsp;</th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$t_sets_used item="t_set_used" name="sets_used"}
						{if $t_set_used!=='default'||$canEditDefault||$advMode}
							{assign var=tsetDisplayed value=1}
							<tr class="{cycle values="row_color1,row_color2"}">
								<td style="text-align: center;">
									<input type="checkbox" name="activeSets[{$t_set_used}]" value="1" checked="checked"{if $t_set_used=='default'} disabled="disabled"{else} class="tset_active"{/if} />
								</td>
								<td style="text-align: center;">
									<input type="{if $advMode}checkbox{else}radio{/if}" name="workWith[]" {if in_array($t_set_used,$workWithList)}checked="checked"{/if} value="{$t_set_used}" />
								</td>
								{if ($advMode && $t_sets_used|@count > 2)||(!$advMode && $t_sets_used|@count>1)}
									<td class="{$row_color}" style="text-align: center;">
										<button name="move[{$t_set_used|escape}]" value="up" alt="Move up" title="Move up"{if $smarty.foreach.sets_used.first || $t_set_used=='default'} style="visibility: hidden;"{/if}>
											<i class="fa fa-chevron-up"></i>
										</button>
										<button name="move[{$t_set_used|escape}]" value="down" alt="Move down" title="Move down"{if $smarty.foreach.sets_used.last||($advMode&&($smarty.foreach.sets_used.total-2)<$smarty.foreach.sets_used.iteration)} style="visibility: hidden;"{/if}>
											<i class="fa fa-chevron-down"></i>
										</button>
									</td>
								{/if}
								<td>{$t_set_used}</td>
								<td class="center">
									{if $t_set_used=='default'}
										Any Language
									{else}
										<select name="language[{$t_set_used|escape}]">
											<option value="0">Any Language</option>
											{foreach $languages as $language_id => $language}
												<option value="{$language_id}"{if $t_sets_meta.$t_set_used.language_id==$language_id} selected="selected"{/if}>{$language}</option>
											{/foreach}
										</select>
									{/if}
								</td>
								<td class="center">
									{if $t_set_used=='default'}
										Any Device
									{else}
										<select name="device[{$t_set_used|escape}]">
											<option value="all">Any Device</option>
											<option value="mobile"{if $t_sets_meta.$t_set_used.device=='mobile'} selected="selected"{/if}>Mobile/Tablet Only</option>
											<option value="desktop"{if $t_sets_meta.$t_set_used.device=='desktop'} selected="selected"{/if}>Desktop Only</option>
										</select>
									{/if}
								</td>
								<td class="center" style="width: 80px;">
									<a href="index.php?page=design_manage&amp;forceEditTset={$t_set_used}&amp;forceChange=1&amp;location={$t_set_used}/main_page/" class="btn btn-success btn-xs">
										<i class="fa fa-folder-open"></i> Edit Templates
									</a>
								</td>
								<td class="center" style="width: 80px;">
									<a href="{if $t_set_used=='default'}index.php?page=design_sets_create_main{else}index.php?page=design_sets_copy&amp;t_set={$t_set_used}{/if}" class="btn btn-info btn-xs lightUpLink"><i class="fa fa-copy"></i> Copy</a>
								</td>
								<td class="center" style="width: 80px;">
									<a href="index.php?page=design_sets_download&amp;t_set={$t_set_used}" class="btn btn-primary btn-xs lightUpLink"><i class="fa fa-download"></i> Download</a>
								</td>
								<td class="center" style="width: 80px;">
									<a href="index.php?page=design_sets_scan&amp;t_set={$t_set_used}" class="btn btn-primary btn-xs lightUpLink"><i class="fa fa-sliders"></i> Re-Scan Attachments</a>
								</td>
								<td class="center" style="width: 10px;">
									{if $importTextTsets.$t_set_used}
										<a href="index.php?page=design_sets_import_text&amp;t_set={$t_set_used}" class="btn btn-primary btn-xs lightUpLink">
											<i class="fa fa-language"></i> Import Text Changes
										</a>
									{/if}
								</td>
							</tr>
						{/if}
					{/foreach}
					
					{foreach $t_sets as $t_set}
						{if !in_array($t_set,$t_sets_used)}
							{if !$inactiveTsetDisplayed}
								{assign var=inactiveTsetDisplayed value=1}
								<tr class="template_disabled">
									<td colspan="11">Inactive Template Sets</td>
								</tr>
							{/if}
							<tr class="{cycle values="row_color1,row_color2"} inactive-Template-sets">
								<td style="text-align: center;">
									<input type="checkbox" name="activeSets[{$t_set}]" value="1" class="tset_active" />
								</td>
								<td style="text-align: center;"><input type="{if $advMode}checkbox{else}radio{/if}" name="workWith[]" {if in_array($t_set,$workWithList)}checked="checked"{/if} value="{$t_set}" /></td>
								{if ($advMode && $t_sets_used|@count > 2)||(!$advMode && $t_sets_used|@count>1)}<td class="{$row_color}"></td>{/if}
								<td>{$t_set}</td>
								<td>
									<select name="language[{$t_set|escape}]" style="display: none;">
										<option value="0">Any Language</option>
										{foreach $languages as $language_id => $language}
											<option value="{$language_id}"{if $t_sets_meta.$t_set.language_id==$language_id} selected="selected"{/if}>{$language}</option>
										{/foreach}
									</select>
								</td>
								<td>
									<select name="device[{$t_set|escape}]" style="display: none;">
										<option value="all">Any Device</option>
										<option value="mobile"{if $t_sets_meta.$t_set.device=='mobile'} selected="selected"{/if}>Mobile/Tablet Only</option>
										<option value="desktop"{if $t_sets_meta.$t_set.device=='desktop'} selected="selected"{/if}>Desktop Only</option>
									</select>
								</td>
								<td class="center" style="width: 80px;">
									<a href="index.php?page=design_manage&amp;forceEditTset={$t_set}&amp;forceChange=1&amp;location={$t_set}/main_page/" class="btn btn-default btn-xs" style="color: #F7AD02;">
										<i class="fa fa-folder-open"></i> Edit Templates
									</a>
								</td>
								<td style="text-align: center;">
									<a href="index.php?page=design_sets_copy&amp;t_set={$t_set}" class="btn btn-default btn-xs lightUpLink" style="color: #F7AD02;"><i class="fa fa-copy"></i> Copy</a>
								</td>
								<td style="text-align: center;">
									<a href="index.php?page=design_sets_download&amp;t_set={$t_set}" class="btn btn-default btn-xs lightUpLink" style="color: #F7AD02;"><i class="fa fa-download"></i> Download</a>
								</td>
								<td style="text-align: center;">
									<a href="index.php?page=design_sets_scan&amp;t_set={$t_set}" class="btn btn-default btn-xs lightUpLink" style="color: #F7AD02;"><i class="fa fa-sliders"></i> Re-Scan Attachments</a>
								</td>
								<td>
									<a href="index.php?page=design_sets_delete&amp;t_set={$t_set}" class="btn btn-danger btn-xs lightUpLink"><i class="fa fa-trash-o"></i> Delete</a>
								</td>
							</tr>
						{/if}
					{/foreach}
					{if !$tsetDisplayed && !$inactiveTsetDisplayed}
						<tr>
							<td colspan="7" class="center">
								No Template Sets Found!
							</td>
						</tr>
					{/if}
				</tbody>
			</table>
			<br />
			<div style="text-align: center;">
				<input type="hidden" name="auto_save" value="1" />
				<input type="submit" name="auto_save" value="Save Settings" />
				<br /><br />
				<a href='index.php?page=design_sets_upload' class='lightUpLink btn btn-primary btn-xs'><i class="fa fa-upload"></i> Upload Template Set</a>
				<br /><br />
				<a href="index.php?page=design_sets_copy&amp;t_set=merged" class="btn btn-primary btn-xs lightUpLink"><i class="fa fa-chain"></i> Merge Sets Together</a>
				
				{if $showExport}
					<br /><br />
					<a class="lightUpLink btn btn-primary btn-xs" href="index.php?page=design_sets_export"><i class="fa fa-share-square-o"></i> Export Pre-5.0 Design to Template Set</a>
				{/if}
				<br /><br />
				<a class="lightUpLink btn btn-primary btn-xs" href="index.php?page=design_sets_create_main"><i class="fa fa-magic"></i> Create Main Template Set</a>
				<div class="clearColumn"></div>
			</div>
		</div>
	</fieldset>
</form>


