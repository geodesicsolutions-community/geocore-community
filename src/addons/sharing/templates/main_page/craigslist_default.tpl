{* 7.5.3-36-gea36ae7 *}
{* IMPORTANT: use only the html tags and attributes specified on http://www.craigslist.org/about/help/html_in_craigslist_postings/details
		Any other tags will be automatically stripped out by craigslist! *}	
<h1>{$listing.title|fromDB}</h1>
<dl>
	<dt><b><big>{$msgs.craigslist_price_label}</big></b></dt>
	<dd>{$listing.price}</dd>
	
	<dt><b><big>{$msgs.craigslist_desc_label}</big></b></dt>
	<dd>{$listing.description|fromDB}</dd>
	
	{if $images}
		<dt><b><big>{$msgs.craigslist_img_label}</big></b></dt>
		<dd>{foreach from=$images item=img}
		<img src="{$img.base}{$img.filename}">
		{/foreach}</dd>
	{/if}
	
</dl>