{* 7.6.3-149-g881827a *}
<div class="element">
	<a href="{$classifieds_file_name}" style="display: none;" id="cat_dropdown_base">&nbsp;</a>
	<select name="category_quick_nav{$nav_id}" id="category_quick_nav{$nav_id}"
		 onchange="location.href = jQuery('#cat_dropdown_base').attr('href')+'?a=5&amp;b=' + this.options[this.selectedIndex].value;" class="field">
		{foreach from=$options key=id item=option}
	 		<option value="{$option.value}"{if $option.value == $category_id} selected="selected"{/if}>{$option.label}</option>
	 	{/foreach}
	</select>
</div>