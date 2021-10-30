{* 16.09.0-79-gb63e5d8 *}
<fieldset>
	<legend>Order Details</legend>
<div class="form-horizontal form-label-left">
	<div id='frm_order_details' class='x_content'>
{if $order}

        <div class='form-group'>
        <label class='control-label col-md-5 col-sm-5 col-xs-12'>Username: </label>
          <div class='col-md-6 col-sm-6 col-xs-12 vertical-form-fix'>
          {$order.username}
          </div>
        </div>
        
        <div class='form-group'>
        <label class='control-label col-md-5 col-sm-5 col-xs-12'>Admin Creator: </label>
          <div class='col-md-6 col-sm-6 col-xs-12 vertical-form-fix'>
		{if $order.admin_username}
			{$order.admin_username} (#{$order.admin})
		{elseif $order.admin}
			ID #{$order.admin} (Error retrieving admin user data)
		{else}
			N/A (User Created on client side)
		{/if}
          </div>
        </div>
        
        <div class='form-group'>
        <label class='control-label col-md-5 col-sm-5 col-xs-12'>Date: </label>
          <div class='col-md-6 col-sm-6 col-xs-12 vertical-form-fix'>
          {$order.date}
          </div>
        </div>

        <div class='form-group'>
        <label class='control-label col-md-5 col-sm-5 col-xs-12'>Gateway: </label>
          <div class='col-md-6 col-sm-6 col-xs-12 vertical-form-fix'>
          {$order.gateway_type}
          </div>
        </div>
	
	{if $order.cc_number}
        <div class='form-group'>
        <label class='control-label col-md-5 col-sm-5 col-xs-12'>CC Number{if $order.cvv2_code} [cvv2]{/if}{if $order.exp_date} (EXP date){/if}: </label>
          <div class='col-md-6 col-sm-6 col-xs-12 vertical-form-fix'>
	  {$order.cc_number} {if $order.cvv2_code}[{$order.cvv2_code}]{/if} {if $order.exp_date}({$order.exp_date}){/if} {if $order.can_delete_cc}<a href="?page=orders_list_order_details&order_id={$order.order_id}&clear_cc=1&auto_save=1" class="mini_cancel lightUpLink">Clear CC Data</a>{/if}
          </div>
        </div>
	{/if}        

        <div class='form-group'>
        <label class='control-label col-md-5 col-sm-5 col-xs-12'>Status: </label>
          <div class='col-md-6 col-sm-6 col-xs-12'>
		<span id="order_status{$order.order_id}" onchange="CJAX.$('do_next').value='save_and_back';">
			<select name="order_options[status]" id="order_options[status]" class='form-control col-md-7 col-xs-12'>
				<option value="active"{if $order.status == "active"} selected="selected"{/if}>Active{if $order.status == "active"} &#42;{/if}</option>
				<option value="pending"{if $order.status == "pending"} selected="selected"{/if}>Pending Payment{if $order.status == "pending"} &#42;{/if}</option>
				<option value="pending_admin"{if $order.status == "pending_admin"} selected="selected"{/if}>Pending{if $order.status == "pending_admin"} &#42;{/if}</option>
				<option value="incomplete"{if $order.status == "incomplete"} selected="selected"{/if}>Incomplete{if $order.status == "incomplete"} &#42;{else}&nbsp;&nbsp;&nbsp;{/if}</option>
				<option value="canceled"{if $order.status == "canceled"} selected="selected"{/if}>Canceled{if $order.status == "canceled"} &#42;{/if}</option>
				<option value="suspended"{if $order.status == "suspended"} selected="selected"{/if}>Suspended{if $order.status == "suspended"} &#42;{/if}</option>
				<option value="fraud"{if $order.status == "fraud"} selected="selected"{/if}>Fraud{if $order.status == "fraud"} &#42;{/if}</option>
				<option value="delete">Delete</option>
			</select>
		</span>
          </div>
        </div>

        <div class='form-group'>
        <label class='control-label col-md-5 col-sm-5 col-xs-12'>Invoice #: </label>
          <div class='col-md-6 col-sm-6 col-xs-12 vertical-form-fix'>
          <a href="{$invoice_link}" class="lightUpLink">{$order.invoice_id}</a>
          </div>
        </div>

        <div class='form-group'>
        <label class='control-label col-md-5 col-sm-5 col-xs-12'>Order Total: </label>
          <div class='col-md-6 col-sm-6 col-xs-12 vertical-form-fix'>
          {$order.total}
          </div>
        </div>	

        <div class='form-group'>
        <label class='control-label col-md-5 col-sm-5 col-xs-12'>Amount Still Due: </label>
          <div class='col-md-6 col-sm-6 col-xs-12 vertical-form-fix'>
          <span style="color: {if $order.due > 0}red{elseif $order.due < 0}green{else}black{/if}">{$order.due|displayPrice}</span>
          </div>
        </div>	
		
        <div class='form-group'>
        <label class='control-label col-md-5 col-sm-5 col-xs-12'>Summary of Order Items: </label>
          <div class='col-md-6 col-sm-6 col-xs-12 vertical-form-fix'>
          {foreach from=$attached_items  item='item'}
	  				[{$item.type}] - {$item.title} <br />
	  	{foreachelse}
	  				None found?
	  {/foreach}
          </div>
        </div>		
			
	<div class='form-group'>
	<label class='control-label col-md-5 col-sm-5 col-xs-12'></label>
	  <div class='col-md-7 col-sm-7 col-xs-12'>
	    <input type='checkbox' id='order_options[apply_to_all]' checked="checked" />&nbsp;
	    Apply Changes to Attached Items
	  </div>
	</div>	

	<div class='form-group'>
	<label class='control-label col-md-5 col-sm-5 col-xs-12'></label>
	  <div class='col-md-7 col-sm-7 col-xs-12'>
	    <input type='checkbox' id='order_options[email_notifications]' checked="checked" />&nbsp;
	    Send Email Notifications
	  </div>
	</div>	
	
	{include file="HTML/separator.tpl"}

	<div class='centercolumn' style='position:relative; text-align:center'>
		{include file="HTML/add_button.tpl" link_is_really_javascript="1" link=$take_action label="Save"}
	</div>
		
{else}
Invalid order, or order is missing needed data.
{/if}
	</div>
</div>
</fieldset>
{if $order}
	<fieldset>
		<legend>Order Items</legend>
		<div class="table-responsive">
			<table class="table table-hover table-striped">
				<thead>
					<tr class="col_hdr_top">
						<th class="col_hdr_left">Item</th>
						<th class="col_hdr_left">Type</th>
						{if $order.status == 'active'}
							<th class="col_hdr">Item Status</th>
						{/if}
						<th class="col_hdr_left">Cost</th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$attached_items key='item_id' item='item'}
						{cycle values="row_color1,row_color2" assign="row_color"}
						<tr>
							<td>
								{if $order.status == 'active' && $item.displayInAdmin}
									{* Only display link if order is active, otherwise can't see much about it *}
									<a href="index.php?page=orders_list_items_item_details&item_id={$item_id}"><span class='color-primary-two'>#{$item_id}</span></a>: 
								{/if}
								{$item.title}
							</td>
							<td>{$item.type}</td>
							{if $order.status == 'active'}
								<td style='text-align:center;'>{$item.status}</td>
							{/if}
							<td>{$item.cost|displayPrice}</td>
						</tr>
						{foreach from=$item.children item='child'}
							<tr>
								<td> &nbsp; &nbsp; <i class="fa fa-chevron-circle-right"></i> {$child.title}</td>
								<td>{$child.type}</td>
								{if $order.status == 'active'}<td></td>{/if}
								<td>{$child.cost|displayPrice}</td>
							</tr>
						{/foreach}
					{foreachelse}
						<tr><td colspan="3">No attached items.</td></tr>
					{/foreach}
				</tbody>
				<tfoot>
						<tr>
							<td class="col_ftr" colspan="{if $order.status == 'active'}3{else}2{/if}" style="text-align: right;">Order Total:</td>
							<td class="col_ftr" style="font-size: 1em;">{$order.total}</td>
						</tr>
				</tfoot>
			</table>
		</div>
	</fieldset>
	<div class='clearColumn'></div>
{/if}

{if !$order}
	{include file="orders/get_order_form.tpl"}
{/if}