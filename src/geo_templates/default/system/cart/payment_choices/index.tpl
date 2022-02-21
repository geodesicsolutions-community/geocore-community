{* 17.03.0-2-g0fa0a27 *}

{include file="system/cart/cart_steps.tpl"}

{foreach from=$error_msgs item=err_msg}
	<div class="field_error_box">
		{$err_msg}
	</div>
{/foreach}
<form action="{$process_form_url}" autocomplete="on" method="post" id="payment-form">
	<div id="cart_left_column">
		<div class="content_box">
			<h2 class="title">{$messages.500266}</h2>
			<p class="page_instructions">{$messages.500267}</p>

			{if $error_msgs.billing_email}
				<div class='field_error_box'>{$error_msgs.billing_email}</div>
			{/if}

			<div class="row_even">
				<label for="firstname" class="field_label">{$messages.500268}</label>
				<input id="firstname" name='c[firstname]' value='{if $cart.billing_info.firstname}{$cart.billing_info.firstname}{elseif $populate_billing_info}{$cart.firstname}{/if}' class="field">
			</div>

			<div class="row_odd">
				<label for="lastname" class="field_label">{$messages.500269}</label>
				<input id="lastname" name='c[lastname]' value='{if $cart.billing_info.lastname}{$cart.billing_info.lastname}{elseif $populate_billing_info}{$cart.lastname}{/if}' class="field">
			</div>

			<div class="row_even">
				<label for="address" class="field_label">{$messages.500270}</label>
				<input id="address" name='c[address]' value='{if $cart.billing_info.address}{$cart.billing_info.address}{elseif $populate_billing_info}{$cart.address}{/if}' class="field">
			</div>

			<div class="row_odd">
				<label for="address_2" class="field_label">{$messages.500271}</label>
				<input id="address_2" name='c[address_2]' value='{if $cart.billing_info.address_2}{$cart.billing_info.address_2}{elseif $populate_billing_info}{$cart.address_2}{/if}' class="field">
			</div>

			<div class="row_even">
				<label for="city" class="field_label">{$messages.500272}</label>
				<input id="city" name='c[city]' value='{if $cart.billing_info.city}{$cart.billing_info.city}{elseif $populate_billing_info}{$cart.city}{/if}' class="field">
			</div>

			<div class="row_odd">
				<label class="field_label">{$messages.500273}</label>
				{$countries}
			</div>

			<div class="row_even" id="billing_state_wrapper">
				<label class="field_label">{$messages.500274}</label>
				{$states}
			</div>

			<div class="row_odd">
				<label for="zip" class="field_label">{$messages.500275}</label>
				<input id="zip" name='c[zip]' value='{if $cart.billing_info.zip}{$cart.billing_info.zip}{elseif $populate_billing_info}{$cart.zip}{/if}' class="field">
			</div>

			<div class="row_even">
				<label for="phone" class="field_label">{$messages.500276}</label>
				<input id="phone" type="tel" name='c[phone]' value='{if $cart.billing_info.phone}{$cart.billing_info.phone}{elseif $populate_billing_info}{$cart.phone}{/if}' class="field">
			</div>

			<div class="row_odd">
				<label for="email" class="field_label">{$messages.500277}</label>
				<input id="email" type="email" name='c[email]' value='{if $cart.billing_info.email}{$cart.billing_info.email}{elseif $populate_billing_info}{$cart.email}{/if}' class="field">
			</div>
		</div>
	</div>

	<div id="cart_right_column">
		<div class="content_box">
			<h1 class="title">{$messages.500264}</h1>
			<p class="page_instructions">{$order_summary_desc}</p>

			<div class="box_pad clearfix">
				{include file='system/cart/display_cart/index.tpl' view_only=1}
			</div>
		</div>
	</div>

	<div class="clearfix"><br /></div>

	<div class="content_box">
		<h3 class="title">{$messages.500278}</h3>
		<p class="page_instructions">{$messages.500279}</p>
		{if $no_free_cart}
			<p class="page_instructions">
				{$messages.500629}
			</p>
		{/if}
		{if $errors.choices_box}
			<div class="field_error_box">
				{$errors.choices_box}
			</div>
		{/if}

		<div id="payment_choices">
			{foreach from=$payment_choices key=index item=payment_choice}
				{if !isset($force_use_gateway) || $force_use_gateway == $index || !isset($payment_choices.$force_use_gateway)}
					{if $payment_choice.choices_box}
						{$payment_choice.choices_box}
					{else}
						{include file="system/cart/payment_choices/gateway_box.tpl"}
					{/if}
				{/if}
			{foreachelse}
				{$messages.500280}
			{/foreach}
		</div>

		<div class="center">
			<input type="submit" value="{$messages.500399}" class="button"><br /><br />
			<a href="{$cart_url}&amp;step=cart" class="button">{$messages.500281}</a>
		</div>
	</div>
</form>
