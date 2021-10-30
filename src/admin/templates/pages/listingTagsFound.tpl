{* 16.09.0-79-gb63e5d8 *}

{capture assign=found}<span class="color-primary-one"><strong>Found</strong></span>{/capture}
{capture assign=not_found}<span style="color:#ff0000;"><strong>Not Found</strong></span>{/capture}

<fieldset>
	<legend>Tag Verification Tool</legend>
	<div class="table-responsive">
		<p class="page_note">
			<strong>How it works:</strong>  The Tag Verification Tool helps you 
			to verify the existence of template tags (item within brackets) 
			that can be used within each of the templates you have chosen above. 
			When these tags are used within their respective template, the tag 
			will display information by pulling it directly from the database. 
			In most cases, these tags will simply display a "label" name that 
			you have defined, and a "value" name that the seller defines when 
			placing their listing. The system automatically searches the 
			templates you have assigned at the top of this page and returns a 
			result for each tag. The search result will either be {$found} or
			{$not_found}.  If there is a tag that displays a {$not_found}
			result, but you want it to show up within the Listing Display Page, 
			simply insert that tag into its appropriate template. 
			<br />
			<strong>Important:</strong> When placing these tags in the HTML of 
			your template, for certain fields you must also go to the 
			<strong>Listing Setup > Fields to Use</strong> page of this admin
			and configure each of them to be used.
		</p>
		<table cellpadding="0" cellspacing="0" class="table table-hover table-striped table-bordered" >
			{foreach from=$tags key=tagType item=theseTags}
				<thead>
				<tr class="col_hdr_top">
					<td style="padding-right: 5px; text-align: right;">Available Tags</td>
					<td>Tag Description</td>
					{foreach from=$templates item=template key=file}
						<td class="center" style="padding-right: 15px;" title="{$file|escape}">
							In Template:<br />
							{$file|truncate:15:'...'}
						</td>
					{/foreach}
				</tr>
				</thead>

				<tr>
					<td class="col_hdr" colspan="100%">
						{if $tagType=='auctions'}
							Auction Specific Tags (used only on Auctions)
						{elseif $tagType=='seller'}
							Seller Information Tags (used on classifieds or auctions)
						{elseif $tagType=='questions'}
							Extra Question Tags (used on classifieds or auctions)
						{elseif $tagType=='checkboxes'}
							CheckBox Tags (used on classifieds or auctions)
						{else}
							Page Specific Tags
						{/if}
					</td>
				</tr>
				{foreach $theseTags as $tag => $desc}
					<tr class="{cycle values='row_color1,row_color2'}">
						<td class="right" style="font-weight: bold; padding-right: 5px; white-space: nowrap;">
							{if $use_listing_tag}
								{if $desc.type=='tag'}
									{ldelim}listing tag='{$tag}'{rdelim}
								{elseif $desc.type=='field'}
									{ldelim}listing field='{$tag}'{rdelim}
								{else}
									{ldelim}${$tag}{rdelim}
								{/if}
							{else}
								{ldelim}${$tag}}
							{/if}
						</td>
						<td class="small_font">
							{if $use_listing_tag}
								{$desc.desc}
							{else}
								{$desc}
							{/if}
						</td>
						{foreach from=$templates item=template key=file}
							<td class="center">
								{if $template.tags.$tag}
									{$found}
								{else}
									{$not_found}
								{/if}
							</td>
						{/foreach}
					</tr>
				{/foreach}
			{/foreach}
		</table>
	</div>
</fieldset>