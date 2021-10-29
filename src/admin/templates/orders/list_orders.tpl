{* 16.09.0-79-gb63e5d8 *}

<script type="text/javascript">
jQuery(function () {
	jQuery('#checkAllOrders').click(function() {
		jQuery('.orderCheckbox').prop('checked',this.checked);
	});
});

</script>

<fieldset>
	<legend>Manage Orders</legend>
	<div class='x_content'>
		<div class="page_note">
			<strong>Note:</strong> Order approval is only for whether payment has been received.
			To allow/deny a specific item in an order, that has to be done after the order as 
			a whole has been approved.
		</div>
		<div id="narrowShowBox" style="margin:5px;">
			<i class="fa fa-filter"> </i> Chart Filters <a href="#" onclick="$('narrowShowBox').hide(); $('narrowChangeBox').appear(); return false;">[Edit]</a>: 

			<div style="white-space: nowrap; display: inline; margin-right: 3px">
			<span class="label label-warning">Status: 
				{if $narrow_order_status=='pending'}
					Pending Payment
				{elseif $narrow_order_status=='pending_admin'}
					Pending
				{else}
					{$narrow_order_status|capitalize}
				{/if}
				</span>
			</div>
			
			<div style="white-space: nowrap; display: inline; margin-right: 3px">
			<span class="label label-warning">Gateway: {$narrow_gateway_type|capitalize}</span>
			</div>

			{if $date.low||$date.high}
			<div style="white-space: nowrap; display: inline; margin-right: 3px">	
				<span class="label label-info">From: {if !$date.low}Beginning{else}{$date.low|escape}{/if}</span>
				<span class="label label-info">To: {if !$date.high}Now{else}{$date.high|escape}{/if}</span>
			</div>
			{/if}
			
			{if $narrow_username}
			<div style="white-space: nowrap; display: inline; margin-right: 3px">
				<span class="label label-warning">Username: {$narrow_username}</span>
			</div>
			{/if}
			
			{if $narrow_admin_text}
			<div style="white-space: nowrap; display: inline; margin-right: 3px">	
				<span class="label label-success">Created by Admin: {$narrow_admin_text}</span>
			</div>
			{/if}

		</div>
		<div class="medium_font" style="display: none;" id="narrowChangeBox">
			<form method="get" action="index.php" class="form-horizontal form-label-left">
				<input type="hidden" name="page" value="orders_list" />
				<input type="hidden" name="sortBy" value="{$sortBy}" />
				<input type="hidden" name="sortOrder" value="{$sortOrder}" />
				
				<div class='x_content'>
				
					<div class='form-group'>
					<label class='control-label col-md-5 col-sm-5 col-xs-12'>Current Order Status: </label>
					  <div class='col-md-6 col-sm-6 col-xs-12'>
						<select name="narrow_order_status" class="form-control col-md-7 col-xs-12" id="narrow_order_status">
							<option value="all"{if $narrow_order_status == 'all'} selected="selected"{/if}>Any Status{if $narrow_order_status == "all"} &#42;{/if}</option>
							<option value="active"{if $narrow_order_status == 'active'} selected="selected"{/if}>Active{if $narrow_order_status == "active"} &#42;{/if}</option>
							<option value="pending"{if $narrow_order_status == 'pending'} selected="selected"{/if}>Pending Payment{if $narrow_order_status == "pending"} &#42;{/if}</option>
							<option value="pending_admin"{if $narrow_order_status == 'pending_admin'} selected="selected"{/if}>Pending{if $narrow_order_status == "pending_admin"} &#42;{/if}</option>
							<option value="incomplete"{if $narrow_order_status == 'incomplete'} selected="selected"{/if}>Incomplete{if $narrow_order_status == "incomplete"} &#42;{/if}</option>
							<option value="canceled"{if $narrow_order_status == 'canceled'} selected="selected"{/if}>Canceled{if $narrow_order_status == "canceled"} &#42;{/if}</option>
							<option value="suspended"{if $narrow_order_status == 'suspended'} selected="selected"{/if}>Suspended{if $narrow_order_status == "suspended"} &#42;{/if}</option>
							<option value="fraud"{if $narrow_order_status == 'fraud'} selected="selected"{/if}>Fraud{if $narrow_order_status == "fraud"} &#42;{/if}</option>
						</select>
					  </div>
					</div>

					<div class='form-group'>
					<label class='control-label col-md-5 col-sm-5 col-xs-12'>Gateway Used: </label>
					  <div class='col-md-6 col-sm-6 col-xs-12'>
						<select name="narrow_gateway_type" class="form-control col-md-7 col-xs-12" id="narrow_gateway_type">
							{foreach from=$types item="title" key="type"}
								<option value="{$type}"{if $narrow_gateway_type == $type} selected="selected"{/if}>{$title}{if $narrow_gateway_type == $type} &#42;{/if}</option>
							{/foreach}
						</select>
					  </div>
					</div>

					<div class='form-group'>
						<label class='control-label col-md-5 col-sm-5 col-xs-12'>From Date: </label> 
						<div class='dateInputOuter col-md-6 col-sm-6 col-xs-12'>
							<i class="fa fa-calendar"></i>
							<input type="text" name="date[low]" id="dateLow" class="form-control dateInput" value="{$date.low|escape}" />
						</div>
					</div>

					<div class='form-group'>
						<label class='control-label col-md-5 col-sm-5 col-xs-12'>To Date: </label> 
						<div class='dateInputOuter col-md-6 col-sm-6 col-xs-12'>
							<i class="fa fa-calendar"></i>
							<input type="text" name="date[high]" id="dateHigh" class="form-control dateInput" value="{$date.high|escape}" />
						</div>
					</div>

					<div class='form-group'>
					<label class='control-label col-md-5 col-sm-5 col-xs-12'>For Username: </label>
					  <div class='col-md-6 col-sm-6 col-xs-12'>
					  	<input type="text" name="narrow_username" class="form-control col-md-7 col-xs-12" value="{$narrow_username|escape}" />
					  </div>
					</div>
					
					<div class='form-group'>
					<label class='control-label col-md-5 col-sm-5 col-xs-12'>Admin Creator ID# or Username: <br /><span class="small_font">(0 for user-created orders)</span></label>
					  <div class='col-md-6 col-sm-6 col-xs-12'>
					  	<input type="text" name="narrow_admin" class="form-control col-md-7 col-xs-12" value="{$narrow_admin|escape}" />
					  </div>
					</div>

					<div class="center">
						<br />
						<input type="submit" value="Narrow Selection" class="mini_button" style="margin-top: 10px; margin-left: 10px;" />
					</div>
				
				</div>
				
			</form>
		</div>

		<div class="table-responsive">
		<table class="table table-hover table-striped table-bordered">
			<thead>
				<tr class="col_hdr_top">
					<th style="width: 10px;">
						{if !$hideStatChange}<input type="checkbox" id="checkAllOrders" />{/if}
					</th>
					<th>
						<a href="{$sort_link}&amp;sortBy=order_id&amp;sortOrder={if $sortBy=='order_id'&&$sortOrder=='up'}down{else}up{/if}">Order</a>
						{if $sortBy=='order_id'}
							<img src="admin_images/admin_arrow_{$sortOrder}.gif" alt="" />
						{/if}
					</th>
					<th>
						<a href="{$sort_link}&amp;sortBy=username&amp;sortOrder={if $sortBy=='username'&&$sortOrder=='up'}down{else}up{/if}">User</a>
						{if $sortBy=='username'}
							<img src="admin_images/admin_arrow_{$sortOrder}.gif" alt="" />
						{/if}
					</th>
					<th>
						<a href="{$sort_link}&amp;sortBy=admin&amp;sortOrder={if $sortBy=='admin'&&$sortOrder=='up'}down{else}up{/if}">Admin Creator</a>
						{if $sortBy=='admin'}
							<img src="admin_images/admin_arrow_{$sortOrder}.gif" alt="" />
						{/if}
					</th>
					<th>
						<a href="{$sort_link}&amp;sortBy=created&amp;sortOrder={if $sortBy=='created'&&$sortOrder=='up'}down{else}up{/if}">Date</a>
						{if $sortBy=='created'}
							<img src="admin_images/admin_arrow_{$sortOrder}.gif" alt="" />
						{/if}
					</th>
					<th>
						<a href="{$sort_link}&amp;sortBy=invoice_id&amp;sortOrder={if $sortBy=='invoice_id'&&$sortOrder=='up'}down{else}up{/if}">Invoice</a>
						{if $sortBy=='invoice_id'}
							<img src="admin_images/admin_arrow_{$sortOrder}.gif" alt="" />
						{/if}
					</th>
					<th>
						<a href="{$sort_link}&amp;sortBy=gateway_type&amp;sortOrder={if $sortBy=='gateway_type'&&$sortOrder=='up'}down{else}up{/if}">Gateway</a>
						{if $sortBy=='gateway_type'}
							<img src="admin_images/admin_arrow_{$sortOrder}.gif" alt="" />
						{/if}
					</th>
					<th>Order Total</th>
					<th>Still Due</th>
					<th>
						<a href="{$sort_link}&amp;sortBy=status&amp;sortOrder={if $sortBy=='status'&&$sortOrder=='up'}down{else}up{/if}">Status</a>
						{if $sortBy=='status'}
							<img src="admin_images/admin_arrow_{$sortOrder}.gif" alt="" />
						{/if}
					</th>
					<th>Approve</th>
				</tr>
			</thead>
			<tbody id="orders_parent">
				{foreach from=$orders item="order"}
					<tr class="{cycle values='row_color1,row_color2'}" id="order{$order.order_id}">
						<td class="medium_font center">{if count($orders) > 1}<input type="checkbox" id='batch_order[{$order.order_id}]' class="orderCheckbox" name="batch_order[{$order.order_id}]" value="1" />{/if}</td>
						<td class="medium_font" style="white-space: nowrap;">
							<a href={$display_order_link|replace:'##':$order.order_id}>{$order.order_id}</a>
						</td>
						<td class="medium_font" style="white-space: nowrap;">
							{if $order.user_id != 0}<a href="index.php?mc=users&amp;page=users_view&amp;b={$order.user_id}">{/if}{$order.username}{if $order.user_id != 0}</a>{/if}
						</td>
						<td class="medium_font" style="white-space: nowrap;">
							{if $order.admin_username}
								{$order.admin_username} (#{$order.admin})
							{elseif $order.admin}
								#{$order.admin}
							{else}
								User Created
							{/if}
						</td>
						<td class="medium_font" style="white-space: nowrap;">{$order.created|date_format}</td>
						<td class="medium_font" style="white-space: nowrap;">
							<a href="{$invoice_link}{$order.invoice_id}" class="lightUpLink">{$order.invoice_id}</a>
						</td>
						<td class="medium_font">{$order.gateway}</td>
						<td class="medium_font">{$order.order_total|displayPrice}</td>
						<td class="medium_font" id="order_due_amount{$order.order_id}"><span style="color: {if $order.due > 0}red{elseif $order.due < 0}green{else}black{/if}">{$order.due|displayPrice}</span></td>
						<td class="medium_font" style="white-space: nowrap; text-align: center;">
							<div class="input-group" style="margin: 4px 0 0 0;">
								<span id="order_status{$order.order_id}">
									<select name="order_status" id="order_status_val{$order.order_id}" class="form-control">
										<option value="active"{if $order.status == "active"} selected="selected"{/if}>Active{if $order.status == "active"} &#42;{/if}</option>
										<option value="pending"{if $order.status == "pending"} selected="selected"{/if}>Pending Payment{if $order.status == "pending"} &#42;{/if}</option>
										<option value="pending_admin"{if $order.status == "pending_admin"} selected="selected"{/if}>Pending{if $order.status == "pending_admin"} &#42;{/if}</option>
										<option value="incomplete"{if $order.status == "incomplete"} selected="selected"{/if}>Incomplete{if $order.status == "incomplete"} &#42;{else}&nbsp;&nbsp;&nbsp;{/if}</option>
										<option value="canceled"{if $order.status == "canceled"} selected="selected"{/if}>Canceled{if $order.status == "canceled"} &#42;{/if}</option>
										<option value="suspended"{if $order.status == "suspended"} selected="selected"{/if}>Suspended{if $order.status == "suspended"} &#42;{/if}</option>
										<option value="fraud"{if $order.status == "fraud"} selected="selected"{/if}>Fraud{if $order.status == "fraud"} &#42;{/if}</option>
										<option disabled="disabled">---------</option>
										<option value="delete">Delete</option>
									</select>
								</span>
								<span class="input-group-btn"><button class='btn btn-primary' {$set_status_link|replace:'##':$order.order_id}>Set</button></span>
							</div></td>
						<td class="medium_font" style="text-align: center;">{$approve_link|replace:'##':$order.order_id}</td>
					</tr>
				{foreachelse}
					<tr>
						<td colspan="11">
							<div class="page_note_error">No Orders Match the "Chart Filters" above. Please edit Filters to Refine Results.</div>
						</td>
					</tr>
				{/foreach}
				<tr>
					<td colspan="11" class="medium_font" style="text-align: center;">
						{$pagination}
					</td>
				</tr>
			
				{if $orders && count($orders) > 1}
					<tr>
						<td colspan="11" class="medium_font" style="text-align: center;">
							With selected:
							<select name="batch_status" id="batch_status">
								<option>--Choose--</option>
								<option value='active'>Active</option>
								<option value='pending'>Pending Payment</option>
								<option value='pending_admin'>Pending</option>
								<option value='incomplete'>Incomplete</option>
								<option value='canceled'>Canceled</option>
								<option value='suspended'>Suspended</option>
								<option value='fraud'>Fraud</option>
							</select>
							{include file="HTML/add_button" label="Apply" link=$apply_url link_is_really_javascript="1"}
						</td>
					</tr>
				{/if}
			</tbody>
		</table>
		</div>
		
		<div class="center">{include file="HTML/add_button" label="Refresh Orders" link="onclick='window.location.reload(true)'" link_is_really_javascript="1"}</div>
	</div>
</fieldset>

{include file='orders/get_order_form.tpl'}
<div class='clearColumn'></div>