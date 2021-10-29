{* 7.5.3-36-gea36ae7 *}
<ul class="button_list">
{foreach from=$methods item=fancy_name key=internal_name}
	<li><input type="button" class="button" onclick="getOptionsForMethod('{$internal_name}');" value="{$fancy_name}" /></li>
{/foreach}
</ul>