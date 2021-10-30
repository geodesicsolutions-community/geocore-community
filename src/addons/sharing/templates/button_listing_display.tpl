{* 7.5.3-36-gea36ae7 *}
<a href="{$file_name}?a=ap&amp;addon=sharing&amp;page=main&amp;share={$forListing}" style="vertical-align:top;"><img src="{$shareButtonImage}" alt="" id="share_button" /></a>

{* split the links into two columns *}

{* be very careful when editing the "style" attributes here, as you may disrupt the behaviour of the popup *}
<div id="share_popup" style="display: none; border: 5px solid #CCCCCC; z-index: 50; width: 270px; background-color: #F7F7F7; position: absolute; font-size: 10pt; padding: 0px;">
	<h1 class="title">{$text.popup_title}<span class="share_clickToClose" onclick="doShareClose();">[X]</span></h1>
	
	<div id="share_lists" class="sharing_popup_list_container">
		<ul class="sharing_method_list">
			{foreach $shortLinks as $link}
				{if $link@iteration is odd by 1}
					<li style="margin: 2px 0px;">{$link}</li>
				{/if}
			{/foreach}
		</ul>
		<ul class="sharing_method_list">
			{foreach $shortLinks as $link}
				{if $link@iteration is even by 1}
					<li style="margin: 2px 0px;">{$link}</li>
				{/if}
			{/foreach}
			{if $showMoreLink}
				{* not in use yet... *}
				<li>
					<a href="{$file_name}?a=ap&amp;addon=sharing&amp;page=main&amp;share={$forListing}" title="{$title}"><img src="{$shareButtonImage}" alt="" style="width: 16px; height: 16px;" /></a>
					<a href="{$file_name}?a=ap&amp;addon=sharing&amp;page=main&amp;share={$forListing}" title="{$title}">{$text.shortlink_more}</a>
				</li>
			{/if}
		</ul>
	</div>
</div>
<div id="share_close" style="display: none; position: absolute; z-index: 48;"></div>
{add_footer_html}
<script type="text/javascript">
{literal}
//<![CDATA[
var doShareClose = function() {
	jQuery('#share_close').hide();
	jQuery('#share_popup').hide('fast');	
}
jQuery('#share_button').mouseover(function() {
	
	//get x/y coords of button
	pos = jQuery('#share_button').position();
	
	//move box into position
	jQuery('#share_popup').css({
		left : (pos.left) + 'px',
		top : (pos.top) + 'px'
	}).show('fast', function () {
		//once it's done fading in, show close
		jQuery('#share_close').show();
	}); //fade the box into view
	
	jQuery('#share_lists').show();
	
	//make another, transparent box around the border, for use in detecting when to close
	w = jQuery('#share_popup').width();
	h = jQuery('#share_popup').height();
	jQuery('#share_close').css({
		left : (pos.left - 15) + 'px',
		top : (pos.top - 15) + 'px',
		width : (w + 30) + 'px',
		height : (h + 30) + 'px'
	}).mouseover(doShareClose);
});

//]]>
{/literal}
</script>
{/add_footer_html}
{foreach $social_buttons as $button_tpl}
	{include file="social_buttons/{$button_tpl}"}
{/foreach}

