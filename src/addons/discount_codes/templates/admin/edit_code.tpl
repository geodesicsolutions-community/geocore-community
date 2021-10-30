{* 16.09.0-105-ga458f5f *}

{$admin_msgs}

<fieldset>
	<legend>{if $discount_id}Edit{else}Add{/if} Discount Code</legend>
	<div class='x_content'>
		<form action="" method="post" class="form-horizontal form-label-left">
			<p class="page_note">* Indicates required fields.</p>
			
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'></label>
			  <div class='col-md-7 col-sm-7 col-xs-12'>
			    <input type="checkbox" name="edit[active]" id="isActive" value="1"{if $data.active} checked="checked"{/if} />&nbsp;
			    Enabled</span>
			  </div>
			</div>

			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>* Discount Code Name: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
			  <input type="text" name="edit[name]" class='form-control col-md-7 col-xs-12' value="{$data.name|fromDB|escape}" size="30" />
			  </div>
			</div>

			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Description: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
			  <textarea name="edit[description]" class='form-control col-md-7 col-xs-12' rows="3" cols="30">{$data.description|fromDB|escape}</textarea>
			  </div>
			</div>

			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>* Code: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
			  <input type="text" name="edit[discount_code]" class='form-control col-md-7 col-xs-12' value="{$data.discount_code|fromDB|escape}" size="30" />
			  </div>
			</div>

			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>* Discount Percentage: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12 input-group'>
			  <input type="text" name="edit[discount_percentage]" class='form-control col-md-7 col-xs-12 input-group' value="{$data.discount_percentage|escape}" size="5" />
			  <div class='input-group-addon'>% Off full price</div>
			  </div>
			</div>		

			{if $is_ent}
				<div class='form-group' style="margin-bottom: 10px;">
				<label class='control-label col-md-5 col-sm-5 col-xs-12'>* Used For: </label>
				  <div class='col-md-7 col-sm-7 col-xs-12'>
				    <input type="checkbox" name="edit[apply_normal]" value="1"{if $data.apply_normal} checked="checked"{/if} />&nbsp;
				    Normal Cart Sub-Total (listing fees, listing extras, non-recurring subscription payments, etc)<br>
				    <input type="checkbox" name="edit[apply_recurring]" value="1"{if $data.apply_recurring} checked="checked"{/if} />&nbsp;
				    Automatic Recurring Payments (Periodic recurring payments, typically for user subscriptions)
				  </div>
				</div>
			{/if}
					
			<div class='form-group'>
				<label class='control-label col-md-5 col-sm-5 col-xs-12'>Start Date: </label> 
				<div class='dateInputOuter col-md-6 col-sm-6 col-xs-12'>
					<i class="fa fa-calendar"></i>
					<input type="text" name="edit[starts]" placeholder="YYYY-MM-DD" id="startDate" class="form-control dateInput" value="{if $data.starts}{$data.starts|format_date:'Y-m-d'}{/if}" size="10" />
				</div>
			</div>
			
			<div class='form-group'>
				<label class='control-label col-md-5 col-sm-5 col-xs-12'>End Date: </label> 
				<div class='dateInputOuter col-md-6 col-sm-6 col-xs-12'>
					<i class="fa fa-calendar"></i>
					<input type="text" name="edit[ends]" placeholder="YYYY-MM-DD" id="endDate" class="form-control dateInput" value="{if $data.ends}{$data.ends|format_date:'Y-m-d'}{/if}" size="10" />
					(Blank or 0 to never end)
				</div>
			</div>

			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'></label>
			  <div class='col-md-7 col-sm-7 col-xs-12'>
			    <input type="checkbox" id="isGroupSpecificCheck" name="edit[is_group_specific]" value="1"{if $data.is_group_specific} checked="checked"{/if} />&nbsp;
			    Group Specific?
			  </div>
			</div>			

			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Attached Groups: </label>
			  <div class='col-md-7 col-sm-7 col-xs-12'>
				{foreach from=$groups item=group}
						<input type="checkbox" name="edit[groups][{$group.group_id}]" value="1"{if $data.groups[$group.group_id]} checked="checked"{/if} /> 
						{$group.name}
					<br />
				{/foreach}
			  </div>
			</div>	
						
			{if $joe_edwards_discountLink}
				<div class="col_hdr_top">Joe Edwards</div>
				<div class="{cycle values='row_color1,row_color2'}">
					<div class="leftColumn">Discount Code Email:</div>
					<div class="rightColumn">
						<input type="text" name="edit[discount_email]" value="{$data.discount_email|fromDB|escape}" />
					</div>
					<div class="clearColumn"></div>
				</div>
				<div class="{cycle values='row_color1,row_color2'}">
					<div class="leftColumn">Cross-Debit User ID:</div>
					<div class="rightColumn">
						<input type="text" name="edit[user_id]" value="{if $data.user_id>0}{$data.user_id|escape}{/if}" size="4" />
					</div>
					<div class="clearColumn"></div>
				</div>
			{/if}
			<div class="center">
				<br />
				<input type="submit" name="auto_save" value="Save" class="mini_button" />
			</div>
		</form>
	</div>
</fieldset>
<div class='clearColumn'></div>