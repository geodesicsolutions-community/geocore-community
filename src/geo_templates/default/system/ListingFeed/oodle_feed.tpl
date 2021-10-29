{* 6.0.7-3-gce41f93 *}
{if $debug}
	<h1>Oodle Feed DEBUG</h1>
	<textarea rows="30" cols="200">
{/if}
<?xml version="1.0" encoding="{if $charset}{$charset}{else}UTF-8{/if}"?>
<!-- BEGINNING OF OODLE FEED -->
<listings>
	{foreach from=$listings item="listing" name="listingLoop"} 
		{process_listing listing=$listing}
		<listing>
			{if $debug}<debug>DEBUG :: Item {$smarty.foreach.listingLoop.iteration} of {$smarty.foreach.listingLoop.total}</debug>{/if} 
			<category>{if $oodleCatMap[$listing.category]}{$oodleCatMap[$listing.category]}{else}{$defaultOodleCat}{/if}</category>
			<id>{$listing.id}</id>
			<title>{$listing.title|fromDB|strip}</title>
			<url>{capture assign="chunk"}{$classifieds_url}?a=2&amp;b={$listing.id}{/capture}{$chunk|rewriteUrl}</url>
			{if $listing.imageUrl}<image_url>{$listing.imageUrl}</image_url>{/if} 
			<description><![CDATA[
				{* Note: description already filtered in pre-processing *}{$listing.description} 
			]]></description>
			{if $listing.date}<create_time>{$listing.date|date_format:'%Y-%m-%d'}</create_time>{/if} 
			{if $listing.ends}<expire_time>{$listing.ends|date_format:'%Y-%m-%d'}</expire_time>{/if} 
			
			{* The location settings *} 
			{if $listing.location_address}<address><![CDATA[{$listing.location_address|fromDB|replace:']]>':''|strip}]]></address>{/if} 
			{if $listing.location_city}<city><![CDATA[{$listing.location_city|fromDB|replace:']]>':''|strip}]]></city>{/if} 
			{if $listing.location_state}<state><![CDATA[{$listing.location_state|fromDB|replace:']]>':''|strip}]]></state>{/if} 
			{if $listing.location_zip}<zip_code><![CDATA[{$listing.location_zip|fromDB|replace:']]>':''|strip}]]></zip_code>{/if} 
			{if $listing.location_country}<country><![CDATA[{$listing.location_country|fromDB|replace:']]>':''|strip}]]></country>{/if} 
			
			{* Pricing *} 
			{if $listing.buy_now>0.00} 
				<price>{$listing.buy_now}</price>
			{elseif $listing.price>0} 
				<price>{$listing.price}</price>
			{/if} 
		</listing>
	{/foreach} 
</listings>
<!-- END OF OODLE FEED -->
{if $debug}</textarea>{/if}
