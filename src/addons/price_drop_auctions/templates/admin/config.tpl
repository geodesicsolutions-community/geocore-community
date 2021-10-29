{* 16.09.0-99-gba74dac *}
{$admin_msgs}
<form action="" method="post" class="form-horizontal">
	<fieldset>
		<legend>Price Drop Auctions</legend>
		<div>
			<p class="page_info">With this addon enabled, sellers of Buy Now Only Auctions will be able to select an option to activate Price Dropping.<br />
			<br />
			If selected, the auction will automatically lower its price as time passes without it being purchased. These drops will occur at random times and will reduce the price by random amounts, but those random values may be constrained here.<br />
			<br />
			Prefer not to use random variations? Set the "At least" and "At most" fields to the same value.</p>
			<div class='form-group'>
				<label class="control-label col-xs-12 col-sm-5">Delay between price drops</label>
				<div class="col-xs-12 col-sm-6">
					<div class="input-group">
						<div class="input-group-addon">At least</div>
						<input type="number" name="delay_low" value="{$delay_low}" step="any" class="form-control" />
						<div class="input-group-addon">hours</div>
					</div>
					<div class="input-group">
						<div class="input-group-addon">At most</div>
						<input type="number"name="delay_high" value="{$delay_high}" step="any" class="form-control" />
						<div class="input-group-addon">hours</div>
					</div>
				</div>
			</div>
			<div class='form-group'>
				<label class="control-label col-xs-12 col-sm-5">Percentage of starting price to decrease by</label>
				<div class="col-xs-12 col-sm-6">
					<div class="input-group">
						<div class="input-group-addon">At least</div>
						<input type="number" min="0" max="100" name="drop_amount_low" value="{$drop_amount_low}" {if $drop_amount_static == 1}disabled="disabled"{/if} class="amount form-control" step="any" />
						<div class="input-group-addon">percent</div>
					</div>
					<div class="input-group">
						<div class="input-group-addon">At most</div>
						<input type="number" min="0" max="100" name="drop_amount_high" value="{$drop_amount_high}" {if $drop_amount_static == 1}disabled="disabled"{/if} class="amount form-control" step="any" />
						<div class="input-group-addon">percent</div>
					</div>
					Or: <input type="checkbox" name="drop_amount_static" value="1" onclick="jQuery('.amount').prop('disabled', this.checked);" {if $drop_amount_static == 1}checked="checked"{/if} /> decrease by an amount based on listing duration, so that the minimum price is always reached
				</div>
			</div>
			<div class="center"><input type="submit" value="Save" name="auto_save" /></div>
		</div>
	</fieldset>
</form>