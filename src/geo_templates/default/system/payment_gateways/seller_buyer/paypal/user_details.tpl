{* 7.4.3-35-g6347856 *}
{add_footer_html}
{literal}
<script type="text/javascript">
	//<![CDATA[
	var geoPaypalInplace = {
		update : function () {
			//first, hide the save/cancel buttons
			jQuery('#savePaypal').hide();
			
			var $this = jQuery(this);
			var title = $this.text().replace(/\s/g,'');
			
			//simple method to generate plain text
			var plain = function (txt) {
				return jQuery('<div>').html(txt).text();
			};
			
			//at this point, the title should be clean...  Go ahead and stick it
			//back in so that newlines and stuff are not displayed for as long
			$this.text(title);
			
			var oldTitle = plain($this.data('orig'));
			if (title===oldTitle) {
				//nothing to do
				return;
			}
			
			jQuery.ajax({
				type: 'POST',
				url: 'AJAX.php?controller=UserDetailChange&action=edit',
				data: {
					'value' : title
				}
			}).done(function (response) {
				if (response.error) {
					gjUtil.addError(response.error);
					return;
				}
				
				if (response.email && response.email.length>0) {
					//update the image title displayed (to account for anything trimmed off),
					//and also update the hidden input so it knows when changes are made.
					$this.text(plain(response.email))
						.data('orig',response.email);
					$this.siblings('.media-editable-saved').addClass('media-editable-saved-show');
					//after 1 second hide it again
					setTimeout(function () {
						$this.siblings('.media-editable-saved').removeClass('media-editable-saved-show');
					}, 1000);
				} else {
					$this.text('')
						.data('orig','');
				}
				/* if (response.message) {
					gjUtil.addMessage(response.message);
				} */
				
				if (response.debug) {
					console.log('Debug: '+response.debug);
				}
			}).error(function () {
				//changing title ajax call failed
				gjUtil.addError('server error');
			});
		}
	};
	
	jQuery(function () {
		jQuery('#paypal_id')
			.unbind()
			.data('orig',jQuery('#paypal_id').text().replace(/\s/g,''))
			.on('focus', function () {
				jQuery('#savePaypal').show();
			})
			.on('blur',geoPaypalInplace.update)
			.on('keyup', function (e) {
				if (e.keyCode == 27) {
					//esc key pressed... cancel
					jQuery(this).text(jQuery(this).data('orig'))
						.blur();
				} else if (e.keyCode == 13) {
					//trigger blur event which in turn should save the value
					jQuery(this).blur();
				}
			});
		jQuery('#savePaypal').click(function (e) {
			e.preventDefault();
			jQuery('#paypal_id').blur();
		});
	});
	//]]>
</script>

{/literal}
{/add_footer_html}

<h1 class="subtitle">{$messages.500204}</h1>
<div class="row_even">
	<div id='update_response'>
		<label class="field_label">{$messages.500205}</label>
		<div class="inline" style="position: relative;">
			<div class="media-editable-saved" style="bottom: 0.6em; left: -4em;"><img src="{external file='images/saved-check.png'}" alt="" /></div>
			<div id="paypal_id" class="field" style="min-width: 250px; min-height: 1.4em;" contenteditable="true">{$paypal_id}</div>
		</div>
		<a href="#" id="savePaypal" style="display: none;">{$messages.500216}</a>
	</div>
</div>
