{* 6.0.7-90-ge35ffc8 *}
Selected:
{if $new_parents}
	<a href="{$browse_link}0" class="moveBrowseLink">Top Level</a> >
	{foreach $new_parents as $p}
		<a href="{$browse_link}{$p.id}" class="moveBrowseLink">{$p.name}</a>
		{if !$p@last} &gt;{/if}
	{/foreach}
{else}
	<a href="{$browse_link}0" class="moveBrowseLink">Top Level</a>
{/if}
<br />
<input type="hidden" name="browse_region" value="{$new_parent}" />
<div style="height: 120px; overflow: auto;">
	<ul>
	{foreach $browse_regions as $r}
		<li>
			<a href="{$browse_link}{$r.id}" class="moveBrowseLink">{$r.name|fromDB} ({$r.id})</a>
		</li>
	{/foreach}
	</ul>
</div>