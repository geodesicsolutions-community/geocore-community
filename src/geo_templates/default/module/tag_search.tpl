{* 7.3beta4-137-ge270e42 *}

<h2 class="title">{$messages.500871} {$helpLink}</h2>
<form method="get" action="{$classifieds_file_name}">
	<div class="center">
		<input type="hidden" name="a" value="tag" />
		{* Designer Note:  The input field MUST have class tagSearchField for
			auto-complete to work.
			*}
		<input type="text" name="tag" class="field tagSearchField" value="{$current_tag|escape}" />
		{if $messages.500873}<input type="submit" value="{$messages.500873|escape}" class="mini_button" />{/if}
	</div>
</form>
