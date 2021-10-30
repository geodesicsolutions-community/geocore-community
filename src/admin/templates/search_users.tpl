{* 16.09.0-79-gb63e5d8 *}

{$admin_msgs}
<form action="" method="post" class="form-horizontal form-label-left">
	<fieldset>
		<legend>Search Method</legend>
		<div class='x_content'>
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Search for: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
				<select name="b[search_type]" id="search_type_ddl" class="form-control col-md-7 col-xs-12">
					<option value="1">Users by Text Field Below</option>
					<option value="2">Users with Suspended Status</option>
					{* option 3 merged into 4 for simplicity: Users registered before/after a specific date *}
					<option value="4">Users Registered in a Date Range</option>
					<option value="5">Users with at Least One Listing Expiring in a Date Range</option>
				</select>
			  </div>
			</div>
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Order results by: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
				<select name="b[sort_type]" class="form-control col-md-7 col-xs-12">
					<option value="1">Username</option>
					<option value="2">Last Name</option>
					<option value="3">First Name</option>
					<option value="4">Email Address</option>
					<option value="5">Company Name</option>
					<option value="6">URL</option>
				</select>
			  </div>
			</div>
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Search in User Group: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
				<select name="b[search_group]" class="form-control col-md-7 col-xs-12">
					<option value="0">All Groups</option>
					{foreach $groups as $g}
						<option value="{$g.id}">{$g.name}</option>
					{/foreach}
				</select>
			  </div>
			</div>
		</div>
	</fieldset>

	<fieldset>
		<legend>Search Criteria</legend>
		<div class='x_content'>
			<div id="search_type_1" class="search_type" style="display: none;">
				<div class='form-group'>
				<label class='control-label col-md-5 col-sm-5 col-xs-12'>Search for this Text: </label>
				  <div class='col-md-6 col-sm-6 col-xs-12'><input type="text" name="b[text_to_search]"  class='form-control col-md-7 col-xs-12'/>
				  </div>
				</div>
				<div class='form-group'>
				<label class='control-label col-md-5 col-sm-5 col-xs-12'>Search in a Specific Field: </label>
				  <div class='col-md-6 col-sm-6 col-xs-12'>
					<select name="b[field_type]" class="form-control col-md-7 col-xs-12" />
						<option value="0">All Fields</option>
						<option value="1">Username</option>
						<option value="2">Last Name</option>
						<option value="3">First Name</option>
						<option value="4">Email Address</option>
						<option value="5">Company Name</option>
						<option value="6">URL</option>
						<option value="7">City</option>
						<option value="8">Phone Contacts</option>							
					</select>
				  </div>
				</div>
			</div>
			<div id="search_type_2" class="search_type" style="display: none;">
				<div class="center">No additional criteria. Click Search to view Suspended users</div>
			</div>
			<div id="search_type_4" class="search_type" style="display: none;">
				<div class='form-group'>
					<label class='control-label col-md-5 col-sm-5 col-xs-12'>After: </label> 
					<div class='dateInputOuter col-md-6 col-sm-6 col-xs-12'>
						<i class="fa fa-calendar"></i>
						<input type="text" name="b[join_begin_date]" class="form-control dateInput" />
					</div>
				</div>
				<div class='form-group'>
					<label class='control-label col-md-5 col-sm-5 col-xs-12'>Before: </label> 
					<div class='dateInputOuter col-md-6 col-sm-6 col-xs-12'>
						<i class="fa fa-calendar"></i>
						<input type="text" name="b[join_end_date]" class="form-control dateInput" />
					</div>
				</div>
			</div>
			<div id="search_type_5" class="search_type" style="display: none;">
				<div class='form-group'>
					<label class='control-label col-md-5 col-sm-5 col-xs-12'>After: </label> 
					<div class='dateInputOuter col-md-6 col-sm-6 col-xs-12'>
						<i class="fa fa-calendar"></i>
						<input type="text" name="b[expire_begin_date]" class="form-control dateInput" />
					</div>
				</div>
				<div class='form-group'>
					<label class='control-label col-md-5 col-sm-5 col-xs-12'>Before: </label> 
					<div class='dateInputOuter col-md-6 col-sm-6 col-xs-12'>
						<i class="fa fa-calendar"></i>
						<input type="text" name="b[expire_end_date]" class="form-control dateInput" />
					</div>
				</div>
			</div>
		</div>
	</fieldset>

<div class="center">
	<input type="submit" value="Search" name="auto_save" />
</div>
</form>

<script>
	jQuery(document).ready(function(){
		jQuery('#search_type_ddl').change(function(){
			jQuery('.search_type').hide();
			jQuery('#search_type_'+jQuery('#search_type_ddl').val()).show();
		});
		jQuery('#search_type_ddl').change();
	});
</script>