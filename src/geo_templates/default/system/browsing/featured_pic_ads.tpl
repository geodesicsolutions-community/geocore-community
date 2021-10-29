{* 7.5.3-125-gf5f0a9a *}

{$category_cache}
	
<br />

{if $show_classifieds}
	<div class="content_box">
		<h3 class="title">{$messages.874} {$current_category_name}</h3>
	</div>
	{if $no_classifieds}
		<div class="note_box">{$no_classifieds}</div>
	{else}
		<table class="featured_items">
			{foreach from=$classified_result.listings item=l key=id name=class}
				{if $smarty.foreach.class.index % $classified_result.column_count == 0}<tr>{/if}
					<td style="width: {$classified_result.column_width}" class="element">
						{$l.thumbnail}
						<a style="width: {$classified_result.column_width}" href="{$classifieds_file_name}?a=2&amp;b={$id}" {if $classified_result.popup}onclick="window.open(this.href,'_blank','width={$classified_result.popup_width},height={$classified_result.popup_height},scrollbars=1,location=0,menubar=0,resizable=1,status=0'); return false;"{/if}>
							<span class="featured_title">{$l.title}</span>
							<span class="price">{$l.price}</span>
						</a>
					</td>
				{if ($smarty.foreach.class.index % $classified_result.column_count == ($classified_result.column_count - 1)) or $smarty.foreach.class.last}</tr>{/if}
			{/foreach}
		</table>
	{/if}
	<br />
{/if}

{if $show_auctions} 
	<div class="content_box">
		<h3 class="title">{$messages.100874} {$current_category_name}</h3>
	</div>

	{if $no_auctions} 
		<div class="note_box">{$no_auctions}</div>
	{else} 
		<table class="featured_items">
			{foreach from=$auction_result.listings item=l key=id name=auc} 
				{if $smarty.foreach.auc.index % $auction_result.column_count == 0}<tr>{/if} 
					<td style="width: {$auction_result.column_width}" class="element">
						{$l.thumbnail}
						<a style="width: {$auction_result.column_width}" href="{$classifieds_file_name}?a=2&amp;b={$id}" {if $auction_result.popup}onclick="window.open(this.href,'_blank','width={$auction_result.popup_width},height={$auction_result.popup_height},scrollbars=1,location=0,menubar=0,resizable=1,status=0'); return false;"{/if}>
							<span class="featured_title">{$l.title}</span>
							<span class="price">{$l.price}</span>
						</a>
					</td>
				{if ($smarty.foreach.auc.index % $auction_result.column_count == ($auction_result.column_count - 1)) or $smarty.foreach.auc.last}</tr>{/if}
			{/foreach}
		</table>
	{/if}
{/if}

{if $pagination}
	{$messages.871} {$pagination}
{/if}
