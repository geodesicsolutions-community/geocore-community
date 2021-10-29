{* 6.0.7-3-gce41f93 *}
<ul class="sub_regions">
	{foreach from=$sub_regions item=sub_region}
		<li class="element">
			<a href="{$sub_region.link}">
				{$sub_region.label}
			</a>
		</li>
	{/foreach}
</ul>