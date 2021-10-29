{* 7.6.3-149-g881827a *}

<div class="content_box">
	{if $already_feedbacked} 
		<h1 class="title">{$messages.102517}</h1>
		<div class="note_box">{$messages.102518}</div>
	{else} 
		<h1 class="title">{$messages.102520}</h1>
		<p class="page_instructions">{$messages.102503}</p>
		
		<form action="{$formTarget}" method="post">
			{if $hidden_rated_id}<input type="hidden" value="{$hidden_rated_id}" name="e[rated_id]" />{/if}
			
			<div class="row_even">
				<label class="field_label">{$messages.102504}</label>
				{$username}
			</div>
			<div class="row_odd">
				<label class="field_label">{$messages.102505}</label>
				<div style="clear: both; display: inline-block; min-width: 260px;">{$auction_id} - {$title}</div>
			</div>
			<div class="row_even">
				<label class="field_label">{$messages.102506}</label>
				{$startDate}
			</div>
			<div class="row_odd">
				<label class="field_label">{$messages.102507}</label>
				{$endDate}
			</div>
			<div class="{if $rating_error}field_error_row{else}row_even{/if}">
				<label class="field_label">{$messages.102508}</label>
				<div style="clear: both; display: inline-block; min-width: 260px;">
				<input type="radio" name="e[rating]" value="a" />{$messages.102735}&nbsp;
				<input type="radio" name="e[rating]" value="b" />{$messages.102734}&nbsp;
				<input type="radio" name="e[rating]" value="c" checked="checked" />{$messages.102733}
				</div>
				{if $rating_error}<br /><span class="error_message">{$rating_error}</span>{/if}
			</div>
			<div class="{if $feedback_error}field_error_row{else}row_odd{/if}">
				<label class="field_label">{$messages.102514}</label>&nbsp;{if $feedback_error}<span class="error_message">{$feedback_error}</span>{/if}
				<textarea name="e[feedback]" rows="10" cols="80" class="field">{$feedback}</textarea>
			</div>
			<div class="center">
				<input type="submit" name="save_feedback" value="{$messages.102516}" class="button" />
				<input type="reset" class="button" />
			</div>
		</form>	
	{/if}
</div>
