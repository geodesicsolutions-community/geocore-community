{* 7.5.3-36-gea36ae7 *}

<div id="contact-seller-form{$listing_id}"{if $ajax_link} style="display: none;"{/if}>
	<div class="contact-result"></div>

	<form action="{$classifieds_file_name}?a=13&amp;b={$listing_id}" method="post" id="contact_seller_form{$listing_id}">
		<div class="{cycle values='row_even,row_odd'}">
			<label class="field_label">{$messages.55}</label>
			<strong class="text_highlight">{$seller.username}</strong>
		</div>
		<div class="{cycle values='row_even,row_odd'}">
			<label for="c_senders_name{$listing_id}" class="field_label">{$messages.1366}</label>
			<input type="text" name="c[senders_name]" id="c_senders_name{$listing_id}" value="{if $user.user_data}{$user.user_data.firstname|fromDB}{/if}" class="field" />	
		</div>
		
		<div class="{cycle values='row_even,row_odd'}">
			<label for="c_senders_email{$listing_id}" class="field_label">{$messages.57}</label>
			<input type="text" name="c[senders_email]" id="c_senders_email{$listing_id}" value="{if $user.user_data}{$user.user_data.email|fromDB}{/if}" class="field" />	
		</div>

		<div class="{cycle values='row_even,row_odd'}">
			<label for="c_senders_phone{$listing_id}" class="field_label">{$messages.1512}</label>
			<input type="text" name="c[senders_phone]" id="c_senders_phone{$listing_id}" value="{if $user.user_data}{$user.user_data.phone|fromDB}{/if}" class="field" />
		</div>
		
		{if $canAskPublicQuestion}
			<div class="{cycle values='row_even,row_odd'}">
				<label for="c_public_question{$listing_id}" class="field_label">{$messages.500890}</label>
				<select name="c[public_question]" class="field" id="c_public_question{$listing_id}">
					<option value="0">{$messages.500891}</option>
					<option value="1">{$messages.500892}</option>
				</select>
			</div>
		{/if}	
		
		<div class="{cycle values='row_even,row_odd'}">
			<label for="c_senders_comments{$listing_id}" class="field_label">{$messages.58}</label>
			<textarea name="c[senders_comments]" id="c_senders_comments{$listing_id}" cols="78" rows="7" class="field">{$values.comment}</textarea>	
		</div>
		{$security_image}
		<div class="center contact-buttons">
			<input type="submit" name="submit" value="{$messages.60}" class="button" />
			<input type="reset" name="reset" value="{$messages.500115}" class="button" />
		</div>
	</form>
	<div class="contact-loading" style="display: none;">
		<br /><br />
		<div class="cntr"><img src="{external file='images/loading.gif'}" alt="" /></div>
		<br /><br />
	</div>
</div>
{if $ajax_link}
	<a href="#" id="contact-seller-ajax-link{$listing_id}" onclick="return false;">{$messages.14}</a>
{/if}
{add_footer_html}
	<script>
		var initContactSellerForm{$listing_id} = function () {
			jQuery('#contact_seller_form{$listing_id}').unbind().submit(function (e) {
				e.preventDefault();
				var $this = jQuery(this);
				
				var container = jQuery('#contact-seller-form{$listing_id}');
				
				var data = $this.serialize();
				
				var isLightbox = jQuery(document).gjLightbox('isOpen');
				
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
					url : '{$classifieds_file_name}?a=13&b={$listing_id}&json=1',
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
				jQuery('#contact-seller-ajax-link{$listing_id}').click(function (e) {
					e.preventDefault();
					initContactSellerForm{$listing_id}();
					jQuery(document).gjLightbox('open', jQuery('#contact-seller-form{$listing_id}').clone(true).show());
				});
			});
		{else}
			jQuery(initContactSellerForm{$listing_id});
		{/if}
			
	</script>
	
	{$security_js}
{/add_footer_html}
