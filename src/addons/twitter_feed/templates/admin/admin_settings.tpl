{* 16.09.0-102-g925bc56 *}

{$adminMsgs}

<form action="" method="post" class="form-horizontal form-label-left">

	<fieldset>
		<legend>Embedded Timeline Appearance</legend>
		<div class='x_content'>
				<p class="page_note">
					<strong>IMPORTANT:</strong> These settings will override any defaults or user-selected settings. When creating a timeline widget,
					users have several display options to select from. If you wish to allow the users' choices to take effect, leave these settings blank (or on 0)
				</p>
				
				<div class='form-group'>
				<label class='control-label col-md-5 col-sm-5 col-xs-12'>Maximum Number of Tweets to Show: </label>
				  <div class='col-md-6 col-sm-6 col-xs-12'>
				  <input type="text" name="d[tweet_limit]" class='form-control col-md-7 col-xs-12' value="{$config.tweet_limit}" size="4" /> <span class="small_font">(1-20, or blank for no limit and to show new tweets as they happen)</span>
				  </div>
				</div>				 

				 <div class="form-group">
				  <label class="control-label col-md-5 col-sm-5 col-xs-12">Dimensions of Widget:</label>
					<div class="col-md-6 col-sm-6 col-xs-12">
					  <div class="input-group" style='margin-bottom:0;' >
						<div class="input-group-addon">Width</div>
						<input type="text" name="d[width]" value="{$config.width}" class="form-control col-md-7 col-xs-12" size="4" id="width" />
						<div class="input-group-addon">pixels</div>
					  </div>
					  <div style='margin-bottom:10px;'><span class="small_font">(must be between 180 and 520, or blank to allow User Select)</span></div>
					  <div class="input-group" style='margin-bottom:0;'>
						<div class="input-group-addon">Height</div>
						<input type="text" name="d[height]" class="form-control col-md-7 col-xs-12" value="{$config.height}" size="4" />
						<div class="input-group-addon">pixels</div>
					  </div>
					  <div style='margin-bottom:10px;'><span class="small_font">(must be greater than 200, or blank to allow User Select)</span></div>
					</div>
				  </div>  

				<div class='form-group'>
				<label class='control-label col-md-5 col-sm-5 col-xs-12'>Widget Theme: </label>
				  <div class='col-md-6 col-sm-6 col-xs-12'>
				  	<div class='input-group'>
				  	  <div class='input-group-addon'>Light or Dark?</div>
					  <select name="d[theme]" class='form-control col-md-7 col-xs-12'>
						<option value="">User Selected</option>
						<option value="light" {if $config.theme == 'light'}selected="selected"{/if}>Always Light</option>
						<option value="dark" {if $config.theme == 'dark'}selected="selected"{/if}>Always Dark</option>
					  </select>
					</div>
				  	<div class='input-group'>
				  	  <div class='input-group-addon' style='vertical-align:top; padding-top: 8px;'>Link Color: #</div>
					  <input type="text" id="link_color" name="d[link_color]" onchange="colorizeSwatches()" class="form-control col-md-7 col-xs-12" value="{$config.link_color}" size="6" placeholder="example: FF0000" /> <span id="swatch_link_color" style="border: 1px solid black; padding: 1px; font-size: 12px;">Color Swatch</span>
					</div>
				  	<div class='input-group'>
				  	  <div class='input-group-addon' style='vertical-align:top; padding-top: 8px;'>Border Color: #</div>
					  <input type="text" id="border_color" name="d[border_color]" onchange="colorizeSwatches()" class="form-control col-md-7 col-xs-12" value="{$config.border_color}" size="6" placeholder="example: FF0000" /> <span id="swatch_border_color" style="border: 1px solid black; padding: 1px; font-size: 12px;">Color Swatch</span>
					</div>
					<script type="text/javascript">
					colorizeSwatches = function() {
						$('swatch_link_color').style.backgroundColor = "#" + $('link_color').value;
						$('swatch_border_color').style.backgroundColor = "#" + $('border_color').value;
					}
					Event.observe('window','load',colorizeSwatches());
					</script>
				  </div>
				</div>

				<div class='form-group'>
				<label class='control-label col-md-5 col-sm-5 col-xs-12'>Chrome Options: </label>
				  <div class='col-md-7 col-sm-7 col-xs-12' style='margin-top: 8px;'>
					See <a href="https://dev.twitter.com/docs/embedded-timelines#customization">this page</a> for a description of these options<br />
					<input type="checkbox" name="d[chrome][noheader]" value="1" {if $config.chrome.noheader}checked="checked"{/if}> noheader<br />
					<input type="checkbox" name="d[chrome][nofooter]" value="1" {if $config.chrome.nofooter}checked="checked"{/if}> nofooter<br />
					<input type="checkbox" name="d[chrome][noborders]" value="1" {if $config.chrome.noborders}checked="checked"{/if}> noborders<br />
					<input type="checkbox" name="d[chrome][noscrollbar]" value="1" {if $config.chrome.noscrollbar}checked="checked"{/if}> noscrollbar<br />
					<input type="checkbox" name="d[chrome][noscrollbar]" value="1" {if $config.chrome.noscrollbar}checked="checked"{/if}> transparent
				  </div>
				</div>	
							
		</div>
	</fieldset>
	
	<fieldset>
		<legend>Default Timeline</legend>
		<div class='x_content'>
			<p class="page_note">You can specify a Timeline to be shown on listings where the seller does not specify one of his own. 
			See the Help page for assistance finding the correct values to enter here</p>

			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Default "href": </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
			  <input type="text" class='form-control col-md-7 col-xs-12' name="d[default_href]" value="{$config.default_href}" size="10" />
			  </div>
			</div>

			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Default "widget-data-id": </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
			  <input type="text" name="d[default_data_id]" class='form-control col-md-7 col-xs-12' value="{$config.default_data_id}" size="10" />
			  </div>
			</div>			
		</div>
	</fieldset>	
	
	<div style="margin: 0 auto; width: 200px;"><input type="submit" class="button" name="auto_save" value="Save"/></div>

</form>