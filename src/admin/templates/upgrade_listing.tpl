{* 16.09.0-79-gb63e5d8 *}
{$error_messages}
<form action='index.php?page=users_restart_ad&amp;b={$listing.id}' method='post' class='form-horizontal'>
	<fieldset>
		<legend>{if $listing.live}Reset{else}Restart &amp; Upgrade Expired{/if} Listing Extras</legend>
		<div class='x_content'>
			<div class="form-group">
				<label class="control-label col-xs-12 col-sm-5">Listing:</label>
				<div class="col-xs-12 col-sm-6 vertical-form-fix">({$listing.id}) {$listing.title|fromDB}</div>
				
			</div>
			<div class="form-group">
				<label class="control-label col-xs-12 col-sm-5">Action:</label>
				<div class="col-xs-12 col-sm-6 vertical-form-fix">
					{if $listing.live}
						<input type='hidden' name='c[live]' value='1' />
						Alter Live 
						{if $listing.item_type == 2}
							{if $listing.auction_type==1}
								Auction
							{elseif $listing.auction_type==2}
								Dutch Auction
							{elseif $listing.auction_type==3}
								Reverse Auction
							{/if}
						{else}
							Classified
						{/if}
					{else}
						<input type='hidden' name='c[live]' value='0' />
						{if $listing.item_type == 2}
							Renew Expired 
							{if $listing.auction_type==1}
								Auction
							{elseif $listing.auction_type==2}
								Dutch Auction
							{elseif $listing.auction_type==3}
								Reverse Auction
							{/if} (creates copy of original)
						{else}
							Renew Expired Classified
						{/if}
					{/if}
				</div>
				
			</div>
			<div class="form-group">
				<label class="control-label col-xs-12 col-sm-5">Start Day</label>
				<div class="col-xs-12 col-sm-6 vertical-form-fix">
					{if $listing.live}
						{html_select_date time=$listing.date field_array='c[date]' prefix='' year_as_text=true} at {html_select_time time=$listing.date field_array='c[date]' prefix='' use_24_hours=0}
					{else}
						{html_select_date field_array='c[date]' prefix='' year_as_text=true} at {html_select_time field_array='c[date]' prefix='' use_24_hours=0}
					{/if}
				</div>
				
			</div>
			{if $listing.item_type == 2}
				<div class="form-group">
					<label class="control-label col-xs-12 col-sm-5">Bidding Starts</label>
					<div class="col-xs-12 col-sm-6 vertical-form-fix">
						{if $listing.live}
							{html_select_date time=$listing.start_time field_array='c[start_time]' prefix='' year_as_text=true} at {html_select_time time=$listing.start_time field_array='c[start_time]' prefix='' use_24_hours=0}
						{else}
							{html_select_date field_array='c[start_time]' prefix='' year_as_text=true} at {html_select_time field_array='c[start_time]' prefix='' use_24_hours=0}
						{/if}
					</div>
					
				</div>
			{/if}
			<div class="form-group">
				<label class="control-label col-xs-12 col-sm-5">New Ending Day</label>
				<div class="col-xs-12 col-sm-6">
					<span id="unlimited-hide-me" {if $listing.ends == 0}style="display: none;"{/if}>
						{if $listing.live}
							{html_select_date time=$listing.ends field_array='c[ends]' prefix='' year_as_text=true} at {html_select_time time=$listing.ends field_array='c[ends]' prefix='' use_24_hours=0}
						{else}
							{html_select_date field_array='c[ends]' prefix='' year_as_text=true} at {html_select_time field_array='c[ends]' prefix='' use_24_hours=0}
						{/if}
					or </span>
					<input type="checkbox" name="c[unlimited_duration]" value="1" {if $listing.ends == 0}checked="checked"{/if} onclick="if(this.checked)jQuery('#unlimited-hide-me').hide(); else jQuery('#unlimited-hide-me').show();" /> Unlimited Duration
				</div>
				
			</div>
			{if $listing.item_type == 2}
				<script type='text/javascript'>{literal}
					function allow_price_change()
					{
						if ($('remove_bids')) {
							if ( $('remove_bids').checked ){
								$('bid_fields').show();
							} else {
								$('bid_fields').hide();
							}
						}
						if ($('buy_now_only')) {
							if ($('buy_now_only').checked) {
								$('not_buy_now_only_fields').hide();
							} else {
								$('not_buy_now_only_fields').show();
							}
						}
					}
					Event.observe(window,'load',function() { 
						if ($('remove_bids')) {
							$('remove_bids').observe('click', function () { allow_price_change(); });
						}
						if ($('buy_now_only')) {
							$('buy_now_only').observe('click', function () { allow_price_change(); });
						}
						allow_price_change();
					});
				{/literal}</script>
				{if $listing.live}
					<div class="form-group">
						<label class="control-label col-xs-12 col-sm-5">Remove Current Bids?</label>
						<div class="col-xs-12 col-sm-6">
							<input type='checkbox' name='c[remove_current_bids]' id='remove_bids' value='1' />
						</div>
						
					</div>
				{else}
					<input type='hidden' name='c[remove_current_bids]' value='1' />
				{/if}
				<div id="bid_fields" style="display: none;">
					<div id="not_buy_now_only_fields">
						<div class="form-group">
							<label class="control-label col-xs-12 col-sm-5">Starting Bid</label>
							<div class="col-xs-12 col-sm-6">
								<input type='text' name='c[starting_bid]' value='{$listing.starting_bid}' class="form-control col-md-7 col-xs-12" />
							</div>
							
						</div>
						<div class="form-group">
							<label class="control-label col-xs-12 col-sm-5">Reserve Price</label>
							<div class="col-xs-12 col-sm-6">
								<input type='text' name='c[reserve_price]' value='{$listing.reserve_price}' class="form-control col-md-7 col-xs-12" />
							</div>
							
						</div>
					</div>
					{if $listing.auction_type != 2}
						<div class="form-group">
							<label class="control-label col-xs-12 col-sm-5">Buy Now Only?</label>
							<div class="col-xs-12 col-sm-6">
								<input type='checkbox' name='c[buy_now_only]' id='buy_now_only' {if $listing.buy_now_only}checked="checked"{/if} value='1' />
							</div>
							
						</div>
						<div class="form-group">
							<label class="control-label col-xs-12 col-sm-5">Buy Now Price</label>
							<div class="col-xs-12 col-sm-6">
								<input type='text' name='c[buy_now]' value='{$listing.buy_now}' class="form-control col-md-7 col-xs-12" />
							</div>
							
						</div>
					{/if}
					<div class="form-group">
						<label class="control-label col-xs-12 col-sm-5">Auction Quantity</label>
						<div class="col-xs-12 col-sm-6">
							<input type='text' name='c[quantity]' value='{$listing.quantity}' class="form-control col-md-7 col-xs-12" />
						</div>
						
					</div>
				</div>
			{/if}
			<div class="form-group">
				<label class="control-label col-xs-12 col-sm-5">Reset Viewed Count</label>
				<div class="col-xs-12 col-sm-6">
					<input type='checkbox' name='c[reset_viewed_count]' value='1' />
				</div>
				
			</div>
			<div class="form-group">
				<label class="control-label col-xs-12 col-sm-5">Featured Listing</label>
				<div class="col-xs-12 col-sm-6">
					<input type='checkbox' {if $listing.featured_ad}checked="checked"{/if} name='c[featured_ad]' value='1' />
				</div>
				
			</div>
			{if $is_ent}
				<div class="form-group">
					<label class="control-label col-xs-12 col-sm-5">Featured Listing Level 2</label>
					<div class="col-xs-12 col-sm-6">
						<input type='checkbox' {if $listing.featured_ad_2}checked="checked"{/if} name='c[featured_ad_2]' value='1' />
					</div>
					
				</div>
				<div class="form-group">
					<label class="control-label col-xs-12 col-sm-5">Featured Listing Level 3</label>
					<div class="col-xs-12 col-sm-6">
						<input type='checkbox' {if $listing.featured_ad_3}checked="checked"{/if} name='c[featured_ad_3]' value='1' />
					</div>
					
				</div>
				<div class="form-group">
					<label class="control-label col-xs-12 col-sm-5">Featured Listing Level 4</label>
					<div class="col-xs-12 col-sm-6">
						<input type='checkbox' {if $listing.featured_ad_4}checked="checked"{/if} name='c[featured_ad_4]' value='1' />
					</div>
					
				</div>
				<div class="form-group">
					<label class="control-label col-xs-12 col-sm-5">Featured Listing Level 5</label>
					<div class="col-xs-12 col-sm-6">
						<input type='checkbox' {if $listing.featured_ad_5}checked="checked"{/if} name='c[featured_ad_5]' value='1' />
					</div>
					
				</div>
			{/if}
			<div class="form-group">
				<label class="control-label col-xs-12 col-sm-5">Better Placement</label>
				<div class="col-xs-12 col-sm-6">
					<input type='checkbox' {if $listing.better_placement}checked="checked"{/if} name='c[better_placement]' value='1' />
				</div>
				
			</div>
			<div class="form-group">
				<label class="control-label col-xs-12 col-sm-5">Bolded</label>
				<div class="col-xs-12 col-sm-6">
					<input type='checkbox' {if $listing.bolding}checked="checked"{/if} name='c[bolding]' value='1' />
				</div>
				
			</div>
			{if $agCheck}
				<script type="text/javascript">
					{literal}
						function ag_show_hide () {
							if ($('ag_check').checked) {
								$('ag_box').show();
							} else {
								$('ag_box').hide();
							}
						}
						Event.observe(window, 'load', function () {
							$('ag_check').observe('click',function () { ag_show_hide(); });
							ag_show_hide();
						});
					{/literal}
				</script>
				<div class="form-group">
					<label class="control-label col-xs-12 col-sm-5">Use Attention Getter</label>
					<div class="col-xs-12 col-sm-6">
						<input type="hidden" name="c[attention_getter]" value="0" />
						<input type='checkbox' name='c[attention_getter]' id='ag_check' value='1' {if $listing.attention_getter}checked="checked"{/if} />
						<div id="ag_box">
							{foreach from=$agChoices item='choice'}
								<div class="ag_choice ag_choice_{$choice.choice_id}" style="display: inline-block; margin: 2px; padding: 1px;">
									<input type='radio' class="ag_radio" name='c[attention_getter_choice]' value="{$choice.choice_id}" 
									{if $choice.value == $listing.attention_getter_url}{$agChecked = $choice.choice_id}checked="checked"{/if} />
									<img src="../{$choice.value}" alt="" />
								</div>
							{/foreach}
						</div>
						<script>
							//draw a small border around the selected attention getter and its radio button
							jQuery('.ag_radio').click(function() {
								jQuery('.ag_choice').each(function() {
									jQuery(this).css('border','none');
								});
								var selected = jQuery(this).val();
								jQuery('.ag_choice_'+selected).css('border','1px solid blue');
							});
							{if $agChecked}
								//listing already has an attention getter selected, so start with it bordered on page load
								jQuery(document).ready(function() {
									jQuery('.ag_choice_{$agChecked}').find('.ag_radio').click();
								});
							{/if}
						</script>
					</div>
					
				</div>
			{/if}
			<div style="text-align: center;"><input type='submit' name='auto_save' value="Save" /></div>
		</div>
	</fieldset>

	<fieldset>
		<legend>Other Actions</legend>
		<div class='x_content'>
			<div class="form-group">
				<label class="control-label col-xs-12 col-sm-5">View Listing's Details</label>
				<div class="col-xs-12 col-sm-6 vertical-form-fix">
					<a href="index.php?page=users_view_ad&amp;b={$listing.id}">View listing #{$listing.id} ({$listing.title|fromDB})</a>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-xs-12 col-sm-5">View User Info</label>
				<div class="col-xs-12 col-sm-6 vertical-form-fix">
					<a href="index.php?page=users_view&amp;b={$listing.seller}">View {$username} ({$listing.seller})</a>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-xs-12 col-sm-5">Inform User of Changes</label>
				<div class="col-xs-12 col-sm-6 vertical-form-fix">
					<a href="index.php?page=admin_messaging_send&amp;b[{$listing.seller}]={$username|escape:url}">Send Message to {$username} ({$listing.seller})</a>
				</div>
			</div>
		</div>
	</fieldset>
</form>