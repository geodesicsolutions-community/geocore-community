{* 16.09.0-79-gb63e5d8 *}

{$admin_msgs}
<div class="page-title1">User Group: <span class='group-color'>
{if $group_name ne ""}
	{$group_name}</span></div>
{else}
	All Groups (Site-Wide)</span></div>
{/if}

{if $group}
<div style='padding: 5px;'><a href="index.php?mc=users&page=users_group_edit&c={$group}" class='back_to'>
<i class='fa fa-backward'></i> Back to {$group_name} Details</a></div>
{/if}

<fieldset>
	<legend>Payment Gateway Settings for 
{if $group}
	<strong>{$group_name}</strong>
{else}
	<strong>Site-Wide</strong>
{/if}
	</legend>
	
	<form method='post' action='' id='frm_all_settings'>
		<input type="hidden" name="group" value="{$group}" id="payGroup" />
		<div id='table_settings'>
		{include file='payment_gateways/gateway_table.tpl'}
		</div>
		<div style="text-align: center">
			<input name='auto_save_ajax' value='Save' type='submit' class="saveAll" />
		</div>
	</form>
</fieldset>
<div class='clearColumn'></div>