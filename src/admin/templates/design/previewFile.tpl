{* 6.0.7-3-gce41f93 *}

<div class="fileInfoName" title="{$localFile|escape}">
	{$localFile}
</div>

<div class="fileInfoPreviewType" title="File Preview - {$filePreviewType|escape}">
	{$filePreviewType}
</div>
<div class="designPreviewWindow" id="mainPreviewWindow" title="Click for larger preview">
	{* Do not insert contents right now, let it be inserted by JS so there's
		no chance of crummy templates "escaping" *}
</div>

<div style="display: none;">
	{* This one is to store the big box *}
	<div id="mainPreviewWindowLarge">
		<div class="closeBoxX"></div>
		<div class="lightUpTitle" id="newConfirmTitle">Full view of {$localFile}</div>
		<br /><br />
		
		<strong>{$filePreviewType}:</strong>  
		{if $canEdit}
			&nbsp; &nbsp; <a href="index.php?page=design_edit_file&amp;location={$location|escape}&amp;file={$file|escape}" class="mini_button">Edit</a>
		{elseif $canView}
			&nbsp; &nbsp; <a href="index.php?page=design_edit_file&amp;location={$location|escape}&amp;file={$file|escape}" class="mini_button">View More</a>
		{/if}
		<div id="mainPreviewWindowLargeContents"></div>
	</div>
</div>
<div class="designPreviewFileInfo">
	
	<div class="fileInfoEntry">
		<strong>Permissions:</strong>
		<span>{$access}</span>
	</div>
	<div class="fileInfoEntry">
		<strong>Last Modified:</strong>
		<span>
			{if $stats.mtime>$yesterday}
				Today - {$stats.mtime|date_format:"%H:%M:%S"}
			{else}
				{$stats.mtime|date_format:"%B %e, %Y - %H:%M:%S"}
			{/if}
		</span>
	</div>
	<div class="fileInfoEntry">
		<strong>Size:</strong>
		<span>{$size}</span>
	</div>
	<div class="fileInfoEntry">
		<strong>Type:</strong>
		<span>{$fileType}</span>
	</div>
	{if $attachments}
		{if $attachments.modules}
			<div class="fileInfoEntry">
				<strong>Attached Modules:</strong><br />
				{foreach from=$attachments.modules item=module key=moduleId}
					<a href="index.php?page=modules_page&b={$moduleId}">{$moduleId} - {$pageNames.$moduleId}</a>
					<br />
				{/foreach}
			</div>
		{/if}
		{if $attachments.addons}
			<div class="fileInfoEntry">
				<strong>Attached Addon Tags:</strong><br />
				{foreach from=$attachments.addons key="author" item="author_info"}
					{foreach from=$author_info key="addon" item="addon_data"}
						{foreach from=$addon_data item="tag"}
							{$addon} - {$tag}<br />
						{/foreach}
					{/foreach}
				{/foreach}
			</div>
		{/if}
		{if $attachments.sub_pages}
			<div class="fileInfoEntry">
				<strong>Sub-Templates:</strong><br />
				{foreach from=$attachments.sub_pages item=sub_page}
					{$sub_page.name}<br />
				{/foreach}
			</div>
		{/if}
	{/if}
</div>