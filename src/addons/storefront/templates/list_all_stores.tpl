{* 7.6.3-149-g881827a *}

{if $states}
	<div class="content_box {cycle values="row_even,row_odd"}">
		<form action="" method="post" id="ssf">
			<label class="field_label" for="storefront_state_filter">
				{$text.list_stores_state_filter_label}
			</label>
			<select name="storefront_state_filter" id="storefront_state_filter" onchange="jQuery('#ssf').submit();" class="field">
				<option value="-1">{$text.list_stores_state_filter_default}</option>
				{foreach $states as $id => $name}
					<option value="{$id}" {if $filter_state == $id}selected="selected"{/if}>{$name}</option>
				{/foreach}
			</select>
		</form>
	</div>
	<br />
{/if} 


<div class="content_box stores-list">
	<h1 class="title">{$text.page_title}</h1>
	
	{if count($stores) > 0}
		<table border='0' cellpadding='3' cellspacing='1' width='100%'>
			<tr class="results_column_header">
				{if $switches.logo}<td class="nowrap">{$text.photo_column}</td>{/if}
				{if $switches.title}<td class="title">{$text.title_column} 	{if $switches.description} / {$text.description_column}{/if}</td>{/if}
				{if $switches.num_items}<td class="nowrap center">{$text.items_column}</td>{/if}
				{if $switches.city}<td class="nowrap">{$text.city_column}</td>{/if}
				{if $switches.state}<td class="nowrap">{$text.state_column}</td>{/if}
				{if $switches.zip}<td class="nowrap">{$text.zip_column}</td>{/if}
			</tr>
			{foreach from=$stores item="store"}
				<tr class="{cycle values='row_even,row_odd'}">
					{if $switches.logo}<td class="nowrap center stores-list-logo"><a href="{$classifieds_file_name}?a=ap&amp;addon=storefront&amp;page=home&amp;store={$store.userid}">{$store.image}</a></td>{/if}
					{if $switches.title}<td class="title"><span class="highlight_links"><a href="{$classifieds_file_name}?a=ap&amp;addon=storefront&amp;page=home&amp;store={$store.userid}"><strong>{$store.name}</strong></a></span> {if $switches.description}<span class="stores-list-welcome"><p><a href="{$classifieds_file_name}?a=ap&amp;addon=storefront&amp;page=home&amp;store={$store.userid}">{$store.desc}</a></p></span>{/if}</td>{/if}
					{if $switches.num_items}<td class="nowrap center">{$store.items}</td>{/if}
					{if $switches.city}<td class="nowrap center">{$store.city}</td>{/if}
					{if $switches.state}<td class="nowrap center">{$store.state}</td>{/if}
					{if $switches.zip}<td class="nowrap center">{$store.zip}</td>{/if}
				</tr>
			{/foreach}
		</table>
	{else}
		<div class="no_results_box">{$text.no_storefronts}</div>
	{/if}
</div>
{if $show_pagination}
	{$pagination}
{/if}
