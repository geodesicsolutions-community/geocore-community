{* 16.09.0-79-gb63e5d8 *}

<div class="table-responsive">

<table class="fileListTable bulk_action table table-hover table-striped table-bordered" cellpadding="0" cellspacing="0" >
	<thead>
		<tr class="headings col_hdr_top">
			<th></th>
			<th style="text-align: left;">
				<a class="fileListLink" href="index.php?page=design_manage&amp;location={$location|escape}&amp;sortBy=filename&amp;sortOrder={if $sortOrder==up && $sortBy==filename}down{else}up{/if}">
					Name
				</a>
				{if $sortBy==filename}
					<img src="admin_images/admin_arrow_{if $sortOrder==down}down{else}up{/if}.gif" alt="Sorting by Filename" />
				{/if}
			</th>
			{if $advMode && $locationInfo.type && $locationInfo.type=='main_page'}
				<th title="# of Attachments">Attach</th>
			{/if}
			{if $advMode}
				<th style="text-align: left;">
					<a class="fileListLink" href="index.php?page=design_manage&amp;location={$location|escape}&amp;sortBy=size&amp;sortOrder={if $sortOrder==up && $sortBy==size}down{else}up{/if}">
						Size
					</a>
					{if $sortBy==size}
						<img src="admin_images/admin_arrow_{if $sortOrder==down}down{else}up{/if}.gif" alt="Sorting by Size" />
					{/if}
				</th>
			{/if}
			<th style="text-align: left;">
				<a class="fileListLink" href="index.php?page=design_manage&amp;location={$location|escape}&amp;sortBy=type&amp;sortOrder={if $sortOrder==up && $sortBy==type}down{else}up{/if}">
					Type
				</a>
				{if $sortBy==type}
					<img src="admin_images/admin_arrow_{if $sortOrder==down}down{else}up{/if}.gif" alt="Sorting by File Type" />
				{/if}
			</th>
			<th style="text-align: left;">
				<a class="fileListLink" href="index.php?page=design_manage&amp;location={$location|escape}&amp;sortBy=modified&amp;sortOrder={if $sortOrder==up && $sortBy==modified}down{else}up{/if}">
					Last Modified
				</a>
				{if $sortBy==modified}
					<img src="admin_images/admin_arrow_{if $sortOrder==down}down{else}up{/if}.gif" alt="Sorting by Last Modified" />
				{/if}
			</th>
			<th>
				Permissions
			</th>
		</tr>
	</thead>
	<tbody>
		{foreach from=$files item=file key=i}
			{cycle values="row_color1,row_color2" assign=row_color}
			<tr id="fileListRow_{$i}" class="{$row_color}{$file.cssClasses} fileEntry">
				<td class="checkboxColumn" title="{$file.title}">
					<input name="selectedFiles[{$i}]" value="{$i}" type="checkbox" id="fileListRow_{$i}_checkbox" class="fileListCheckbox" />
				</td>
				<td class="fileColumn" title="{$file.title}">
					<div>
						{if $file.is_dir}<a class="fileListLink" href="index.php?page=design_manage&amp;location={$location|escape}{$file.filename|escape}/">{/if}
							<img src="admin_images/icons/{$file.icon}" alt="" /> {$file.filename}
						{if $file.is_dir}</a>{/if}
					</div>
				</td>
				{if $advMode && $locationInfo.type && $locationInfo.type=='main_page'}
					<td class="attachmentsColumn" title="{$file.title}">
						<div>
							{if $file.attachments}
								{$file.attachments.modules+$file.attachments.sub_pages+$file.attachments.addons}
							{else}
								-
							{/if}
						</div>	
					</td>
				{/if}
				{if $advMode}
					<td class="sizeColumn" title="{$file.title}">
						<div>
						{if $file.is_dir}
							-
						{else}
							{if $file.size_mb}
								{$file.size_mb} MB
							{elseif $file.size_kb}
								{$file.size_kb} KB
							{else}
								{$file.size} Bytes
							{/if}
						{/if}
						</div>
					</td>
				{/if}
				<td class="typeColumn" title="{$file.title}">{$file.type}</td>
				<td class="modifyColumn" title="{$file.title}">
					<div>
					{if $file.modified>$yesterday}
						Today - {$file.modified|date_format:"%H:%M:%S"}
					{else}
						{$file.modified|date_format:"%B %e, %Y - %H:%M:%S"}
					{/if}
					</div>
				</td>
				<td class="permissionsColumn" title="read | {if $file.readonly}NO {/if}write">
					r{if !$file.readonly}w{/if}
				</td>
			</tr>
		{foreachelse}
			<tr>
				<td colspan="6" class="emptyFolder">Empty Folder</td>
			</tr>
		{/foreach}
	</tbody>
