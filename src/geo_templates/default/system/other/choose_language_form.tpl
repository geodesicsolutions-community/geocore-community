{* 16.07.0-78-g99df348 *}

<div class="content_box language_form">
	<h2 class="title">{$messages.327}</h3>
	<p class="page_instructions">{$messages.328}</p>
	<div style="width: 100%; text-align: center; padding-top: 5px; padding-bottom: 5px;">	
		<form id="form1" action="" method="post">		
			{$messages.329}
			<select class="field" name="set_language_cookie">
				{foreach $languages as $l}
					<option value="{$l.id}"{if $l.selected} selected="selected"{/if}>{$l.name}</option>
				{/foreach}
			</select>
			<div class="center">
				<input class="button" type="submit" name="submit" value="{$messages.330}" />
			</div>
		</form>
	</div>
</div>