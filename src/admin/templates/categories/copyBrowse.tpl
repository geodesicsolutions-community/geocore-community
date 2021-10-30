{* 7.3.3-139-g0040985 *}

Parent Category:

{if $new_parents}
	<a href="{$browse_link}0" class="moveBrowseLink">Top Level</a> &gt;
	{foreach $new_parents as $p}
		<a href="{$browse_link}{$p.id}" class="moveBrowseLink">{$p.name}</a>
		{if !$p@last} &gt;{/if}
	{/foreach}
{else}
	<a href="{$browse_link}0" class="moveBrowseLink">Top Level</a>
{/if}
<br />
<input type="hidden" name="browse_value" value="{$new_parent}" />
<div style="height: 120px; overflow: auto;">
	<ul>
	{foreach $browse_values as $r}
		<li>
			<a href="{$browse_link}{$r.category_id}" class="moveBrowseLink">{$r.category_name|fromDB} ({$r.category_id})</a>
		</li>
	{/foreach}
	</ul>
</div>