{* 16.09.0-79-gb63e5d8 *}
{if !$brief}
<fieldset>
	<legend>Get Order{if $ent} or Recurring Billing{/if}</legend>
	<div id="get-orders">{/if}
		<form action="index.php?page=orders_list_order_details" method="post" class="form-horizontal form-label-left">
			<div class="x_content">
				<div class='form-group'>
					<label class='control-label col-md-4 col-sm-4 col-xs-12'>Order ID # </label>
					<div class='col-md-6 col-sm-6 col-xs-12'>
					    <div class='input-group'>
						  <input type='text' size='8' name='orderId' class='form-control col-md-7 col-xs-12' /> 
						  <span class="input-group-btn"><input type="submit" value="Get Order" class="btn btn-primary" /></span>
					    </div>
					</div>
				</div>
				<div class='form-group'>
					<label class='control-label col-md-4 col-sm-4 col-xs-12'>Invoice ID # </label>
					<div class='col-md-6 col-sm-6 col-xs-12'>
					    <div class='input-group'>
						  <input type='text' size='8' name='invoiceId' class='form-control col-md-7 col-xs-12' /> 
						  <span class="input-group-btn"><input type="submit" value="Get Order" class="btn btn-primary" /></span>
					    </div>
					</div>
				</div>				
				<div class='form-group'>
					<label class='control-label col-md-4 col-sm-4 col-xs-12'>Invoice Transaction ID # </label>
					<div class='col-md-6 col-sm-6 col-xs-12'>
					    <div class='input-group'>
						  <input type='text' size='8' name='transactionId' class='form-control col-md-7 col-xs-12' />  
						  <span class="input-group-btn"><input type="submit" value="Get Order" class="btn btn-primary" /></span>
					    </div>
					</div>
				</div>
			</div>
		</form>
		{if $ent}
			<form action="index.php?page=recurring_billing_details" method="post" class="form-horizontal form-label-left">
				<div class="x_content">
					<div class='form-group'>
						<label class='control-label col-md-4 col-sm-4 col-xs-12'>Recurring Billing Internal ID # </label>
						<div class='col-md-6 col-sm-6 col-xs-12'>
						    <div class='input-group'>
							  <input type='text' size='8' name='id' class='form-control col-md-7 col-xs-12' /> 
							  <span class="input-group-btn"><input type="submit" value="Get Recurring Billing" class="btn btn-primary" /></span>
						    </div>
						</div>
					</div>
					<div class='form-group'>
						<label class='control-label col-md-4 col-sm-4 col-xs-12'>Recurring Billing Gateway ID # </label>
						<div class='col-md-6 col-sm-6 col-xs-12'>
						    <div class='input-group'>
							  <input type='text' size='8' name='altId' class='form-control col-md-7 col-xs-12' /> 
							  <span class="input-group-btn"><input type="submit" value="Get Recurring Billing" class="btn btn-primary" /></span>
						    </div>
						</div>
					</div>
				</div>
			</form>
		{/if}
{if !$brief}
	</div>
</fieldset>{/if}