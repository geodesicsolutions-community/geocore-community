{* 7.6.3-149-g881827a *}

<div class="content_box">
	<h1 class="title">{$messages.102410}</h1>
	<p class="page_instructions">{$messages.102411}</p>
	
	<div class="row_even">
		<label class="field_label"><span class="highlight">{$username}</span></label>
		
		{if $no_feedback}
			 {$messages.102412} {$messages.102436}
		{else}
			 {$messages.102412} {$feedback_score} ({$feedback_percentage}%)
		{/if}
		<a href="show_help.php?a=102826" class="lightUpLink" onclick="return false;"><img src="{$help_image}" alt="" /></a>
	</div>
	
	<br />
	<div class="center">
		<a href="{$view_feedback_link}" class="button">{$messages.102434}</a>
		<a href="{$open_feedback_link}" class="button">{$messages.102435}</a>
	</div>
</div>
