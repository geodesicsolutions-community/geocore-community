{* 7.5.3-125-gf5f0a9a *}

<div class="content_box">
	<h1 class="title my_account">{$messages.622}</h1>
	<h3 class="subtitle">{$messages.363} {$helpLink}</h3>
	<p class="page_instructions">{$messages.364}</p>
	
	<form action="{$formTarget}" method="post">
		<div class="listing_extra_item">
			<input type="radio" name="c[communication_type]" value="1"{if $communicationType == 1} checked="checked"{/if} />
			{$messages.365}
			<br />
			<span class="sub_note">{$messages.366}</span>
		</div>
		
		<div class="listing_extra_item">
			<input type="radio" name="c[communication_type]" value="3"{if $communicationType == 3} checked="checked"{/if} />
			{$messages.369}
			<br />
			<span class="sub_note">{$messages.370}</span>
		</div>
		
		<div class="center"><input type="submit" class="button" name="z" value="{$messages.372}" /></div>
	</form>
</div>

<div class="center">
	<a href="{$userManagementHomeLink}" class="button">{$messages.371}</a>
</div>