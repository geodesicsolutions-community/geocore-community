{* 7.5.3-36-gea36ae7 *}

{foreach from=$levels item=level key=key name=levels}
	<select name="b[search_location][{$key}]" class="addonNavigation_regionSelect field{if $level.count == 1 && !$level.emptyHidden} onlyRegionOnLevel{/if}">
		<option value="0">{$msgs.selectRegions}</option>
		{foreach from=$level.regions item=region}
			<option value="{$region.id}"{if $level.selected && $level.selected==$region.id} selected="selected"{/if}>{$region.label}</option>
		{/foreach}
	</select>
{/foreach}

{if $ajax}
	<script type="text/javascript">
		//<![CDATA[
		addonNavigation.init();
		//]]>
	</script>
{/if}