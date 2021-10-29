{* 7.6.3-142-g79032f3 *}
{foreach from=$durations item=d}
<option value="{$d.numerical_length}" {if $d.selected}selected="selected"{/if}>{$d.display_amount} / {$d.display_length}</option>
{/foreach}