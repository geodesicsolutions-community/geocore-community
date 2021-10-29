{* 7.6.3-149-g881827a *}

{foreach from=$listingData key="browse_type" item='listings'}
	<div class="content_box">
		<h2 class="title">{if $display_classifieds && $browse_type == 1}<span class="category-intro">{$messages.200109}</span>{else}<span class="category-intro">{$messages.200110}</span>{/if} {if $category_name}{$category_name}{else}{$storefront_name}{/if}</h2>
		{if $listings}
			{$listings}
		{else}
			<div class="no_results_box">{if $display_classifieds && $browse_type == 1}{$messages.17}{else}{$messages.100017}{/if}</div>
		{/if}
	</div>
	<br />
{/foreach}

{if $pagination}{$pagination}{/if}
