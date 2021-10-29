{* 6.0.7-237-g52e6c31 *}
<label>{$messages.2311}</label>
<div class="element">
	<form action="" method="post">
		<select class="field" name="set_state_filter" onchange="if(this.options[this.selectedIndex].value != '') this.form.submit();">
			<option {if $tpl_vars.first_opt_selected}selected="selected"{/if} value="clear state">{$messages.2310}</option>

			{if $tpl_vars.clear_opt}
				<option value="clear state">{$messages.2304}</option>
			{/if}
			{foreach from=$opts item=o}
				<option {if $o.sel}selected="selected"{/if} value="{$o.value}">{$o.name}</option>
			{/foreach}
			{if $tpl_vars.clear_opt}
				<option value="clear state">{$messages.2301}</option>
			{/if}
		</select>
	</form>
</div>
