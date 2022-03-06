{* 7.6.3-149-g881827a *}

<div class="sidebar-collapse" style="margin: 0 5px 0 0;">
	<div class="content_box">
		<h1 class="title">{$msgs.usercp_toggle_header}</h1>
		<form id="main_form" action="{$classifieds_file_name}?a=ap&amp;addon=storefront&amp;page=control_panel&amp;action=update&amp;action_type=main" method="post">
			<div class="row_even"><input type="radio" name="data[store_on]" {if $store_is_on}checked="checked"{/if} onclick="jQuery('#main_form').submit();" value="1" /> <strong>{$msgs.usercp_toggle_on}</strong></div>
			<div class="row_odd"><input type="radio" name="data[store_on]" {if !$store_is_on}checked="checked"{/if} onclick="jQuery('#main_form').submit();" value="0" /> <strong>{$msgs.usercp_toggle_off}</strong></div>
			<input type="hidden" name="data[fromPage]" value="{$action_type}" />
			<input type="submit" style="display: none;" /> {* hidden button here to make IE play nice *}
		</form>
		<a href="{$classifieds_file_name}?a=ap&amp;addon=storefront&amp;page=home&amp;store={$user_id}" onclick="window.open(this.href); return false;" class="preview center" style="float: none;">{$msgs.usercp_preview}</a>
	</div>

	<div class="content_box">
		<h2 class="title">{$msgs.usercp_links_header}</h2>
		<ul class="storefront_cp_nav">
			{if $show_traffic}<li class="{cycle values='row_even,row_odd'}"><a href="{$classifieds_file_name}?a=ap&amp;addon=storefront&amp;page=control_panel">{$msgs.usercp_links_stats}</a></li>{/if}
			<li class="{cycle values='row_even,row_odd'}"><a href="{$classifieds_file_name}?a=ap&amp;addon=storefront&amp;page=control_panel&amp;action=display&amp;action_type=customize">{$msgs.usercp_links_customize}</a></li>
			<li class="{cycle values='row_even,row_odd'}"><a href="{$classifieds_file_name}?a=ap&amp;addon=storefront&amp;page=control_panel&amp;action=display&amp;action_type=pages">{$msgs.usercp_links_pages}</a></li>
			{if $show_newsletter}<li class="{cycle values='row_even,row_odd'}"><a href="{$classifieds_file_name}?a=ap&amp;addon=storefront&amp;page=control_panel&amp;action=display&amp;action_type=newsletter">{$msgs.usercp_links_newsletter}</a></li>{/if}			
			<li class="{cycle values='row_even,row_odd'}"><a href="{$classifieds_file_name}?a=28&b=154">{$msgs.usercp_links_help}</a></li>
		</ul>
	</div>

</div>
<div id="content_column_wide">
	<div class="content_box">
		<h1 class="title">{$msgs.usercp_title}</h1>
	</div>
	
	{$success_fail}
	{* it is up to each file that includes this one to close the div after the main contents. *}