{* 7.1beta4-5-g3b68d86 *}


<div class="closeBoxX"></div>
<div class="lightUpTitle" id="newConfirmTitle">{if $new}Add{else}Edit{/if} Value{if $value.name} {$value.name}{/if}</div>


<form style="display:block; margin: 15px; width: 450px;" action="index.php?page=leveled_field_value{if $new}_create{else}_edit{/if}&amp;leveled_field={$leveled_field}&amp;parent={$parent}{if !$new}&amp;value={$value.id}{/if}&amp;p={$page|escape}" method="post">
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn"><input type="checkbox" name="enabled" value="1"{if $new||$value.enabled=='yes'} checked="checked"{/if} /></div>
		<div class="rightColumn">Enabled?</div>
		<div class="clearColumn"></div>
	</div>
	{if !$new}
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">Value ID#</div>
			<div class="rightColumn">{$value.id}</div>
			<div class="clearColumn"></div>
		</div>
	{/if}
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Multi-Level Field Group</div>
		<div class="rightColumn">{$leveled_field_label}</div>
		<div class="clearColumn"></div>
	</div>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Parent Value</div>
		<div class="rightColumn">
			{if $value.parent}
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
		<div class="leftColumn">Value Level</div>
		<div class="rightColumn">{$value.level}</div>
		<div class="clearColumn"></div>
	</div>

	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Display Order #</div>
		<div class="rightColumn"><input type="text" name="display_order" value="{$value.display_order}" size="4" /></div>
		<div class="clearColumn"></div>
	</div>
	
	<br />
	<div class="col_hdr">Value Name</div>
	<br />
	{foreach $languages as $lang}
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">{$lang.language}</div>
			<div class="rightColumn"><input type="text" name="name[{$lang.language_id}]" size="20" value="{if $names[$lang.language_id]}{$names[$lang.language_id]}{else}{$value.name}{/if}" /></div>
			<div class="clearColumn"></div>
		</div>
	{/foreach}
	<br /><br />
	<div style="float: right;">
		<input type="submit" name="auto_save" value="{if $new}Add Value{else}Apply Changes{/if}" class="mini_button" />
		<input type="button" class="closeLightUpBox mini_cancel" value="Cancel" />
	</div>
	<br />
</form>