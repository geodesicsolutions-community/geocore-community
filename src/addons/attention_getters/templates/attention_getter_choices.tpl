{* 7.5.3-36-gea36ae7 *}

<div class="listing_extra_item">
	{if !$allFree}<div class="listing_extra_cost price">{$price}</div>{/if}
	{if $checkbox_hidden}
		{$checkbox_hidden}
	{else}
		<input type="hidden" name="c[attention_getter]" value="0" />
	{/if}
	<input id='agCheckbox' onclick='Disab();' type="checkbox" name="c[attention_getter]" value="1" {if $mainToggle}checked="checked"{/if} {$input_extra} />
	<label for="agCheckbox">{$toggleLabel}</label>
	<a href="show_help.php?addon=attention_getters&amp;auth=geo_addons&amp;textName=AG_desc" class="lightUpLink" onclick="return false;"><img src="{if $in_admin}../{/if}{external file=$messages.500797}" alt="" /></a>

	{if $error}<br /><span class="error_message">{$error}</span>{/if}
</div>

<div class="listing_extra_item_child clearfix">
	<ul id="attention_getters">
		{foreach from=$list item=ag key=i}
			<li>
				<input type="radio" id="geo_radioag{$i}" name="c[attention_getter_choice]" onchange="if(this.checked)document.getElementById('agCheckbox').checked=true;" value="{$ag.id}" {if $ag.checked}checked="checked"{/if} />
				<img id="ag{$i}" onclick="Enab(this.id);" src="{if $in_admin}../{/if}{$ag.img}" style="border: none;" alt="" />
			</li>
		{/foreach}
	</ul>
</div>
{add_footer_html}
<script type="text/javascript">
	Disab();
</script>
{/add_footer_html}