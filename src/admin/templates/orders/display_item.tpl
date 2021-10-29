{* 16.09.0-79-gb63e5d8 *}
{$adminMessages}
<fieldset>
	<legend>General Item Info</legend>
<div class="form-horizontal form-label-left">
	<div id ='frm_order_details x_content'>
	
		<div class='form-group'>
		<label class='control-label col-md-5 col-sm-5 col-xs-12'>Item: </label>
		  <div class='col-md-6 col-sm-6 col-xs-12 vertical-form-fix'>
		  #{$item.id}
		  </div>
		</div>
	
		<div class='form-group'>
		<label class='control-label col-md-5 col-sm-5 col-xs-12'>Attached to Order: </label>
		  <div class='col-md-6 col-sm-6 col-xs-12 vertical-form-fix'>
			{if $item.order_id}
				<a href="index.php?page=orders_list_order_details&amp;order_id={$item.order_id}">#{$item.order_id}</a>
				{if $item.orderStatus != 'active'}
					<br />(order status: {$item.orderStatus})
				{/if}
			{else}
				Unknown (no data)
			{/if}
		  </div>
		</div>

		<div class='form-group'>
		<label class='control-label col-md-5 col-sm-5 col-xs-12'>Price Plan Settings: </label>
		  <div class='col-md-6 col-sm-6 col-xs-12 vertical-form-fix'>
		  <a href="{$item.pricePlanUrl}">{$item.pricePlan}</a>
		  </div>
		</div>

		<div class='form-group'>
		<label class='control-label col-md-5 col-sm-5 col-xs-12'>Item Last Modified: </label>
		  <div class='col-md-6 col-sm-6 col-xs-12 vertical-form-fix'>
		  {$item.date|date_format}
		  </div>
		</div>

		<div class='form-group'>
		<label class='control-label col-md-5 col-sm-5 col-xs-12'>User: </label>
		  <div class='col-md-6 col-sm-6 col-xs-12 vertical-form-fix'>
			{if $item.user_id > 0}
				<a href="index.php?page=users_view&amp;b={$item.user_id}">{$item.username}</a>
			{elseif $item.order_id}
				Anonymous
			{else}
				Unknown (No data)
			{/if}
		  </div>
		</div>

		<div class='form-group'>
		<label class='control-label col-md-5 col-sm-5 col-xs-12'>Item Type: </label>
		  <div class='col-md-6 col-sm-6 col-xs-12 vertical-form-fix'>
		  {$item.type}
		  </div>
		</div>

		<div class='form-group'>
		<label class='control-label col-md-5 col-sm-5 col-xs-12'>Status: </label>
		  <div class='col-md-6 col-sm-6 col-xs-12'>
			{if $item.orderStatus === 'active'}
				<div class="rightColumn">
					<span id="item_status{$item.id}">
						<select name="item_status" id="item_status_val{$item.id}" class='form-control col-md-7 col-xs-12'>
							<option value="active"{if $item.status == 'active'} selected="selected"{/if}>Active{if $item.status == "active"} &#42;{/if}</option>
							<option value="pending"{if $item.status == 'pending'} selected="selected"{/if}>Pending{if $item.status == "pending"} &#42;{/if}</option>
							<!-- <option value="edit">Edit Details</option> 
							<option value="pending_alter"{if $item.status == 'pending_alter'} selected="selected"{/if}>Needs Alteration{if $item.status == "pending_alter"} &#42;{/if}</option> -->
							<option value="declined"{if $item.status == 'declined'} selected="selected"{/if}>Declined{if $item.status == "declined"} &#42;{/if}</option>
							<option disabled="disabled"></option>
							<option value="delete">Delete</option>
						</select>
					</span>
					{$item.set_status_link}
					<br />
					<label class="small_font"><input type="checkbox" checked="checked" id="send_email" value="1" /> Send E-Mail Notifications</label>
				</div>
			{else}
				<div class="rightColumn">
					Order Not Active. Cannot Change Item's Status.
				</div>
			{/if}
		  </div>
		</div>
		
	</div>
</div>
</fieldset>
<div class='clearColumn'></div>
{if $itemDetails}
<fieldset>
	<legend>More Details</legend>
	<div>
		{$itemDetails}
	</div>
</fieldset>
<div class='clearColumn'></div>
{/if}