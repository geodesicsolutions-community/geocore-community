{* 7.5.3-36-gea36ae7 *}

<div class="listing_extra_item">
	{if !$allFree}<div class="listing_extra_cost price">{$price}</div>{/if}
	
	<input type="hidden" name="c[charitable_badge_toggle]" value="0" />
	<input id="charitable_badge_main_toggle" onclick='if(this.checked) jQuery(".charitable_badge_option").prop("disabled",false); else jQuery(".charitable_badge_option").prop("disabled",true);' type="checkbox" name="c[charitable_badge_toggle]" value="1" {if $toggle}checked="checked"{/if} />
	<label for="charitable_badge_main_toggle">{$toggleLabel}</label>
	<a href="show_help.php?addon=charity_tools&amp;auth=geo_addons&amp;textName=tooltip_listing_placement" class="lightUpLink" onclick="return false;">
		<img src="{external file=$helpIcon}" alt="" class="help_icon" />
	</a>

	{if $error}<br /><span class="error_message">{$error}</span>{/if}
</div>

<div class="listing_extra_item_child clearfix">
	<ul id="charitable_badges">
		{foreach $badges as $id => $b}
			<li>
				<input type="radio" class="charitable_badge_option" id="charitable_badge{$id}" name="c[charitable_badge_choice]" onchange="if(this.checked)jQuery('#charitable_badge_main_toggle').checked=true;" value="{$id}" {if $choice == $id}checked="checked"{/if} />
				<label for="charitable_badge{$id}"><img src="{$b.image}" alt="" /></label>
				{if $b.show_tooltip}
					<a href="show_help.php?addon=charity_tools&amp;auth=geo_addons&amp;textName=tooltip_charitable_description_{$id}" class="lightUpLink" onclick="return false;">
						<img src="{external file=$helpIcon}" alt="" class="help_icon" />
					</a>
				{/if}
			</li>
		{/foreach}
	</ul>
</div>
{add_footer_html}
<script type="text/javascript">
	if(jQuery('#charitable_badge_main_toggle').prop('checked')) {
		jQuery(".charitable_badge_option").prop('disabled',false);
	} else {
		jQuery(".charitable_badge_option").prop('disabled',true);
	}
</script>
{/add_footer_html}