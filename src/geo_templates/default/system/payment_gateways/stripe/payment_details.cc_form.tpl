{* 17.10.0-4-g98652d0 *}

<span class="nowrap">
	<label for="cc_number" class="inline">{$messages.500295}</label>
	<input type="text" id="cc_number" data-stripe="number" size="20" class="field" autocomplete="off" />
</span>

{if $use_cvv2}
	<span class="nowrap">
		<label for="cvv2_code" class="inline"><a href="{external file='images/cvv2_code.gif'}" class="lightUpImg">{$messages.500296}</a></label>
		<input type="text" id="cvv2_code" data-stripe="cvc" size="4" class="field" autocomplete="off" />
	</span>
{/if}

<span class="nowrap">
	<label class="inline">{$messages.500297}</label>
	<select data-stripe="exp_month" class="field">
		{section name='exp_month' loop='13' start='1'}
			{$month = $smarty.section.exp_month.index|string_format:"%02d"}
			<option value="{$month}"{if $smarty.now|date_format:"%m" == $month} selected="selected"{/if}>{$month}</option>
		{/section}
	</select> / <select data-stripe="exp_year" class="field">
		{$startYear = $smarty.now|date_format:'%y'}
		{section name='exp_year' loop=$startYear+13 start=$startYear}
			{$year = $smarty.section.exp_year.index|string_format:"%02d"}
			<option value="{$year}"{if $startYear == $year} selected="selected"{/if}>{$year}</option>
		{/section}
	</select>
</span>


{if $error_msgs.cc_result_message}
	<div class="error_message">{$error_msgs.cc_result_message}</div>
{/if}
{if $error_msgs.cc_number}
	<div class="error_message">{$error_msgs.cc_number}</div>
{/if}
{if $error_msgs.cvv2_code}
	<div class="error_message">{$error_msgs.cvv2_code}</div>
{/if}
{if $error_msgs.exp_date}
	<div class="error_message">{$error_msgs.exp_date}</div>
{/if}
<div class="error_message" id="async_result" style="display: none;"></div>

{add_footer_html}
	{* use stripe.js to convert data from browser into a token -- this way, CC data never hits our server, so less worrying about PCI and stuff! *}
	<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
	<script type="text/javascript">	
		Stripe.setPublishableKey('{$stripe_public_key}');
	</script>
	<script>
		$trigger = jQuery('#stripe');
		$form = jQuery('#payment-form');
		jQuery(document).ready(function() {
			jQuery("input[type=radio]").change(function() { //watch all radio buttons
				if($trigger.prop('checked')) {
					//trigger button clicked -- modify form submission
					$form.on('submit',function(){
						jQuery('input[type=submit]').prop('disabled',true); //turn off submit button
						Stripe.card.createToken($form, stripeResponseHandler); //make a Stripe token and pass it to the handler
						return false; //prevent default form function
					});
				} else {
					//different gateway picked, so return the form submission to normal
					$form.off('submit');
				}
			});
			$trigger.change(); //run once when page loads
		});
		
		function stripeResponseHandler(status, response) {
			var $form = jQuery('#payment-form');
			if (response.error) {
		  		// Show the errors on the form:
		    	jQuery('#async_result').text(response.error.message).show();
		    	jQuery('input[type=submit]').prop('disabled',false); // Re-enable submission
			} else { 
				//token is good
		    	var token = response.id;
		    	//append token to form data		
		    	$form.append(jQuery('<input type="hidden" name="stripeToken">').val(token));
				//kill the async token-creator listener, because we now want to actually submit the form
				$form.off('submit');
				//submit the form
		        $form.submit();
			}
		};
	</script>
{/add_footer_html}