</table>

</div>

{if !$locationInfo.t_set}
	{capture assign=tsetListHtml}
		<h2>Go to <span class="text_blue">main_page</span> in:</h2>
		<ul>
			{foreach from=$files item=file}
				{if $file.is_t_set && $file.hasMainpage && !$file.readonly}
					{capture assign="tsetFilenameLabel"}{$file.filename} Template Set{/capture}
					<li>
						<a href="index.php?page=design_manage&location={$file.filename}/main_page/" class="fileListLink folderLink"
							title="{$file.filename|escape}">
							<img src="admin_images/icons/folder-closed.png" alt="folder" />
							<span class="text_green">
								{$file.filename}
							</span>
						</a>
					</li>
				{/if}
			{/foreach}
		</ul>
	{/capture}
{/if}

{* Put the total size somewhere that JS can get to it... *}
<script type="text/javascript">
//<![CDATA[
//JS to set the data for this set of files

	{if $isAjax}
		geoDesignManage.setListData({ldelim}
	{else}
		var fileListData = {ldelim}
	{/if}
		{if $locationInfo.t_set}
			system_exists : {$system_exists},
			addon_exists : {$addon_exists},
			module_exists : {$module_exists},
			external_exists : {$external_exists},
			main_page_exists : {$main_page_exists},
		{else}
			tsetListJumpHtml : '{$tsetListHtml|escape_js}',
		{/if}
		files : [
			{foreach from=$files item=file key=index name=fileLoop}
				{ldelim}
					filename : '{$file.filename|escape_js}',
					fileType : '{$file.type|escape_js}',
					readonly : {$file.readonly},
					is_dir : {$file.is_dir},
					is_t_set : {$file.is_t_set},
					hasMainpage : {$file.hasMainpage}
				}{if !$smarty.foreach.fileLoop.last},{/if}
			{/foreach}
		],
		locationParts : {ldelim}
			{foreach from=$locationParts item=part key=index name=locationLoop}
				{$index} : {ldelim}
					location : '{$part.location|escape_js}',
					title : '{$part.title|escape_js}',
					fullPath : '{$part.fullPath|escape_js}',
					showLink : {$part.showLink},
					endPath : {$part.endPath}
				}{if !$smarty.foreach.locationLoop.last},{/if}
			{/foreach}
		},
		canCreateFolder : {$canCreateFolder},
		canCreateFile : {$canCreateFile},
		canUploadFile : {$canUploadFile},
		canEditSystemTemplates : {$canEditSystemTemplates},
		totalSize : '{$totalSize|escape_js}',
		fileCount : {$fileCount},
		folderCount : {$folderCount},
		currentLocation : '{$location|escape_js}',
		t_set : '{if $locationInfo.t_set}{$locationInfo.t_set|escape_js}{else}n/a{/if}',
		t_type : '{$locationInfo.type|escape_js}',
		t_localFile : '{$locationInfo.localFile|escape_js}',
		viewing : '{$viewing|escape_js}',
		is_writable : {$is_writable},
		wReason : '{$wReason|escape_js}'
	}{if $isAjax}){/if};
	
//]]>
</script>

