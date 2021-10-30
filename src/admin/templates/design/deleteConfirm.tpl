{* 6.0.7-3-gce41f93 *}

{$adminMsgs}

<div class="closeBoxX"></div>
<div class="lightUpTitle" id="newConfirmTitle">Delete Files</div>

<div>
	Are you sure?  This will <strong style="color: red;">delete</strong> the following files:
	<ul>
		{foreach from=$fileList item=subList}
			{foreach from=$subList item=subSubList key=tset}
				{foreach from=$subSubList key=type item=files}
					{foreach from=$files key=k item=file}
						{if $k=='attachments'}
							{foreach from=$file item=attachedFile}
								<li>{$geo_templatesDir}{$tset}/{$type}/<strong>attachments</strong>/{$attachedFile}</li>
							{/foreach}
						{else}
							<li>{$geo_templatesDir}{$tset}/{$type}/{$file}</li>
						{/if}
					{/foreach}
				{/foreach}
			{/foreach}
		{/foreach}
	</ul>
	<br />
	{if $attachmentsList.modules_to_template}
		The following <strong>Sub-Template to Template attachments</strong> will be adjusted:
		<br />
		(Template contents not affected, just the attachments)
		<ul>
			{foreach from=$attachmentsList.modules_to_template item=page}
				<li>{$page}</li>
			{/foreach}
		</ul>
	{/if}
	{if $attachmentsList.templates_to_page}
		The following <strong>Templates to Page attachments</strong> will be adjusted:
		<br />
		<ul>
			{foreach from=$attachmentsList.templates_to_page item=page}
				<li>{$page}</li>
			{/foreach}
		</ul>
	{/if}
	<div class="templateToolButtons">
		<form action="index.php?page=design_delete_files&location={$location|escape}" method="post">
			{foreach from=$deleteFile item=file}
				<input type="hidden" name="deleteFiles[]" value="{$file}" />
			{/foreach}
			<input type="submit" class="mini_button" name="auto_save" value="Delete Files{if $attachmentsList} &amp; Update Attachments{/if}" />
			<input type="button" class="closeLightUpBox mini_cancel" value="Cancel" />
		</form>
	</div>
</div>
