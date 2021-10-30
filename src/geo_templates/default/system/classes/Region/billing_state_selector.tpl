{* 7.5.3-36-gea36ae7 *}
<div id="{$name}_state_remove_me" style="display: inline-block;">
	{if $states === false}
		<input type="text" name="{$name}[state]" class="field" />
	{else}
		<select name="{$name}[state]" id="{$name}_state_ddl" class="field">
			<option value=""></option>
			{foreach $states as $id => $s}
				<option value="{$s.abbreviation}"{if $s.selected} selected="selected"{/if}>{$s.name}</option>
			{/foreach}
		</select>
	{/if}
</div>