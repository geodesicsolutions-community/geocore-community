{* 16.09.0-79-gb63e5d8 *}


<script type="text/javascript">
jQuery(function () {
	jQuery('#checkAllOrders').click(function() {
		jQuery('.orderCheckbox').prop('checked',this.checked);
	});
});

</script>

<form id="batchForm" action="{$links.applyUrl}" method="post">
<fieldset>
	<legend>Managing Recurring Billings</legend>
	<div>
		<div class="medium_font">Show Billing Set to: 
			<select name="narrow_status" id="narrow_status">
				<option value="all"{if $narrow_order_status == 'all'} selected="selected"{/if}>Any Status{if $narrow_order_status == "all"} &#42;{/if}</option>
				<option value="active"{if $narrow_order_status == 'active'} selected="selected"{/if}>Active{if $narrow_order_status == "active"} &#42;{/if}</option>
				<option value="canceled"{if $narrow_order_status == 'canceled'} selected="selected"{/if}>Canceled{if $narrow_order_status == "canceled"} &#42;{/if}</option>
				<option value="other"{if $narrow_order_status == 'other'} selected="selected"{/if}>Other{if $narrow_order_status == "other"} &#42;{/if}</option>
			</select>
			Using: 
			<select name="narrow_gateway_type" id="narrow_gateway_type">
				{foreach from=$types item="title" key="type"}
					<option value="{$type}"{if $narrow_gateway_type == $type} selected="selected"{/if}>{$title}{if $narrow_gateway_type == $type} &#42;{/if}</option>
				{/foreach}
			</select>
			{include file='HTML/add_button.tpl' link_is_really_javascript=1 link="onclick=\"window.location='index.php?page=recurring_billing_list&narrow_status='+\$F('narrow_status')+'&narrow_gateway_type='+\$F('narrow_gateway_type')\"" label="Narrow Selection" type="button"}
			
		</div>
		<div class="table-responsive">
		<table class="table table-hover table-striped table-bordered">
			<thead>
				<tr class="col_hdr_top">
					<th style="width: 20px;"><input type="checkbox" id="checkAllOrders" /></th>
					<th>Internal ID</th>
					<th>User</th>
					<th>Start Date</th>
					<th>Paid Until</th>
					<th>Gateway</th>
					<th>Cost per Cycle</th>
					<th>Cycle Duration</th>
					<th>Last Known Status</th>
					<th>Refresh Status</th>
				</tr>
			</thead>
			<tbody id="orders_parent">
			{foreach from=$recurringBillings item="recurring"}
				<tr class="{cycle values='row_color1,row_color2'}" id="order{$recurring.id}">
					<td class="center medium_font" style="width: 15px;">
						{if count($recurringBillings) > 1}
							<input type="checkbox" class="orderCheckbox" id='batch_recurring[{$recurring.id}]' name="batch_recurring[{$recurring.id}]" value="{$recurring.id}" />
						{/if}
					</td>
					<td class="medium_font" style="white-space: nowrap;">
						<a href="{$links.recurring_details}{$recurring.id}">{$recurring.id}</a>
					</td>
					<td class="medium_font" style="white-space: nowrap;">
						{if $recurring.user_id != 0}<a href="index.php?mc=users&amp;page=users_view&amp;b={$recurring.user_id}">{/if}{$recurring.username}{if $recurring.user_id != 0}</a>{/if}
					</td>
					<td class="medium_font" style="white-space: nowrap;">{$recurring.start_date|date_format}</td>
					<td class="medium_font" style="white-space: nowrap;"><span id="paidUntilValue{$recurring.id}">{$recurring.paid_until|date_format}</span></td>
					<td class="medium_font">{$recurring.gateway}</td>
					<td class="medium_font">{$recurring.price_per_cycle|displayPrice}</td>
					<td class="medium_font">{$recurring.cycle_duration} days</td>
					<td class="medium_font" style="white-space: nowrap;">
						<span id="statusValue{$recurring.id}">{if $recurring.status_extra}{$recurring.status_extra}{else}{$recurring.status|capitalize}{/if}</span>
						{if $recurring.status!='canceled' && $recurring.canCancel}
							{include file='HTML/add_button.tpl' link_is_really_javascript=1 link=$links.cancel label="Cancel" assign="linky" type="button"}
							{$linky|replace:0:$recurring.id}
						{/if}
					</td>
					<td class="medium_font">
						{if $recurring.canCancel}
							{include file='HTML/add_button.tpl' link_is_really_javascript=1 link=$links.refresh label="Refresh Status" assign="linky" type="button"}
							{$linky|replace:0:$recurring.id}
						{else}
							-
						{/if}
					</td>
				</tr>
			{foreachelse}
				<tr>
					<td colspan="10">
					<div class="page_note_error">No Recurring Billings match Selection above.</div>
					</td>
			{/foreach}
				<tr>
					<td colspan="10" class="medium_font" style="text-align: center;">
						{$pagination}					
					</td>
				</tr>
			{if $recurringBillings && count($recurringBillings) > 1}
				<tr>
					<td colspan="10" class="medium_font" style="text-align: left;">
						With selected:
						<select name="batch_status" id="batch_status">
							<option>--Choose--</option>
							<option value='updateStatus'>Refresh Status</option>
							<option value='cancel'>Cancel Billing</option>
						</select>
						{include file="HTML/add_button" label="Apply" link=$links.applySelected link_is_really_javascript="1" type="button"}
					</td>
				</tr>
			{/if}
			</tbody>
		</table>
		</div>
	{include file="HTML/add_button" label="Refresh List" link="onclick='window.location.reload(true)'" link_is_really_javascript="1"}
	</div>
</fieldset>
</form>
{include file='orders/get_order_form.tpl' ent=1}