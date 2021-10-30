{* 16.09.0-79-gb63e5d8 *}
{$admin_msgs}
<fieldset>
	<legend>Generate Revenue Report</legend>
	<div>
		<p class="page_note">
			Select one or more user groups, and enter a date range to obtain a report of total revenue from members of the selected group(s) during the selected date range.<br />
			<br />
			<strong>NOTE:</strong> If you do not select at least one user group, all user groups will be reported. If you do not select a valid date range, the last 30 days will be reported.
		</p>
	
		<form action="" method="post" class="form-horizontal form-label-left">
			<div class='x_content'>
				<div class='form-group'>
				<label class='control-label col-md-5 col-sm-5 col-xs-12'>User Group: </label>
				  <div class='col-md-6 col-sm-6 col-xs-12'>
					<select name="d[usergroups][]" multiple="multiple" class="form-control">
						{foreach from=$groups item="groupName" key="groupId"}
							<option value="{$groupId}">{$groupName}</option>
						{/foreach}
					</select>
				  </div>
				</div> 

				<div class='form-group'>
					<label class='control-label col-md-5 col-sm-5 col-xs-12'>Start Date: </label> 
					<div class='dateInputOuter col-md-6 col-sm-6 col-xs-12'>
						<i class="fa fa-calendar"></i>
						<input type="text" name="d[start_date]" id="startDate" class="form-control dateInput" />
					</div>
				</div>		

				<div class='form-group'>
					<label class='control-label col-md-5 col-sm-5 col-xs-12'>End Date: </label> 
					<div class='dateInputOuter col-md-6 col-sm-6 col-xs-12'>
						<i class="fa fa-calendar"></i>
						<input type="text" name="d[end_date]" id="endDate" class="form-control dateInput" />
					</div>
				</div>	

				<div class='form-group'>
				<label class='control-label col-md-5 col-sm-5 col-xs-12'>Output as CSV: <input type="checkbox" name="d[as_csv]" value="1" /></label>
				</div> 
	
				<div class="center">
					<input type="submit" value="Submit" name="auto_sv" />
				</div>
			</div>
		
		</form>
	</div>
</fieldset>

{if $report !== false}
	<fieldset>
	<div class='form-horizontal form-label-left'>
		<legend>Revenue Report</legend>
		<div>Revenue for selected group(s) from {$report_start} to {$report_end} for {$classifieds_url}</div>
		<div class='x_content'>

			{foreach from=$report item="group"}
			        <div class='form-group'>
			        <label class='control-label col-md-5 col-sm-5 col-xs-12'>{$group.name}: </label>
			          <div class='col-md-6 col-sm-6 col-xs-12'>
						{$group.total}<br />
						{$group.numListings} listings<br />
						{$group.newUsers} new registrations
			          </div>
        			</div> 
			{/foreach}
		</div>
	</div>
	</fieldset>	
{/if}