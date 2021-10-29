{* 16.09.0-79-gb63e5d8 *}

{if $parents}
	<div class="breadcrumbBorder">
		<ul id="breadcrumb">
			<li><a href="index.php?page=category_config&amp;parent={$last_id}">Top Level</a></li>
			{foreach $parents as $p}
				<li{if $p@last} class="current2"{/if}><a href="index.php?page=category_config&amp;parent={$p.id}">{$p.name}{if $p.enabled=='no'} <span style='color:red;'>[Disabled!]</span>{/if}</a></li>
			{/foreach}
		</ul>
	</div>
{/if}

<div style="padding: 5px 5px 5px 15px; margin: 0 0 5px 0;">
	{if $parents}<span class="color-primary-one" style="font-weight:bold;">This Category:<span> 
		<a href="index.php?page=category_edit&amp;category={$parent}&amp;p={$page}" class="btn btn-info btn-xs editCatLink" style="margin:0;"><i class="fa fa-pencil"></i> Edit</a>
		<a href="index.php?page=category_manage&amp;category={$parent}&amp;p={$page}" class="btn btn-primary btn-xs lightUpLink" style="margin:0;"><i class="fa fa-gear"></i> Manage</a>
	{else}
		<form action="index.php" method="get">
			<input type="hidden" name="page" value="category_config" />
			<label><span class='color-primary-six' style='font-weight: normal;'>Go to Category (ID#):</span>&nbsp;</label><input type="text" name="parent" placeholder="123" size="4" />
			<input type="submit" value="Go" style="font-size:14px; padding:3px 10px;"/>
		</form>
	{/if}
</div>

