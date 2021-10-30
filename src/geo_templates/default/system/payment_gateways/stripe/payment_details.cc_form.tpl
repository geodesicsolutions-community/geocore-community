{* @git-info@ *}

<section class="stripe-inputs">
	<div class="stripe-inputs__input">
		<label for="stripe-card-number">{$messages.500295}</label>
		<div id="stripe-card-number" class="field field--stripe-number"></div>
	</div>
	<div class="stripe-inputs__input">
		<label for="stripe-card-cvc"><a href="{external file='images/cvv2_code.gif'}" class="lightUpImg">{$messages.500296}</a></label>
		<div id="stripe-card-cvc" class="field field--stripe-cvc"></div>
	</div>
	<div class="stripe-inputs__input">
		<label for="stripe-card-expiry">{$messages.500297}</label>
		<div id="stripe-card-expiry" class="field field--stripe-expiry"></div>
	</div>
</section>

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
	<script type="text/javascript" src="https://js.stripe.com/v3/"></script>
	<script>
		jQuery(document).ready(function() {
			var $trigger = jQuery('#stripe');
			var $form = jQuery('#payment-form');
			var stripeInit = false;
			var stripe = Stripe('{$stripe_public_key}');
			var clientSecret = '{$client_secret}';
			var cardNumber = null;

			function initStripe() {
				if (stripeInit) {
					return;
				}
				stripeInit = true;
				var elementStyles = {
					base: {
						color: '#32325D',
						fontWeight: 500,
						fontFamily: 'Source Code Pro, Consolas, Menlo, monospace',
						fontSize: '16px',
						fontSmoothing: 'antialiased',
						backgroundColor: 'transparent',

						'::placeholder': {
							color: '#CFD7DF',
						},
						':-webkit-autofill': {
							color: '#e39f48',
						},
					},
					invalid: {
						color: '#E25950',

						'::placeholder': {
							color: '#FFCCA5',
						},
					},
				};
				var elementClasses = {
					focus: 'focus',
					empty: 'empty',
					invalid: 'invalid',
				};


				var elements = stripe.elements({
					locale: 'auto'
				});
				cardNumber = elements.create('cardNumber', {
					style: elementStyles,
					classes: elementClasses
				});
				cardNumber.mount('#stripe-card-number');

				var cardExpiry = elements.create('cardExpiry', {
					style: elementStyles,
					classes: elementClasses,
				});
				cardExpiry.mount('#stripe-card-expiry');

				var cardCvc = elements.create('cardCvc', {
					style: elementStyles,
					classes: elementClasses,
				});
				cardCvc.mount('#stripe-card-cvc');
			}

			function billingInfo() {
				// stripe does not like "empty" values so trim everything and unset anything empty
				var parseProperties = function(obj) {
					Object.keys(obj).forEach(function(key) {
						if (obj[key] && typeof obj[key] === 'object') {
							parseProperties(obj[key]);
							if (Object.keys(obj[key]).length === 0) {
								delete obj[key];
							}
							return;
						}
						// handle falsish values like null or undefined
						obj[key] = obj[key] || '';
						// trim
						obj[key] = obj[key].trim();
						// delete if empty
						if (!obj[key].length) {
							delete obj[key];
						}
					});
					return obj;
				};
				return parseProperties({
					address: {
						city: jQuery('#city').val(),
						// relies on billing region using c for name...
						country: jQuery('#c_country_ddl').val(),
						line1: jQuery('#address').val(),
						line2: jQuery('#address_2').val(),
						postal_code: jQuery('#zip').val(),
						// relies on billing region using c for name...
						state: jQuery('#c_state_ddl').val()
					},
					name: jQuery('#firstname').val() + ' ' + jQuery('#lastname').val(),
					email: jQuery('#email').val(),
					phone: jQuery('#phone').val()
				});
			}

			function showError(msg) {
				jQuery('#async_result').text(msg).show();
			}

			function hideError() {
				jQuery('#async_result').hide();
			}

			function disableSubmit() {
				jQuery('input[type=submit]').prop('disabled',true);
			}

			function enableSubmit() {
				jQuery('input[type=submit]').prop('disabled',false);
			}

			// watch all radio buttons
			jQuery("input[type=radio]").change(function() {
				if ($trigger.prop('checked')) {
					//trigger button clicked -- modify form submission
					initStripe();
					$form.on('submit',function(){
						disableSubmit();
						// if any errors still show, hide them to avoid confusion
						hideError();
						stripe.confirmCardPayment(clientSecret, {
							payment_method: {
								card: cardNumber,
								billing_details: billingInfo()
							}
						}).then(function (response) {
							if (response.error) {
								// Show the errors on the form:
								showError(response.error.message);
								// Re-enable submission
								enableSubmit();
							} else {
								// payment is good, submit to let back end record payment

								// kill the async token-creator listener, because we now want to actually submit the form
								$form.off('submit');
								// submit the form
								$form.submit();
							}
						});
						// prevent default form function
						return false;
					});
				} else {
					//different gateway picked, so return the form submission to normal
					$form.off('submit');
				}
			});
			$trigger.change(); //run once when page loads
		});
	</script>
{/add_footer_html}
