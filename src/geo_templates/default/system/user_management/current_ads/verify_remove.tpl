{* 7.5.3-125-gf5f0a9a *}
<div class="content_box">
	<h1 class="title my_account">{$messages.635}</h1>
	<h3 class="subtitle">{$messages.475}</h3>
	<p class="page_instructions">{$messages.476}</p>
	
	<form action="{$formTarget}" method="post">
		<div class="row_even">
			<input type="hidden" name="c[id]" value="{$classifiedId}" />
			<label for="registration_code" class="field_label">{$messages.477}</label>
			<textarea name="c[reason_for_removal]" cols="80" rows="5" class="field"></textarea>
		</div>
		
		<div class="center">
			<input type="submit" name="z[remove]" value="{$messages.478}" class="button" />
		</div>
	</form>
</div>

<br />
<div class="center">
	<a href="{$currentAdsLink}" class="button">{$messages.480}</a>
</div>