{* 6.0.7-3-gce41f93 *}
<div class="browsing_options_links">
	{$headerText}
{foreach from=$option_data item=opt name=optloop}
	<a href="{$uri}{if $opt.param}&amp;o={$opt.param}{/if}" class="{if $opt.selected}browsing_options_selected{else}browsing_options_not_selected{/if}">{$opt.text}</a>
	{if $smarty.foreach.optloop.total > 1 && !$smarty.foreach.optloop.last}
		 {$delimeter} {* don't print delimeter if only one option or this is last in list *}
	{/if}
{/foreach}	
</div>