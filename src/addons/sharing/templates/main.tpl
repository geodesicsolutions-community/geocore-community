{* 7.6.3-78-g04c6c8b *}

<div class="content_box">
	<h2 class="title">{$msgs.listing_box_label}</h2>
	<div style="width: 100%; text-align: center; padding-top: 5px; padding-bottom: 5px;">
		<form action="" method="post" onsubmit="return false;" id="form1">
			{if $shareSpecificListing}
				{* we already know what listing to use *}
				#{$id} -- {$title}
				<input type="hidden" name="listingToShare" id="listingToShare" value="{$id}" />
			{else}
				{* show form to allow user to select one of his listings *}
				
				{$msgs.listing_ddl_label} <select name="share[listing_id]" id="listing_select" class="field">
					<option value="">-</option>
					{foreach from=$listings item=listing}
						<option value="{$listing.id}">#{$listing.id} -- {$listing.title}</option>
					{/foreach}
				</select>
			{/if}
		</form>
	</div>
</div>

<br />

<div class="content_box" id="share_methods_box" style="display: none;">
	<h3 class="title">{$msgs.method_box_label}</h3>
	<div style="width: 100%; text-align: center; padding-top: 5px; padding-bottom: 5px;" id="share_methods"></div>
</div>

<br />

<form action="{$classifieds_file_name}?a=ap&addon=sharing&page=ajax&function=processOptionsForm" method="post" id="options_form" />
<div class="content_box" id="share_options_box" style="display: none;">
	<h2 class="title">{$msgs.options_box_label}</h2>
	
	<div style="width: 100%; text-align: center; padding-top: 5px; padding-bottom: 5px;" id="share_options"></div>
	
</div>
</form>

{include file='js.tpl'}