{* 7.3beta5-58-g1c7639d *}
<div class="listing_extra_item clearfix">
	<div class="listing_extra_cost">
		<div id="twitter_feed_input_container">
			<textarea name="c[twitter_feed_code]" id="twitter_feed_input" class="field" cols="50"></textarea>
			<div id="twitter_feed_loading_message" style="display: none;">{$addon_text.loading_message}</div>
			<div id="twitter_feed_parse_error" style="display: none;">{$addon_text.parse_error}</div>
		</div>
		<div id="twitter_feed_results_container" style="display: none;">
			<input type="hidden" name="c[twitter_feed_href]" id="twitter_feed_href" value="{$prevalue_href}" />
			<input type="hidden" name="c[twitter_feed_data_id]" id="twitter_feed_data_id" value="{$prevalue_data_id}" />
			{if $prevalue_href && $prevalue_data_id}
				{* starting with a widget already set! *}
				{add_footer_html}
					<script type="text/javascript">
						jQuery('#twitter_feed_input').val('');
						jQuery('#twitter_feed_input_container').hide();
						jQuery('#twitter_feed_results_container').show();
					</script>
				{/add_footer_html}
			{/if}
			<div id="twitter_feed_result">{$addon_text.saved_message}</div>
			<div id="twitter_feed_clear_btn"><a href="#" class="button" onclick="twitterFeedReleaseCode(); return false;">{$addon_text.release_btn}</a></div>
		</div>
	</div>
	<br />
	{$addon_text.widget_code_label}
	<a href="show_help.php?addon=twitter_feed&amp;auth=geo_addons&amp;textName=widget_help_box" class="lightUpLink" onclick="return false;"><img src="{external file=$helpIcon}" alt="" /></a>
	<br />
	{if $error}<span class="error_message">{$error}</span>{/if}
	<br />
</div>
