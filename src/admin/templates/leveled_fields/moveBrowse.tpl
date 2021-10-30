{* 7.1beta2-155-gb7c0534 *}
Field Group:
<a href="{$browse_groups_link}0" class="moveBrowseLink">{$new_leveled_field_label}</a>
<br />
Parent Value:

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
<input type="hidden" name="new_leveled_field" value="{$new_leveled_field}" />
<input type="hidden" name="browse_value" value="{$new_parent}" />
<div style="height: 120px; overflow: auto;">
	<ul>
	{foreach $browse_values as $r}
		<li>
			<a href="{$browse_link}{$r.id}" class="moveBrowseLink">{$r.name|fromDB} ({$r.id})</a>
		</li>
	{/foreach}
	</ul>
</div>