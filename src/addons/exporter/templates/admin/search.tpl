{* 17.04.0-10-g9979ee3 *}
{$notices}

{capture assign="multiSelect"}
	<strong>Multi-Select Tip:</strong> To select or un-select multiple entries, hold down CTRL in Windows 
		or CMD in OS X while left-clicking.
{/capture}
{capture assign="exportButton"}
	<div class="center"><a href="#" class="btn btn-success exportButton"><i class="fa fa-download"></i> Export &amp; Download Now</a></div>
{/capture}
<div id="requestResponse"></div>

<form method="post" id="exportForm" action="index.php?page=addon_exporter" class='form-horizontal'>
	<br />
	{$exportButton}
	<input type="hidden" name="auto_save" value="1" />
	
	<br /><br /><br />
	
	<ul class="tabList">
		<li id="criteriaTab" class="activeTab">Export Criteria</li>
		<li id="dataTab">Data Exported</li>
		<li id="saveTab">Save/Load Export Settings</li>
	</ul>

	<div class="tabContents" id="criteriaTabContents">
		<div class='col-xs-12 col-sm-3'>
			<fieldset>
				<legend>General</legend>
				<div class='x_content'>
					<div class="form-group">
						<label class='control-label col-xs-12 col-sm-5'>Max listings</label>
						<div class="col-xs-12 col-sm-6">
							<input type="text" name="maxListings" value="500" class="form-control" />
						</div>
					</div>
					<div class="form-group">
						<label class='control-label col-xs-12 col-sm-5'>Listing Type</label>
						<div class="col-xs-12 col-sm-6">
							<input type="radio" name="item_type" value="indif" checked="checked" /> Indifferent<br />
							<input type="radio" name="item_type" value="2" /> Auction<br />
							<input type="radio" name="item_type" value="1" /> Classified
						</div>
					</div>
					<div class="form-group">
						<label class='control-label col-xs-12 col-sm-5'>Status</label>
						<div class="col-xs-12 col-sm-6">
							<input type="radio" name="live" value="indif" /> Indifferent<br />
							<input type="radio" name="live" value="1" checked="checked" /> Live<br />
							<input type="radio" name="live" value="0" /> Expired<br />
						</div>
					</div>
					<div class="form-group">
						<label class='control-label col-xs-12 col-sm-5'>Image</label>
						<div class="col-xs-12 col-sm-6">
							<input type="radio" name="image" value="indif" checked="checked" /> Indifferent<br />
							<input type="radio" name="image" value="1" /> Yes<br />
							<input type="radio" name="image" value="0" /> No
						</div>
					</div>
					<div class="form-group">
						<label class='control-label col-xs-12 col-sm-5'>Price Range</label>
						<div class="col-xs-12 col-sm-6">
							<div class='input-group'>
								<div class='input-group-addon'>Low</div>
								<input type="text" name="price[low]" class="form-control" />
							</div>
							<div class='input-group'>
								<div class='input-group-addon'>High</div>
								<input type="text" name="price[high]" class="form-control" />
							</div>
						</div>
					</div>
				</div>
			</fieldset>
		</div>
		<div class='col-xs-12 col-sm-3'>
			<fieldset>
				<legend>Date</legend>
				<div style="height: 434px;">
					<div class="form-group">
						<label class='control-label col-xs-12 col-sm-3'>Start Date</label>
						<div class="col-xs-12 col-sm-9">
							<div class='input-group'>
								<div class='input-group-addon'>From <i class='fa fa-calendar'></i></div>
								<input type="text" name="date[start][low]" id="startDateLow" class="dateInput form-control" />
							</div>
							<div class='input-group'>
								<div class='input-group-addon'>To <i class='fa fa-calendar'></i></div>
								<input type="text" name="date[start][high]" id="startDateHigh" class="dateInput form-control" />
							</div>
						</div>
					</div>
					<div class="form-group">
						<label class='control-label col-xs-12 col-sm-3'>End Date</label>
						<div class="col-xs-12 col-sm-9">
							<div class='input-group'>
								<div class='input-group-addon'>From <i class='fa fa-calendar'></i></div>
								<input type="text" name="date[end][low]" id="endDateLow" class="dateInput form-control" />
							</div>
							<div class='input-group'>
								<div class='input-group-addon'>To <i class='fa fa-calendar'></i></div>
								<input type="text" name="date[end][high]" id="endDateHigh" class="dateInput form-control" />
							</div>
						</div>
					</div>
					<div class="form-group form-inline">
						<label class='control-label col-xs-12' style='text-align: left;'>Duration</label>
						<div class="col-xs-12">
							<div class='input-group'>
								<div class='input-group-addon'>From</div>
								<input type="text" name="date[duration][low][num]" class="form-control" />
							</div>
							<div class='input-group'>
								<select name="date[duration][low][multiplier]" class='form-control'>
									<option value="0">Select</option>
									<option value="86400">day(s)</option>
									<option value="604800">week(s)</option>
									<option value="2419200">month(s)</option>
									<option value="31536000">year(s)</option>
								</select>
							</div>
							<br />
							<div class='input-group'>
								<div class='input-group-addon'>To</div>
								<input type="text" name="date[duration][high][num]" class="form-control" />
							</div>
							<div class='input-group'>
								<select name="date[duration][high][multiplier]" class='form-control'>
									<option value="0">Select</option>
									<option value="86400">day(s)</option>
									<option value="604800">week(s)</option>
									<option value="2419200">month(s)</option>
									<option value="31536000">year(s)</option>
								</select>
							</div>
						</div>
					</div>
				</div>
			</fieldset>
		</div>
		<div class='col-xs-12 col-sm-3'>
			<fieldset>
				<legend>Extras</legend>
				<div style="height: 434px;">
					<div class="form-group">
						<label class='control-label col-xs-12 col-sm-5'>Bolding</label>
						<div class="col-xs-12 col-sm-6">
							<input type="radio" name="bolding" value="indif" checked="checked" /> Indifferent<br />
							<input type="radio" name="bolding" value="1" /> Yes<br />
							<input type="radio" name="bolding" value="0" /> No
						</div>
					</div>
					<div class="form-group">
						<label class='control-label col-xs-12 col-sm-5'>Better placement</label>
						<div class="col-xs-12 col-sm-6">
							<input type="radio" name="better_placement" value="indif" checked="checked" /> Indifferent<br />
							<input type="radio" name="better_placement" value="1" /> Yes<br />
							<input type="radio" name="better_placement" value="0" /> No
						</div>
					</div>
					<div class="form-group">
						<label class='control-label col-xs-12 col-sm-5'>Attention getter</label>
						<div class="col-xs-12 col-sm-6">
							<input type="radio" name="attention_getter" value="indif" checked="checked" /> Indifferent<br />
							<input type="radio" name="attention_getter" value="1" /> Yes<br />
							<input type="radio" name="attention_getter" value="0" /> No
						</div>
					</div>
					<div class="form-group">
						<label class='control-label col-xs-12 col-sm-5'>Featured Levels</label>
						<div class="col-xs-12 col-sm-6">
							<input type="checkbox" name="featured_ad" value="1" /> One<br />
							<input type="checkbox" name="featured_ad_2" value="1" /> Two<br />
							<input type="checkbox" name="featured_ad_3" value="1" /> Three<br />
							<input type="checkbox" name="featured_ad_4" value="1" /> Four<br />
							<input type="checkbox" name="featured_ad_5" value="1" /> Five
						</div>
					</div>
				</div>
			</fieldset>
		</div>
		<div class='col-xs-12 col-sm-3'>
			<fieldset>
				<legend>Categories</legend>
				<div style="height: 434px;">
					<p>Select which categories to export:</p>
					<select name="category[]" size="10" multiple="multiple" id="catMultiselect" class='form-control'>
						{$categories}
					</select>
					<div class="page_note">{$multiSelect}</div>
				</div>
			</fieldset>
		</div>
		
		<div class="clear"></div>
	</div>
	
	<div class="tabContents" id="dataTabContents">
	
		<div class="form-group">
			<label class='control-label col-xs-12 col-sm-5'>Export Type</label>
			<div class="col-xs-12 col-sm-6">
				<input type="radio" name="exportType" value="xml" class="exportTypeRadio" checked="checked" /> XML<br />
				<input type="radio" name="exportType" value="csv" class="exportTypeRadio" /> CSV
			</div>
		</div>
		<div class="form-group">
			<label class='control-label col-xs-12 col-sm-5'>Export To</label>
			<div class="col-xs-12 col-sm-6">
				<div class='input-group'>
					<div class='input-group-addon'>addons/exporter/exports/</div>
					<input type="text" name="filename" value="export" size="10" class='form-control' />
					<span class='input-group-addon' id="filenameExtension">.xml</span>
				</div>
			</div>
		</div>
	
		<div class='col-xs-12 col-sm-4'>
			<fieldset>
				<legend>Main Fields</legend>
				<div>
					<p class="page_note">Select which fields from the main listing table you would like the export to contain.</p>
					<select name="show[]" size="5" multiple="multiple" class='form-control'>
						<option value="id" selected="selected">ID</option>
						<option value="title" selected="selected">Title</option>
						<option value="description" selected="selected">Description</option>
						<option value="category">Category ID</option>
						<option value="seller">Seller ID</option>
						<option value="email">E-Mail</option>
						<option value="date">Date</option>
						<option value="duration">Duration (Days)</option>
						<option value="item_type">Item type</option>
						<option value="live">Status</option>
						<option value="image">Image Count</option>
						<option value="price">Price</option>
						<option value="high_bidder">High Bidder ID</option>
						<option value="reserve_price">Reserve Price</option>
						<option value="location_city">City</option>
						<option value="location_state">State</option>
						<option value="location_country">Country</option>
						<option value="location_zip">Zip code</option>
						<option value="phone">Phone 1</option>
						<option value="phone2">Phone 2</option>
						<option value="fax">Fax</option>
						<option value="url_link_1">URL Link 1</option>
						<option value="url_link_2">URL Link 2</option>
						<option value="url_link_3">URL Link 3</option>
						<option value="email">E-Mail</option>
						<option value="mapping_location">Mapping Location</option>				
						<option value="better_placement">Better placement</option>
						<option value="optional_field_1">Optional field 1</option>
						<option value="optional_field_2">Optional field 2</option>
						<option value="optional_field_3">Optional field 3</option>
						<option value="optional_field_4">Optional field 4</option>
						<option value="optional_field_5">Optional field 5</option>
						<option value="optional_field_6">Optional field 6</option>
						<option value="optional_field_7">Optional field 7</option>
						<option value="optional_field_8">Optional field 8</option>
						<option value="optional_field_9">Optional field 9</option>
						<option value="optional_field_10">Optional field 10</option>
						<option value="optional_field_11">Optional field 11</option>
						<option value="optional_field_12">Optional field 12</option>
						<option value="optional_field_13">Optional field 13</option>
						<option value="optional_field_14">Optional field 14</option>
						<option value="optional_field_15">Optional field 15</option>
						<option value="optional_field_16">Optional field 16</option>
						<option value="optional_field_17">Optional field 17</option>
						<option value="optional_field_18">Optional field 18</option>
						<option value="optional_field_19">Optional field 19</option>
						<option value="optional_field_20">Optional field 20</option>
						<option value="featured_ad">Featured level 1</option>
						<option value="featured_ad_2">Featured level 2</option>
						<option value="featured_ad_3">Featured level 3</option>
						<option value="featured_ad_4">Featured level 4</option>
						<option value="featured_ad_5">Featured level 5</option>
						<option value="bolding">Bolding</option>
						<option value="quantity">Initial Quantity</option>
						<option value="quantity_remaining">Quantity Remaining</option>
					</select>
					<p class="page_note">{$multiSelect}</p>
				</div>
			</fieldset>
		</div>
		<div class='col-xs-12 col-sm-4'>
			<fieldset>
				<legend>Additional Fields</legend>
				<div>
					<p class="page_note">Select which information pulled from additional tables in the database you would
						like the export to contain.</p>
					<select name="show_extra[]" size="3" multiple="multiple" id="dataExtra" class='form-control'>
						<option value="img_url_1">Main Image URL</option>
						<option value="img_url_all">All Image URLs</option>
						<option value="extra_questions">Extra Questions</option>
					</select>
					<p class="page_note">{$multiSelect}</p>
				</div>
			</fieldset>
		</div>
		<div class='col-xs-12 col-sm-4'>
			<fieldset>
				<legend>Field Formatting</legend>
				<div>
					<p class="page_note">These settings affect how certain fields are displayed in the exported data.  They will only
						affect applicable fields as selected in <strong>Main Fields</strong> or <strong>Additional Fields</strong>.</p>
					<div class="form-group">
						<label class='control-label col-xs-12 col-sm-4'>Date/Time Fields</label>
						<div class="col-xs-12 col-sm-8">
							<input type="radio" name="fieldFormat[date]" value="unix" checked="checked" /> 1184618389 <em>(Unix timestamp)</em><br />
							<input type="radio" name="fieldFormat[date]" value="date_time" /> 07/16/2007 - 20:39:49<br />
							<input type="radio" name="fieldFormat[date]" value="date" /> 07/16/2007<br />
							<input type="radio" name="fieldFormat[date]" value="custom" /> 
								Custom (see <a href="http://php.net/date" target="_new">date</a>):<br />
								<input type="text" class='form-control' style="margin-left: 13px;" name="fieldFormat[date_custom]" value="m/d/Y - H:i:s" size="10" />
						</div>
					</div>
					<div class="form-group">
						<label class='control-label col-xs-12 col-sm-4'>Category ID Fields</label>
						<div class="col-xs-12 col-sm-8">
							<input type="radio" name="fieldFormat[category]" value="id" checked="checked" /> ID Only<br />
							<input type="radio" name="fieldFormat[category]" value="name_id" /> Name &amp; ID<br />
							<input type="radio" name="fieldFormat[category]" value="name" /> Name Only
						</div>
					</div>
				</div>
			</fieldset>
		</div>
		<div class="clear"></div>
	</div>
	<div class="tabContents" id="saveTabContents">
		<div>
			<div class='col-xs-12 col-sm-6'>
				<fieldset>
					<legend>Save</legend>
					<div>
						<div class="page_note">Save export settings currently set for use later.</div>
						<div class="form-group">
							<label class='control-label col-xs-12 col-sm-4'>Save Name</label>
							<div class="col-xs-12 col-sm-8">
								<input type="text" name="save_name" value="" class='form-control' />
							</div>
						</div>
						<div class="center"><a href="#" id="saveButton" class="btn btn-xs btn-info">Save Settings</a></div>
					</div>
				</fieldset>
			</div>
			<div class='col-xs-12 col-sm-6'>
				<fieldset>
					<legend>Load</legend>
					<div>
						<p class="page_note">Load any previously saved export settings.</p>
						<div id="loadTable">
							{include file='admin/load_settings_table.tpl'}
						</div>
					</div>
				</fieldset>
			</div>
		</div>
		<div class="clear"></div>
	</div>
</form>
<br />
{$exportButton}
<div class="clear"></div>