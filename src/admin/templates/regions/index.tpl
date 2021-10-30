{* 16.09.0-79-gb63e5d8 *}

{$admin_msgs}

<fieldset>
	<legend>Regions</legend>
	<div>
		<br />
		
		{if $parent}
			<div class="breadcrumbBorder">
				<ul id="breadcrumb">
					{* <li class="current">Viewing Regions For:</li> *}
					<li{if !$parent} class="current2"{/if}><a href="index.php?page=regions">Top</a></li>
					{foreach $parents as $p}
						<li{if $p@last} class="current2"{/if}><a href="index.php?page=regions&amp;parent={$p.id}">{$p.name}</a></li>
					{/foreach}
				</ul>
			</div>
			<br />
		{/if}
		
		{include file='regions/levelInfo.tpl'}
		<br />
		
		<form action="index.php?parent={$parent}&amp;p={$page}" method="post" id="massForm">
			<div class="table-responsive manageRegions">
			<table class="table table-hover table-striped table-bordered">
				<thead>
					<tr class="col_hdr_top">
						<th colspan="2">Region (ID#)</th>
						<th style="width: 60px;">Listings</th>
						<th style="width: 60px;">Enabled?</th>
						{if $level.region_type=='country'||$level.region_type=='state/province'}
							<th style="width: 50px;"><span title="{$level.type_label} Abbreviation">Abbr.</span></th>
						{/if}
						<th>Unique Name</th>
						<th>Tax</th>
						<th>Display Order</th>
						<th style="width: 90px;"></th>
					</tr>
					<tr class="col_ftr">
						<th class="center" style="width: 21px;"><input type="checkbox" id="checkAllRegions" /></th>
						<td colspan="9" style="color:#394D5F;">With Selected:
							<a href="#" class="btn btn-dark btn-xs massEditButton"><i class="fa fa-edit"></i> Mass Edit</a>
							<a href="#" class="btn btn-dark btn-xs moveButton"><i class="fa fa-arrows"></i> Move</a>
							&nbsp; &nbsp; &nbsp; &nbsp;
							<a href="#" class="btn btn-danger btn-xs massDeleteButton"><i class="fa fa-trash-o"></i> Delete</a>
						</td>
					</tr>
				</thead>
				<tfoot>
					<tr class="col_ftr">
						<td colspan="9" style="padding-left: 6px; color:#394D5F;"><i class="fa fa-sign-out" style="font-size: 1.4em;"></i> With Selected:
							<a href="#" class="btn btn-dark btn-xs massEditButton"><i class="fa fa-edit"></i> Mass Edit</a>
							<a href="#" class="btn btn-dark btn-xs moveButton"><i class="fa fa-arrows"></i> Move</a>
							&nbsp; &nbsp; &nbsp; &nbsp;
							<a href="#" class="btn btn-danger btn-xs massDeleteButton"><i class="fa fa-trash-o"></i> Delete</a>
							{if $pagination}
								<br />{$pagination}
							{/if}
						</td>
					</tr>
					
				</tfoot>
				<tbody>
					<tr class="{cycle values='row_color1,row_color2'}">
						<td class="center"></td>
						<td>
							<a href="index.php?page=regions">Top</a>
						</td>
						<td class="center"></td>
						<td class="center"></td>
						{if $level.region_type=='country'||$level.region_type=='state/province'}
							<td class="center"></td>
						{/if}
						<td></td>
						<td class="center"></td>
						<td class="center"></td>
						<td class="center">
							{if $parent}<a href="index.php?page=regions" class="btn btn-success btn-xs" style="margin:0; font-size:1.4em;"><i class="fa fa-angle-double-up"></i> Top Regions</a>{/if}
						</td>
					</tr>
					{if $parent}
						{foreach $parents as $region}
							<tr class="{cycle values='row_color1,row_color2'}" id="row_{$region.id}">
								<td class="center"></td>
								<td{if $region.enabled=='no'} class="disabled"{/if}>
									<span style="font-family: monospace; color: #777777;">{'&nbsp;&nbsp;&nbsp;&nbsp;'|str_repeat:($region.level-1)}|--</span> <a href="index.php?page=regions&amp;parent={$region.id}">{$region.name} ({$region.id})</a>
									<div class="disabledSection"{if $region.enabled=='yes'} style="display: none;"{/if}>
										<strong style="color: red;">Warning:</strong> Parent Region Disabled!  Sub-Regions below are not currently usable on the site.
									</div>
								</td>
								<td class="center">{$region.listing_count}</td>
								<td class="center">
									{include file='regions/enabled.tpl'}
								</td>
								{if $level.region_type=='country'||$level.region_type=='state/province'}
									<td class="center">{$region.billing_abbreviation}</td>
								{/if}
								<td>{$region.unique_name}</td>
								<td class="center">
									{if $region.tax_percent>0}{$region.tax_percent}%{/if}
									{if $region.tax_percent>0 && $region.tax_flat>0} + {/if}
									{if $region.tax_flat>0}{$region.tax_flat|displayPrice}{/if}
									{if $region.tax_percent==0 && $region.tax_flat==0}-{/if}
								</td>
								<td class="center">{$region.display_order}</td>
								<td class="center" style="white-space: nowrap;">
									<a href="index.php?page=regions&amp;parent={$region.id}" class="btn btn-success btn-xs" style="margin:0;"><i class="fa fa-folder-open"></i> Enter</a>
									<a href="#" class="btn btn-info btn-xs" style="visibility: hidden; margin:0;"><i class="fa fa-pencil"style="margin:0;"></i> Edit</a>
								</td>
							</tr>
						{/foreach}
					{/if}
					{if !$regions}
						<tr><td colspan="10"><p class="page_note_error">No regions were found at this level!  You can create some new regions at this level using the "Add Region" or "Bulk Add" buttons at the bottom...</p></td></tr>
					{else}
						{foreach $regions as $region}
							<tr class="{cycle values='row_color1,row_color2'}" id="row_{$region.id}">
								<td class="center"><input type="checkbox" name="regions[]" class="regionCheckbox" value="{$region.id}" /></td>
								<td{if $region.enabled=='no'} class="disabled"{/if}>
									<span style="font-family: monospace; color: #777777;">{'&nbsp;&nbsp;&nbsp;&nbsp;'|str_repeat:($region.level-1)}|--</span> <a href="index.php?page=regions&amp;parent={$region.id}">{$region.name|fromDB} ({$region.id})</a>
								</td>
								<td class="center">{$region.listing_count}</td>
								<td class="center">
									{include file='regions/enabled.tpl'}
								</td>
								{if $level.region_type=='country'||$level.region_type=='state/province'}
									<td class="center">{$region.billing_abbreviation}</td>
								{/if}
								<td>{$region.unique_name}</td>
								<td class="center">
									{if $region.tax_percent>0}{$region.tax_percent}%{/if}
									{if $region.tax_percent>0 && $region.tax_flat>0} + {/if}
									{if $region.tax_flat>0}{$region.tax_flat|displayPrice}{/if}
									{if $region.tax_percent==0 && $region.tax_flat==0}-{/if}
								</td>
								<td class="center">{$region.display_order}</td>
								<td class="center" style="white-space: nowrap;">
									<a href="index.php?page=regions&amp;parent={$region.id}" class="btn btn-success btn-xs" style="margin:0;"><i class="fa fa-folder-open"></i> Enter</a>
									<a href="index.php?page=region_edit&amp;region={$region.id}&amp;p={$page}" class="btn btn-info btn-xs lightUpLink" style="margin:0;"><i class="fa fa-pencil"></i> Edit</a>
								</td>
							</tr>
						{/foreach}
					{/if}
				</tbody>
			</table>
			</div>
		</form>
		<br />
		<div class="center">
			<a href="index.php?page=region_create&amp;parent={$parent}" class="btn btn-success btn-xs lightUpLink"><i class="fa fa-plus-circle"></i> Add Region</a>
			<a href="index.php?page=region_create_bulk&amp;parent={$parent}" class="btn btn-success btn-xs lightUpLink"><i class="fa fa-truck"></i> Bulk Add</a>
		</div>
	</div>
</fieldset>
<div class="clearColumn"></div>