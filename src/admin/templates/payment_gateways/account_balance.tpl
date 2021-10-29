{* 6.0.7-3-gce41f93 *}
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Allow Positive Balance
		</div>
		<div class='rightColumn'>
			<input type='hidden' name='{$payment_type}[allow_positive]' value='0' />
			<input type='checkbox' name='{$payment_type}[allow_positive]' value='1' {$positive_check} />
		</div>
		<div class='clearColumn'></div>
	</div>
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Allow Negative Balance
		</div>
		<div class='rightColumn'>
			<input type='hidden' name='{$payment_type}[allow_negative]' value='0' />
			<input type='checkbox' name='{$payment_type}[allow_negative]' value='1' {$negative_check} />
		</div>
		<div class='clearColumn'></div>
	</div>
	<div id='negative_balance_settings'>
		<div class='row_color{cycle values="1,2"}'>
			<div class='leftColumn'>
				Negative Balance Time Cutoff
			</div>
			<div class='rightColumn'>
				<label><input type='text' name='{$payment_type}[negative_time]' value='{$negative_time}' size='4' /> days</label>
			</div>
			<div class='clearColumn'></div>
		</div>
		<div class='row_color{cycle values="1,2"}'>
			<div class='leftColumn'>
				Negative Balance $ Cutoff
			</div>
			<div class='rightColumn'>
				<label>{$precurrency}<input type='text' name='{$payment_type}[negative_max]' value='{$negative_max}' size='4' />{$postcurrency}</label>
			</div>
			<div class='clearColumn'></div>
		</div>
	</div>
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Min. Add to Balance
		</div>
		<div class='rightColumn'>
			<label>{$precurrency}<input type='text' name='{$payment_type}[min_add_to_balance]' value='{$min_add}' size='4' />{$postcurrency}</label>
		</div>
		<div class='clearColumn'></div>
	</div>
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Force use
		</div>
		<div class='rightColumn'>
			<input type='checkbox' name='{$payment_type}[force_use]' value='1' {$force_check} />
		</div>
		<div class='clearColumn'></div>
	</div>
	{if $finalFees}
		<div class='row_color{cycle values="1,2"}'>
			<div class='leftColumn'>
				Auto-charge Auction Final Fees
			</div>
			<div class='rightColumn'>
				<input type="checkbox" id="{$payment_type}[charge_final_fees]" name="{$payment_type}[charge_final_fees]" value="1" {if $charge_final_fees}checked="checked"{/if}
					onclick="if (this.checked) $('{$payment_type}use_no_free_cart').show(); else $('{$payment_type}use_no_free_cart').hide();" />
			</div>
			<div class='clearColumn'></div>
			<div id='{$payment_type}use_no_free_cart'{if !$charge_final_fees} style='display: none;'{/if}>
				<div class='leftColumn'>
					Allow on Free Order
				</div>
				<div class='rightColumn'>
					<input type="checkbox" name="{$payment_type}[use_no_free_cart]" value="1" {if $use_no_free_cart}checked="checked"{/if} />
				</div>
				<div class='clearColumn'></div>
			</div>
		</div>
	{/if}