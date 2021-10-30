{strip}{* 7.6.2-16-g30706bd *}
{if $debug}
	<h1>RSS Listings DEBUG</h1>
	<textarea rows="30" cols="200">
{/if}
<?xml version="1.0" encoding="{if $charset}{$charset}{else}UTF-8{/if}"?>
{/strip}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		{if $atomLink}<atom:link href="{$atomLink}" rel="self" type="application/rss+xml" />{/if} 
		<title><![CDATA[{$title}]]></title>
		<link>{$classifieds_url}</link>
		<description>{$description}</description>
		{if $listings != 0}
			{foreach $listings as $listing name="listingLoop"} 
				{process_listing listing=$listing}
				<item>
					{if $debug}<debug>DEBUG :: Item {$smarty.foreach.listingLoop.iteration} of {$smarty.foreach.listingLoop.total}</debug>{/if} 
					<title><![CDATA[{strip}
						{if $titleCharLimit}
							{$listing.title|fromDB|truncate:$titleCharLimit}
						{else}
							{$listing.title|fromDB}
						{/if}
					{/strip}]]></title>
					{* Send link through filter page to convert URL if applicable *} 
					<link>{capture assign="chunk"}{$classifieds_url}?a=2&amp;b={$listing.id}{/capture}{$chunk|rewriteUrl}</link>
					<guid>{$classifieds_url}?a=2&amp;b={$listing.id}</guid>
					<pubDate>{$listing.date|format_date:'r'}</pubDate>
					<description><![CDATA[
						{if $leadImage && $listing.images.1} 
							{strip}<img src="{$listing.images.1.url}" alt="{$listing.images.1.text|fromDB}"
								{if $listing.images.1.width} width="{$listing.images.1.width}"{/if}{if $listing.images.1.height} height="{$listing.images.1.height}"{/if}
								{if $leadImageFloat} style="float: {$leadImageFloat};"{/if} class='leadImage' />{/strip}
						{/if}
						{foreach $fields as $field => $fieldLabel}
							{if $listing.$field} 
								{if $fieldLabel}<strong>{$fieldLabel}</strong>{/if}
								{if $field == 'price'}
									{* If price, that will already be formatted for us *} 
									{$listing.$field}
								{elseif $field == 'description'}
									{if $descriptionCharLimit} 
										{$listing.description|fromDB|replace:']]>':''|strip_tags|truncate:$descriptionCharLimit}
									{else} 
										{$listing.description|fromDB|replace:']]>':''}
									{/if} 
									<br />
								{elseif $field == 'image'}
									{foreach from=$listing.images item='image'} 
										<img src="{$image.url}" alt="{$image.text|fromDB}"{if $image.width} width="{$image.width}"{/if}{if $image.height} height="{$image.height}"{/if} /> &nbsp; 
									{/foreach}
								{else} 
									{$listing.$field|fromDB}
								{/if} 
								<br />
							{/if}
						{/foreach}
						{if $leadImage && $listing.images.1 && $leadImageFloat} 
							<div style="clear: both;" class="clearLeadImage"></div>
						{/if} 
					]]></description>
				</item>
			{/foreach}
		{else}
			{if $useEmptyItem} 
				<item>
					<title>{$emptyItem.title}</title>
					<link>{if $emptyItem.link == 'detect'}{$classifieds_url}{else}{$emptyItem.link}{/if}</link>
					<pubDate>{$smarty.now|format_date:'r'}</pubDate>
					<description><![CDATA[{$emptyItem.description}]]></description>
				</item>
			{/if}
		{/if}
	</channel>
</rss>
{if $debug}</textarea>{/if}