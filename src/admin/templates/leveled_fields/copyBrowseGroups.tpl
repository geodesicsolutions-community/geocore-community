{* 7.1beta2-159-g657f89c *}
Field Group: 

<br />
<input type="hidden" name="new_leveled_field" value="0" />
<input type="hidden" name="browse_value" value="0" />
<div style="height: 120px; overflow: auto;">
	<ul>
	{foreach $browse_values as $r}
		<li>
			<a href="{$browse_link}{$r.id}" class="moveBrowseLink">{$r.label|fromDB}</a>
		</li>
	{/foreach}
	</ul>
</div>