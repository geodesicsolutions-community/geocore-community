{* 7.6.3-149-g881827a *}
{if $canReply}<form action="{$formTarget}" method="post">{/if}
	<div class="content_box">
		<h1 class="title my_account">{$messages.625}</h1>
		<h3 class="subtitle">{$messages.410}</h3>
		<p class="page_instructions">{$messages.411}</p>
	</div>

	<div class="content_box">
		<h2 class="title">{$messages.502049}</h2>
		<div class="row_even">
			<label class="field_label">{$messages.412}</label>
			{if $sender_id}<a href="{$classifieds_file_name}?a=6&amp;b={$sender_id}">{/if}
			{$sender}
			{if $sender_id}</a>{/if}
		</div>
	
		<div class="row_odd">
			<label class="field_label">{$messages.413}</label>
			{$dateSent}
		</div>
	
		<div class="row_even">
			<label class="field_label label-fix-rwd">{$messages.1186}</label>
			<span class="text_highlight value-fix-rwd">{$listingTitle}</span>
		</div>
	</div>

	<div class="content_box">
		<h3 class="subtitle">{$messages.414}</h3>
		<div class="box_pad">
			{$message}
		</div>
	</div>
	{if $canReply}

		<div class="content_box">
			<h3 class="subtitle" style="margin-bottom: 10px;">{$messages.415}</h3>
			<textarea cols="138" rows="15" name="d[message]" class="field">{$messages.254}</textarea>
		
			<div class="center">
				{if $isPublicQuestion}
				<input class="field" type="checkbox" style="height: 15px;" name="d[public_answer]" value="1" /> <span style="font-size: 0.8em;">{$messages.500893}</span><br /><br />
				{/if}
				
				<input type="submit" name="z" value="{$messages.1197}" class="button" />
				
				<input type="hidden" name="d[replied_to_this_messages]" value="{$comm_id}" />
				<input type="hidden" name="d[message_to]" value="{$newMessage.to}" />
				<input type="hidden" name="d[from]" value="{$newMessage.from}" />
				<input type="hidden" name="d[regarding_ad]" value="{$newMessage.about}" />
			</div>
		
		</div>
	{/if}
	
	<div class="center">
		<a href="{$userManagementHomeLink}" class="button">{$messages.416}</a>
	</div>
	
{if $canReply}</form>{/if}