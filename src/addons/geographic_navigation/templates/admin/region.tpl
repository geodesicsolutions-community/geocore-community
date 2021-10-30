{* 7.5.3-36-gea36ae7 *}
{strip}
<div>
	{if $region.id!='new'}
		<span class="geographicAddonExpand_" id="regionExpandButton_{$region.id}">
			<img src="../addons/geographic_navigation/plus.gif" alt="" class="plus" />
			<img src="../addons/geographic_navigation/minus.gif" alt="" class="minus" style="display: none;" />
		</span> 
		{if $region.childClass!='isRegion'}
			<span title="{$region.level}">
				{$region.name}  
				{if $subdomains}
					[ <strong>{if $region.subdomain}{$region.subdomain}{else}??{/if}</strong>.{$domain} ]
					<input type="button" value="Edit Sub-Domain" class="editRegionButton_" />
				{/if}
				<input type="checkbox" class="regionCheckBox_ {$region.childClass}"
					value="{$region.id}"
					 {if $region.on_off_value}checked="checked"{/if} />
			</span>
			<div class="regionEdit" style="display:none;">
				<form class="editRegionForm_" method="post" action="AJAX.php?controller=addon_geographic_navigation&action=editRegion">
					<input type="hidden" name="childClass" value="{$region.childClass}" />
					<input type="hidden" name="region_id" value="{$region.id}" />
					{if $region.childClass=='isState'}
						<input type="hidden" name="country_id" value="{$region.country_id}" />
					{/if}
					{$region.name|escape}
					[ <input type="text" name="subdomain" value="{$region.subdomain|escape}" />.{$domain} ]
					<input type="submit" value="Apply" />
					<input type="button" class="cancelRegionButton_" value="Cancel" />
				</form>
			</div>
		{else}
			<div class="regionField">
				<span title="{$region.level}">{$region.name}</span> 
				{if $subdomains}
					[ <strong>{if $region.subdomain}{$region.subdomain}{else}??{/if}</strong>.{$domain} ]
				{/if}
				<img src="../addons/geographic_navigation/move.gif" class="regionNameHook" alt="Change Order" />
				<input type="button" value="Edit" class="editRegionButton_" />
				<form class="deleteRegionForm_" method="post" action="AJAX.php?controller=addon_geographic_navigation&action=deleteRegion">
					<input type="hidden" name="parent_state" value="{$region.parent_state}" />
					<input type="hidden" name="parent_region" value="{$region.parent_region}" />
					<input type="hidden" name="region_id" value="{$region.id}" />
					<input type="submit" value="Delete" class="deleteRegionButton" />
				</form>
			</div>
			<div class="regionEdit" style="display:none;">
				<form class="editRegionForm_" method="post" action="AJAX.php?controller=addon_geographic_navigation&action=editRegion">
					<input type="hidden" name="childClass" value="{$region.childClass}" />
					<input type="hidden" name="parent_state" value="{$region.parent_state}" />
					<input type="hidden" name="parent_region" value="{$region.parent_region}" />
					<input type="hidden" name="region_id" value="{$region.id}" />
					<input type="text" name="label" value="{$region.name|escape}" />
					{if $subdomains}
						[ <input type="text" name="subdomain" value="{$region.subdomain|escape}" />.{$domain} ]
					{/if}
					<input type="submit" value="Apply" />
					<input type="button" class="cancelRegionButton_" value="Cancel" />
				</form>
			</div>
		{/if}
	{else}
		{* A new field *}
		<div class="regionField"><input type="button" value="Add new {$region.level}" class="editRegionButton_" /></div>
		<div class="regionEdit" style="display:none;">
			<form class="newRegionForm_" method="post" action="AJAX.php?controller=addon_geographic_navigation&action=addNewRegion">
				<input type="hidden" name="parent_state" value="{$region.parent_state}" />
				<input type="hidden" name="parent_region" value="{$region.parent_region}" />
				<input type="text" name="label" value="region name" onfocus="this.value = ''" />
				{if $subdomains}
					[ <input type="text" name="subdomain" value="region.sub-domain" onfocus="this.value = ''" />.{$domain} ]
				{/if}
				<input type="submit" value="Add" />
				<input type="button" class="cancelRegionButton_" value="Cancel" />
			</form>
		</div>
	{/if}
</div>
<div class="notRetrieved {$region.childClass}" id="subRegion_{$region.id}" style="display: none;">Loading...</div>
{/strip}