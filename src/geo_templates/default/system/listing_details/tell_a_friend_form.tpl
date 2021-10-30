{* 7.5.3-36-gea36ae7 *}

<div id="tell-a-friend-form{$listing_id}"{if $ajax_link} style="display: none;"{/if}>
	<div class="contact-result"></div>
	<form action="{$classifieds_file_name}?a=12&amp;b={$listing_id}" method="post" id="tell_a_friend_form{$listing_id}">
		<div class="{cycle values='row_odd,row_even'}">
			<label for="c_friends_name{$listing_id}" class="field_label">{$messages.43}</label>
			<input type="text" name="c[friends_name]" id="c_friends_name{$listing_id}" class="field" />	
		</div>
		<div class="{cycle values='row_odd,row_even'}">
			<label for="c_friends_email{$listing_id}" class="field_label">{$messages.44}</label>
			<input type="text" name="c[friends_email]" id="c_friends_email{$listing_id}" class="field" />	
		</div>
		<div class="{cycle values='row_odd,row_even'}">
			<label for="c_senders_name{$listing_id}" class="field_label">{$messages.45}</label>
			{if $user.user_data&&$user.user_data.firstname}
				{$user.user_data.firstname|fromDB}
			{else}
				<input type="text" name="c[senders_name]" id="c_senders_name{$listing_id}" class="field" />
			{/if}	
		</div>
		<div class="{cycle values='row_odd,row_even'}">
			<label for="c_senders_email{$listing_id}" class="field_label">{$messages.46}</label>
			{if $user.user_data&&$user.user_data.email}
				{$user.user_data.email|fromDB}
			{else}
				<input type="text" name="c[senders_email]" id="c_senders_email{$listing_id}" class="field" />
			{/if}
		</div>
		<div class="{cycle values='row_odd,row_even'}">
			<label for="c_senders_comments{$listing_id}" class="field_label">{$messages.47}</label>
			<textarea name="c[senders_comments]" id="c_senders_comments{$listing_id}" cols="78" rows="7" class="field"></textarea>
		</div>
		{$security_image}
		<div class="center">
			<input type="submit" name="submit" value="{$messages.52}" class="button" />
			<input type="reset" name="reset" value="{$messages.500116}" class="button" />
		</div>
	</form>
	<div class="contact-loading" style="display: none;">
		<br /><br />
		<div class="cntr"><img src="{external file='images/loading.gif'}" alt="" /></div>
		<br /><br />
	</div>
</div>
{if $ajax_link}
	<a href="#" id="tell-a-friend-ajax-link{$listing_id}" onclick="return false;">{$messages.13}</a>
{/if}
{add_footer_html}
	<script>
		var initTellAFriendForm{$listing_id} = function () {
			jQuery('#tell_a_friend_form{$listing_id}').submit(function (e) {
				e.preventDefault();
				var $this = jQuery(this);
				
				var container = jQuery('#tell-a-friend-form{$listing_id}');
				
				var isLightbox = jQuery(document).gjLightbox('isOpen');
				
				var data = $this.serialize();
				
				if(isLightbox) {
					//replace just the submit buttons with the spinner graphic (minimize motion on the lightbox for aesthetics)
					jQuery(gjUtil.lightbox.contents).find('.contact-buttons').hide();
					jQuery(gjUtil.lightbox.contents).find('.contact-loading').show();
				} else {
					//not using a lightbox, so we can replace the whole form with the spinner graphic
					$this.hide('fast');
					container.find('.contact-loading').show('fast');
				}
				
				jQuery.ajax({
					url : '{$classifieds_file_name}?a=12&b={$listing_id}&json=1',
					data : data,
					dataType : 'json',
					type : 'POST'
				}).done(function (response) {
					if(isLightbox) {
						jQuery(gjUtil.lightbox.contents).find('.contact-loading').hide();
					} else {
						container.find('.contact-loading').hide('fast');
					}
					
					if (!response) {
						if(isLightbox) {
							jQuery(gjUtil.lightbox.contents).find('.contact-buttons').show();
							jQuery(gjUtil.lightbox.contents).find('.contact-result').html('<div class="error_message">{$messages.69|escape_js} (invalid response)</div>');
						} else {
							container.find('.contact-result').html('<div class="error_message">{$messages.69|escape_js} (invalid response)</div>');
						}
						if (changeSecurityImage) {
							//reset security image
							changeSecurityImage();
						}
						return;
					}
					
					if (response && !response.success) {
						//show the form again since it was not a success
						if(isLightbox) {
							jQuery(gjUtil.lightbox.contents).find('.contact-buttons').show();
							if(response.message.length > 0) {
								jQuery(gjUtil.lightbox.contents).find('.contact-result').html(response.message).show();
							}
						} else {
							$this.show('fast');
							if(response.message.length > 0) {
								container.find('.contact-result').html(response.message).show();
							}
						}
						
					} else if(response.success) {
						if (response.message) {
							if(isLightbox) {
								jQuery(gjUtil.lightbox.contents).find('.contact-buttons').show();
								jQuery(document).gjLightbox('close');
								gjUtil.addMessage(response.message, 1500);
							} else {
								container.find('.contact-result').html(response.message);
							}
						}
					}
				});
			});
		};
		{if $ajax_link}
			jQuery(function () {
				jQuery('#tell-a-friend-ajax-link{$listing_id}').click(function (e) {
					e.preventDefault();
					initTellAFriendForm{$listing_id}();
					jQuery(document).gjLightbox('open', jQuery('#tell-a-friend-form{$listing_id}').clone(true).show());
				});
			});
		{else}
			jQuery(initTellAFriendForm{$listing_id});
		{/if}
	</script>
	
	{$security_js}
{/add_footer_html}
