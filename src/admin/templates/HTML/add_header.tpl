{* 6.0.7-3-gce41f93 *}
<div class='options_header'>
{assign var='c' value=0}
{foreach from=$header key=k item=i}
	{if $i|is_array}
		<div id='table_header{$c}' class='{if $total ne 1 AND $c ne $total}table_line {/if}' style='{if $i.width}width:{$i.width};{/if}{if $total eq 1} text-align: left;{/if}'>
		{$i.text}
		</div>
		
	{else}
		<div id='table_header{$k}' class='{if $line_limit  ne $c}table_line {/if} table_header_spacer'>
		{$i}
		</div>
	{/if}
	{assign var='c' value=$c+1}
{/foreach}
<div class='clearColumn'></div>
</div>
