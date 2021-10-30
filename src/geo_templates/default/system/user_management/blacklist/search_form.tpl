{* 7.6.3-149-g881827a *}

<div class="content_box">
	<h1 class="title my_account">{$messages.102993}</h1>
	<p class="page_instructions">{$messages.103057}</p>
	<form action="{$searchFormTarget}" method="post">
		<div class="row_even center">
			{if $searchError}
				<span class="error_message">{$searchError|fromDB}</span>
				<br /><br />
			{/if}
	
			<input type="radio" name="d[field_type]" value="1" checked="checked" /> {$messages.102995}&nbsp;
			<input type="radio" name="d[field_type]" value="2" checked="checked" /> {$messages.102996}&nbsp;
			<input type="radio" name="d[field_type]" value="3" checked="checked" /> {$messages.102997}
			
			<br /><br />
			<input type="text" name="d[text_to_search]" size="40" maxlength="30" class="field" />
			<input type="submit" name="search" value="{$messages.102998}" class="button" />
		</div>
	</form>
</div>

<div class="center">
	<a class="button" href="{$userManagementHomeLink}">{$messages.102999}</a>
</div>