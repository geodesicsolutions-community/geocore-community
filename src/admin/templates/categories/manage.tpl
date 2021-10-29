{* 16.09.0-79-gb63e5d8 *}

<div class="closeBoxX"></div>
<div class="lightUpTitle" id="newConfirmTitle" style="min-width: 250px;">Manage Category</div>
<div class="center"><h4>Category: <span class="color-primary-one">{$category.name}</span></h4></div>
<div class="center font-medium" style="padding: 20px;">
	<a href="index.php?mc=&amp;page=fields_to_use&amp;categoryId={$category_id}" class="mini_button" style="width: 120px; margin:2px;"><i class="fa fa-random" style="float:left; padding:2px;"></i> Fields To Use</a>
	<br />
	<a href="index.php?mc=&amp;page=category_durations&amp;c={$category_id}" class="mini_button" style="width: 120px; margin:2px;"><i class="fa fa-calendar" style="float:left; padding:2px;"></i> Durations</a>
	<br />
	<a href="index.php?page=category_templates&amp;b={$category_id}" class="mini_button lightUpLink" style="width: 120px; margin:2px;"><i class="fa fa-paint-brush" style="float:left; padding:2px;"></i> Templates</a>
	<br />
	<a href="index.php?mc=&amp;page=categories_questions&amp;b={$category_id}" class="mini_button" style="width: 120px; margin:2px;"><i class="fa fa-question-circle" style="float:left; padding:2px;"></i> Questions</a>
	{if $addon_links}
		<br /><br />
		<strong>Available Addons:</strong>
		
		{foreach $addon_links as $links}
			{foreach $links as $link}
				<br />
				<a href="{$link.href}" class="mini_button" style="width: 120px; margin:2px;">{$link.label}</a>
			{/foreach}
		{/foreach}
	{/if}
	<br /><br />
	<hr />
	<a href="index.php?page=category_copy_parts&amp;categoryId={$category_id}" class="mini_button lightUpLink" style="width: 120px; margin:2px;"><i class="fa fa-copy" style="float:left; padding:2px;"></i> Copy... To ...</a>
</div>

