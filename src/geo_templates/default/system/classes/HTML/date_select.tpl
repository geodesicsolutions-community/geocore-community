{* 16.09.0-106-ge989d1f *}

<div class="date-time-labels">

	<div {if $in_admin}style='display: inline-block;'{/if}>	
		<div {if $in_admin}style='display: inline-block;'{/if}>
			<label>{$labels.month}</label><br /> <select {if $isEnd}id="endtime_month"{/if} name="{$names.month}" class="field date-time">
			{section name=month start=1 loop=13}
				<option value="{$smarty.section.month.index}"{if $smarty.section.month.index == $values.month} selected="selected"{/if}>{$smarty.section.month.index}</option>
			{/section}
			</select>
		</div>
		<div {if $in_admin}style='display: inline-block;'{/if}>
			<label>{$labels.day}</label><br /> <select {if $isEnd}id="endtime_day"{/if} name="{$names.day}" class="field date-time">
			{section name=day start=1 loop=32}
				<option value="{$smarty.section.day.index}"{if $smarty.section.day.index == $values.day} selected="selected"{/if}>{$smarty.section.day.index}</option>
			{/section}
			</select>
		</div>
		<div {if $in_admin}style='display: inline-block;'{/if}>	
			<label>{$labels.year}</label><br /> <select {if $isEnd}id="endtime_year"{/if} name="{$names.year}" class="field date-time">
			{* $years is the current year plus the next two *} 
			{foreach from=$years item=year}
				<option value="{$year}"{if $year == $values.year} selected="selected"{/if}>{$year}</option>
			{/foreach}
			</select>
		</div>
	</div>

{* separate the date and time with a bunch of non-breaking spaces *}
{section name=spacer start=0 loop=11}&nbsp;{/section}

	<div {if $in_admin}style='display: inline-block;'{/if}>
		<div {if $in_admin}style='display: inline-block;'{/if}>
			<label>{$labels.hour}</label><br /> <select {if $isEnd}id="endtime_hour"{/if} name="{$names.hour}" class="field date-time">
			{section name=hour start=0 loop=24}
				<option value="{$smarty.section.hour.index}"{if $smarty.section.hour.index == $values.hour} selected="selected"{/if}>{$smarty.section.hour.index|string_format:"%02d"}</option>
			{/section}
			</select>
		</div>

		<div {if $in_admin}style='display: inline-block;'{/if}>		
			<label>{$labels.minute}</label><br /> <select {if $isEnd}id="endtime_minute"{/if} name="{$names.minute}" class="field date-time">
			{section name=minute start=0 loop=60}
				<option value="{$smarty.section.minute.index}"{if $smarty.section.minute.index == $values.minute} selected="selected"{/if}>{$smarty.section.minute.index}</option>
			{/section}
			</select>
		</div>
	</div>
	
</div>