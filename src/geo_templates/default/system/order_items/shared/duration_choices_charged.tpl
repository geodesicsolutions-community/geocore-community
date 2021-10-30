{* 7.5.3-36-gea36ae7 *}
{foreach from=$durations item=d}
<option value="{$d.numerical_length}" {if $d.selected}selected="selected"{/if}>{$d.display_length} - {$d.display_amount}</option>
{/foreach}