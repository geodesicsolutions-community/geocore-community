{* 7.1beta1-1215-g6a6ef13 *}


<div class="closeBoxX"></div>

{if $adminMsgs}
	{$adminMsgs}
{else}
	<div class="lightUpTitle" id="editConfirmTitle">
		{if $defaults=='make_copy'}Make a Copy of{else}Rename or Move{/if} {if $is_dir}Folder &amp; Contents{else}File{/if}:
	</div>
	<form style="display:block; margin: 15px;" action="index.php?page=design_rename_file&amp;defaults={$defaults|escape}&amp;location={$location|escape}" method="post">
		<input type="hidden" name="file" value="{$file|escape}" />
		<input type="hidden" name="move_or_copy" value="{$defaults|escape}" />
		<input type="hidden" name="auto_save" value="1" />
		<strong>From: </strong>{$t_set}/{$tType}/<strong>{$tLocalFile|escape}</strong><br />
		<strong>To:</strong> {$t_set}/{$tType}
		<select name="toDir">
			<option value='.'>/</option>
			{foreach from=$toDirs item=dir}
				<option value="{$dir|escape}"{if $dir==$selectedDir} selected="selected"{/if}>/{$dir|escape}/</option>
			{/foreach}
		</select>
		<input type="text" name="localNewName" value="{$localFile|escape}" size="20" />
		<br />
		<p style="width: 350px;">
			{if $defaults=='make_copy'}
				<strong style="color: red;">Note: </strong> This will automatically copy any applicable modules to template attachments.
			{else}
				<strong style="color: red;">Note: </strong> This will also rename/move any applicable modules to template attachment files, and adjust any template to page attachments.
			{/if}
		</p>
		<div style="float: right; margin-top: 5px;">
			<input type="submit" class="mini_button" value="{if $defaults=='make_copy'}Make Copy{else}Move/Rename{/if}" />
			<input type="button" class="closeLightUpBox mini_cancel" value="Cancel" />
		</div>
	</form>
{/if}
