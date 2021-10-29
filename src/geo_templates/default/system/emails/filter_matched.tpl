{* 7.5.3-36-gea36ae7 *}
{$messageBody}<br />
<br />
{foreach $data as $id => $filter}
	{if $filter.filter_info.string}
		{$filterLabel} {$filter.filter_info.string}<br />
	{/if}
	{if $filter.filter_info.category}
		{$categoryLabel} {$filter.filter_info.category}<br />
	{/if}
	{foreach $filter.filter_info.addons as $addonName => $addonInfo}
		{$addonInfo}<br />
	{/foreach}
	{$titleLabel} {$filter.title}<br />
	{$linkLabel} <a href="{$filter.url}">{$filter.url}</a><br />
	{if !$filter@last}<br />-----<br /><br />{/if}
{/foreach}