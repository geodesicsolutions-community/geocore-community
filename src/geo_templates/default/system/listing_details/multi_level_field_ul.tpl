{* 7.5.3-36-gea36ae7 *}
<ul class="info">
	{foreach $leveled_fields as $levels}
		{foreach $levels as $level}
			<li class="label">{$level.level_info.label}</li>
			<li class="value">{$level.name}</li>
		{/foreach}
	{/foreach}
</ul>