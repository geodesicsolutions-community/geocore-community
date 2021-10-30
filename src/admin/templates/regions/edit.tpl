{* 6.0.7-115-g9890342 *}


<div class="closeBoxX"></div>
<div class="lightUpTitle" id="newConfirmTitle">{if $new}Add{else}Edit{/if} Region{if $region.name} {$region.name}{/if}</div>


<form style="display:block; margin: 15px; width: 450px;" action="index.php?page={if $new}region_create{else}region_edit{/if}&amp;parent={$parent}{if !$new}&amp;region={$region.id}{/if}&amp;p={$page|escape}" method="post">
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn"><input type="checkbox" name="enabled" value="1"{if $new||$region.enabled=='yes'} checked="checked"{/if} /></div>
		<div class="rightColumn">Enabled?</div>
		<div class="clearColumn"></div>
	</div>
	{if !$new}
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">Region ID#</div>
			<div class="rightColumn">{$region.id}</div>
			<div class="clearColumn"></div>
		</div>
	{/if}
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Region Parent</div>
		<div class="rightColumn">
			{if $region.parent}
				{foreach $parents as $parent}
					{$parent.name}
					{if !$parent@last} &gt;{/if}
				{/foreach}
			{else}
				Top Level
			{/if}
		</div>
		<div class="clearColumn"></div>
	</div>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Region Level</div>
		<div class="rightColumn">{$region.level} ({$level.region_type})</div>
		<div class="clearColumn"></div>
	</div>

	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Display Order #</div>
		<div class="rightColumn"><input type="text" name="display_order" value="{$region.display_order}" size="4" /></div>
		<div class="clearColumn"></div>
	</div>
	{if $level.region_type=='country'||$level.region_type=='state/province'}
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">{$level.type_label} Abbreviation<br /><span class="small_font">(If Applicable, Used for Billing)</span></div>
			<div class="rightColumn"><input type="text" name="billing_abbreviation" value="{$region.billing_abbreviation|escape}" size="4" /></div>
			<div class="clearColumn"></div>
		</div>
	{else}
		{* Don't show it, but still maintain the value just in case level configuration is temporarily jacked up *}
		<input type="hidden" name="billing_abbreviation" value="{$region.billing_abbreviation|escape}" />
	{/if}
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Unique Name<br /><span class="small_font">(Optional, used for subdomain)</span></div>
		<div class="rightColumn"><input type="text" name="unique_name" value="{$region.unique_name}" size="20" /></div>
		<div class="clearColumn"></div>
	</div>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Billing % Tax</div>
		<div class="rightColumn"><input type="text" name="tax_percent" value="{$region.tax_percent}" size="3" />%</div>
		<div class="clearColumn"></div>
	</div>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Billing Flat Tax</div>
		<div class="rightColumn">{$precurrency}<input type="text" name="tax_flat" value="{$region.tax_flat}" size="3" />{$postcurrency}</div>
		<div class="clearColumn"></div>
	</div>
	
	<br />
	<div class="col_hdr">Region Name</div>
	<br />
	{foreach $languages as $lang}
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">{$lang.language}</div>
			<div class="rightColumn"><input type="text" name="name[{$lang.language_id}]" size="20" value="{if $names[$lang.language_id]}{$names[$lang.language_id]}{else}{$region.name}{/if}" /></div>
			<div class="clearColumn"></div>
		</div>
	{/foreach}
	<br /><br />
	<div style="float: right;">
		<input type="submit" name="auto_save" value="{if $new}Add Region{else}Apply Changes{/if}" class="mini_button" />
		<input type="button" class="closeLightUpBox mini_cancel" value="Cancel" />
	</div>
</form>