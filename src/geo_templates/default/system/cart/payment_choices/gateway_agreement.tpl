{* 6.0.7-3-gce41f93 *}

<div class='payment_text payment_choices_field_labels'>
	<label><input type="checkbox" name="c[user_agreement]" value="{$payment_choice.radio_value}" /> {$payment_choice.user_agreement.label}</label>
	{if $payment_choice.user_agreement.text}
		<div class="agreement_text_box">{$payment_choice.user_agreement.text}</div>
	{/if}
</div>
