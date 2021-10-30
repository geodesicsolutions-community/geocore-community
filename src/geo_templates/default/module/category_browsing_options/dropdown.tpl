{* 6.0.7-3-gce41f93 *}
<div class="browsing_options_links">
	<form action="" method="get">
		{$headerText}
	
		<select name="o" onchange="to='{$uri}&amp;o='+this.options[this.selectedIndex].value; window.location.href=to;">
		{foreach from=$option_data item=opt name=optloop}
			<option {if $opt.selected}selected="selected"{/if} value="{$opt.param}">{$opt.text}</option>
		{/foreach}
		</select>
	</form>
</div>