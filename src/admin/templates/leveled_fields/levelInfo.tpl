{* 7.1beta3-73-gdb3fbe6 *}

{$last_id=0}
{foreach $parents as $p}
	<div style="display: inline-block; padding: 5px; margin: 5px;">
		<a href="index.php?page=leveled_field_values&amp;leveled_field={$leveled_field}&amp;parent={$last_id}">
			{if $levels.{$p.level}.label}
				<strong>{$levels.{$p.level}.label}:</strong>
			{else}
				<em style="color: red;">[Level {$p.level} Blank Label]</em> 
			{/if}
		</a>
		<br />
		<select>
			<option>
				{$p.name}{if $p.enabled=='no'} [Disabled Value!]{/if}
			</option>
		</select>
		
	</div>
	{$last_id=$p.id}
{/foreach}

<div style="display: inline-block; padding: 5px; margin: 5px;">
	{if $levelInfo.label}
		<strong>{$levelInfo.label}:</strong>
	{elseif $levelInfo.level}
		<em style="color: red;">[Level {$levelInfo.level} Blank Label]</em>
	{else}
		<em>[Add Value to Create Level {$level}]</em>
	{/if}
	<br />
	<select>
		<option>Set Values Below!</option>
	</select>
</div>

<a href="index.php?page=leveled_field_levels&amp;leveled_field={$leveled_field}" class="mini_button">Edit Levels</a>
<br /><br />