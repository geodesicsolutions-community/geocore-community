{* 16.09.0-103-gbd4ee83 *}

{$admin_msgs}
<fieldset id="user_attachment_type_edit">
	<legend>Edit Attachment Type Details</legend>
	<div>
	<script type='text/javascript'>
	function validate_inputs()
	{
		if (document.forms['attachment_form_type_1']['attachment_active_checkbox'].checked) {
			if (document.getElementById('fee_share').value==0){ alert ('You must enter a value for the percentage of fees to share'); return false; }
		
			if (document.getElementById('group_attach_from').value == document.getElementById('group_attach_to').value) { alert ('User group to attach from cannot match user group to attach to.'); return false; } return true;
		}
	}
	</script>
		
	<form name="attachment_form_type_1" class="form-horizontal form-label-left" action="" method="post">
	
	{if $not_enough_user_groups == 0}
		<div class="x_content">
			<div class="header-color-primary-one">Edit Details for Share Fees With Attached Users Attachment Type</div>

			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'></label>
			  <div class='col-md-7 col-sm-7 col-xs-12'>
			    <input id=attachment_active_checkbox type="checkbox" name="shared_fee_data[active]"  value="1"{if $attachment_data.active == 1} checked="checked"{/if} />&nbsp;
			    Active
			  </div>
			</div>

			<div class='form-group'>
			<label class='control-label col-sm-5 col-xs-12'>Users from this User Group: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
				<select id="group_attach_from" name="shared_fee_data[group_attach_from]" class="form-control col-md-7 col-xs-12">
				{foreach from=$group_list_from key=group_id item=group_name}
					<option value="{$group_id}" {if $attachment_data.attaching_user_group == $group_id}selected{/if}>{$group_name}</option>
				{/foreach}
				</select>
			  </div>
			</div>		
			

				
			<div class="form-group">
				<label class='control-label col-sm-5 col-xs-12'>Can Attach to Users in this user group:</label>
				<div class="col-sm-6 col-xs-12">
					<select id="group_attach_to" name="shared_fee_data[group_attach_to]" class="form-control">
					{foreach from=$group_list_to key=group_id item=group_name}
							<option value="{$group_id}" {if $attachment_data.attached_to_user_group == $group_id}selected{/if}>{$group_name}</option>
					{/foreach}
					</select>
				</div>
			</div>			
			
			<div class="form-group">
				<label class='control-label col-sm-5 col-xs-12'>Percentage of fees that will be shared from attached user to attaching to user:</label>
				<div class="col-sm-6 col-xs-12">
					<input type=text id="fee_share" name="shared_fee_data[fee_share]" class="form-control" value="{$attachment_data.percentage_fee_shared}">
				</div>
			</div>	
				
			<div class="form-group">
				<label class='control-label col-sm-5 col-xs-12'>Require user to choose attachment</label>
				<div class="col-sm-6 col-xs-12">
					<input id=attachment_required_checkbox type="checkbox" name="shared_fee_data[required]"  value="1"{if $attachment_data.required == 1} checked="checked"{/if} />
				</div>
			</div>			
			<div class="form-group">
				<label class='control-label col-sm-5 col-xs-12'>Redirect to attached user storefront after login</label>
				<div class="col-sm-6 col-xs-12">
					<input id=attachment_redirect_after_login type="checkbox" name="shared_fee_data[post_login_redirect]"  value="1"{if $attachment_data.post_login_redirect == 1} checked="checked"{/if} />
				</div>
			</div>	
			<div class="form-group">
				<label class='control-label col-sm-5 col-xs-12'>Display attached users storefront category choices - attached users listings can display in attached to users storefront if you use this feature</label>
				<div class="col-sm-6 col-xs-12">
					<input id=attachment_store_category_display type="checkbox" name="shared_fee_data[display_store_category_choices]"  value="1"{if $attachment_data.store_category_display == 1} checked="checked"{/if} />
				</div>
			</div>					
			<div class="form-group">
				<label class='control-label col-sm-5 col-xs-12'>Display attached to user message within attached users client home page</label>
				<div class="col-sm-6 col-xs-12">
					<input id=attachment_use_attached_messages type="checkbox" name="shared_fee_data[use_attached_messages]"  value="1"{if $attachment_data.use_attached_messages == 1} checked="checked"{/if} />
				</div>
			</div>		
							
			<h2 class="header-color-primary-one">Types of Fees To Share:</h2>
			
			<div class="form-group">
				<label class='control-label col-sm-5 col-xs-12'>Auction Final Fees</label>
				<div class="col-sm-6 col-xs-12">
					<input id=attachment_types_final_fee type="checkbox" name="fee_types_shared[auction_final_fees]"  value="auction_final_fees"  {if $attachment_data.fee_types_list.auction_final_fees == 1} checked="checked"{/if} /></div>
				</div>
			</div>					
		</div>	
		<div class="center">
			<input type="submit" name="auto_save" value="{if $new}Add Category{else}Apply Changes{/if}" class="mini_button" onClick="javascript:return (validate_inputs());"/>
		</div>
		
	{else}
		<div class="header-color-primary-one">Error: There are not enough user groups to use this user attachment type.  You must have at least<br> two user groups.  One set of users to attach and another user group of users to attach to.</div>
	{/if}
	</form>
	</div>
</fieldset>