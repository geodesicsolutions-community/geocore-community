{* 6.0.7-3-gce41f93 *}


<div class="closeBoxX"></div>

<div class="lightUpTitle" id="newConfirmTitle">Create New {if $location || $errorMsgs}Folder{else}Template Set{/if}</div>
{if $errorMsgs}
	<div class="errorBoxMsgs">
		<br />
		<strong>Unable to perform action here:</strong><br />
		{$errorMsgs}
		<br /><br />
		<div class="templateToolButtons">
			<input type="button" class="closeLightUpBox mini_button" value="Ok" />
		</div>
		<div class="clearColumn"></div>
	</div>
{else}
	
	<form style="display:block; margin: 15px;" action="index.php?page=design_new_folder&amp;location={$location|escape}" method="post">
		<input type="hidden" name="auto_save" value="1" />
		{if $location}
			<strong>Create folder in:</strong> {$location|escape}
		{else}
			Create new Template Set
		{/if}
		<br />
		<label>
			<strong>Name:</strong> 
			{if $locationInfo.t_set && !$locationInfo.type}
				<select name="name" style="width: 150px;">
					<option value="main_page">main_page/ -- main page templates folder</option>
					<option value="external">external/ -- media file folder</option>
					<option value="system">system/ -- Dynamic system templates folder</option>
					<option value="module">module/ -- Dynamic module content templates folder</option>
					<option value="addon">addon/ -- Addon template replacements folder</option>
				</select>
			{else}
				<input type="text" size="20" name="name" />
			{/if}
		</label>
		<br />
		<div class="templateToolButtons">
			<input type="submit" class="mini_button" value="Create Folder" />
			<input type="button" class="closeLightUpBox mini_cancel" value="Cancel" />
		</div>
	</form>
{/if}
