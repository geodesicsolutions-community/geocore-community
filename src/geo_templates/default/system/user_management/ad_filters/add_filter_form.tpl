{* 7.6.3-149-g881827a *}

<div class="content_box">
	<h1 class="title my_account">{$messages.626}</h1>
	<h3 class="subtitle">{$messages.429}</h3>
	<p class="page_instructions">{$messages.430}</p>
	
	<form action="{$formTarget}" method="post">
		<div class="row_even">
			<label class="field_label">{$messages.431}</label>
			{$categoryDDL}		
		</div>
		<div class="row_odd">
			<label class="field_label">{$messages.432}</label>
			<input type="radio" name="d[subcategories_also]" value="1" checked="checked" />&nbsp;{$messages.3271}  &nbsp;&nbsp; 
			<input type="radio" name="d[subcategories_also]" value="0" />&nbsp;{$messages.3272}		
		</div>
		<div class="row_even">
			<label for="d[search_terms]" class="field_label">{$messages.433}<br /><span class="mini_note">{$messages.434}</span></label>
			<input type="text" name="d[search_terms]" id="d[search_terms]" size="50" maxlength="50" class="field" />		
		</div>
		{foreach $addonFilters as $addonName => $filterInput}
			<div class='{cycle values="row_odd,row_even"}'>
				{$filterInput}
			</div>
		{/foreach}
		<div class="center"><input type="submit" value="{$messages.3273}" class="button" /></div>
	</form>
</div>	

<div class="center">
	<a href="{$userManagementHomeLink}" class="button">{$messages.436}</a>
</div>