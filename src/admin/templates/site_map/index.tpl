{* 7.5.3-36-gea36ae7 *}
<div style="text-align: center;">
<fieldset id='AdminSiteMap' style='width: 800px; margin: auto; text-align: left;'>
	<legend>Admin Site Map</legend>
	<div class="bigger_container">
		<div class="site_map">
{foreach from=$page_structure item="top_category"}
<div class="column">
	<ul>
	{foreach from=$top_category.children_categories item="category"}
		{include file="site_map/category"}
	{/foreach}
	</ul>
</div>
{/foreach}
		</div>
	</div>
</fieldset>
</div>