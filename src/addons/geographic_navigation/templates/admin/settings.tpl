{* 16.09.0-96-gf3bd8a1 *}

{$adminMessages}
<form method='post' action='' class='form-horizontal form-label-left'>
	<fieldset>
		<legend>General Settings</legend>
		<div class='x_content'>
			<div class='form-group'>
			<label class='control-label col-md-4 col-sm-4 col-xs-12'></label>
			  <div class='col-md-7 col-sm-7 col-xs-12'>
			    <input type="checkbox" name="settings[combineTree]" value="1" {if $combineTree}checked="checked" {/if}/>&nbsp;
			    Combine with Category Breadcrumb
			  </div>
			</div>	
			<div class='form-group'>
			<label class='control-label col-md-4 col-sm-4 col-xs-12'></label>
			  <div class='col-md-7 col-sm-7 col-xs-12'>
			    <input type="checkbox" name="settings[showInSearchBox]" value="1" {if $showInSearchBox}checked="checked" {/if}/>&nbsp;
			    Show in Module Search Box 1
			  </div>
			</div>	
			<div class='form-group'>
			<label class='control-label col-md-4 col-sm-4 col-xs-12'></label>
			  <div class='col-md-7 col-sm-7 col-xs-12'>
			    <input type="checkbox" name="settings[showInTitleListing]" value="1" {if $showInTitleListing}checked="checked" {/if}/>&nbsp;
			    Show listing's region in Title Module
			  </div>
			</div>	
			<div class='form-group'>
			<label class='control-label col-md-4 col-sm-4 col-xs-12'></label>
			  <div class='col-md-7 col-sm-7 col-xs-12'>
			    <input type="checkbox" name="settings[showInTitle]" value="1" {if $showInTitle}checked="checked" {/if}/>&nbsp;
			    Show current selected region in Title Module
			  </div>
			</div>	
			<div class='form-group'>
			<label class='control-label col-md-4 col-sm-4 col-xs-12'></label>
			  <div class='col-md-7 col-sm-7 col-xs-12'>
			    <input type="checkbox" name="settings[terminalSiblings]" value="1" {if $terminalSiblings}checked="checked" {/if}/>&nbsp;
			    Show siblings in navigation when there are no children
			  </div>
			</div>	
			{if showLegacyUrlSetting}
			<div class='form-group'>
			<label class='control-label col-md-4 col-sm-4 col-xs-12'></label>
			  <div class='col-md-7 col-sm-7 col-xs-12'>
			    <input type="checkbox" name="settings[useLegacyUrls]" value="1" {if $useLegacyUrls}checked="checked" {/if}/>&nbsp;
			    Allow Legacy (Geographic Navigation 4.x) URLs
			  </div>
			</div>	
			{/if}
			<div class='form-group'>
			<label class='control-label col-md-4 col-sm-4 col-xs-12'></label>
			  <div class='col-md-7 col-sm-7 col-xs-12'>
			    <input type="checkbox" name="settings[geo_ip]" value="1" {if $geo_ip}checked="checked" {/if} onchange="if(this.checked)jQuery('#geoapi').show();else jQuery('#geoapi').hide();" />&nbsp;
			    <strong>BETA / Experimental</strong>: Automatically assign visitors' regions based on their IPs
					<div id="geoapi" {if !$geo_ip}style="display: none;"{/if}>
						<a href="http://www.ipinfodb.com/register.php">IpInfoDB API key</a> (required): <input type="text" name="settings[geo_ip_apikey]" class="form-control col-md-7 col-xs-12" value="{$geo_ip_apikey}" size="70" /><br>
						<input type="checkbox" name="settings[geo_ip_redirect_ssl]" value="1" {if $geo_ip_redirect_ssl}checked="checked" {/if}/> Redirect to regions https (use if you have configured your full site to be ssl at all times - requires wildcard ssl certificate) 
					</div>
			  </div>
			</div>	
			
		
			<div class="center">
				<input type="submit" name="auto_save" value="Save" />
			</div>
		</div>
	</fieldset>
	
	<fieldset>
		<legend>Navigation Tag Parameters</legend>
			<div class="page_note">Tags Parameters (shown in this <span class="color-primary-two" style="font-weight: bold;">COLOR</span> below) can be added to the <span style="font-weight:bold;">Navigation Template Tag</span>
			(shown in the next section) in order to produce the results associated with that setting.</div>
		<div class='x_content'>
		
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Show Listing Counts: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
				<select name='settings[countFormat]' class='form-control col-md-7 col-xs-12'>
					{html_options options=$countOptions selected=$countFormat}
				</select>
				<span class="color-primary-two" style="font-weight:bold;">use_cat_counts=1</span><br /><span class="small_font">(if added to navigation tag, will reduce listing counts specific to current category)</span>
			  </div>
			</div>
					
			<div class="header-color-primary-mute">Settings below can be changed via Tag Parameters listed under each Value</div>

			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'></label>
			  <div class='col-md-7 col-sm-7 col-xs-12'>
			    <input type="checkbox" name="settings[hideEmpty]" value="1" {if $hideEmpty}checked="checked" {/if}
						onclick="if (this.checked) { jQuery('#hideEmptySpan').text('1'); } else { jQuery('#hideEmptySpan').text('0'); }" />&nbsp;
			    		Hide Empty Regions<br />
					<span class="color-primary-two" style="font-weight:bold;">hideEmpty=<span id="hideEmptySpan">{if $hideEmpty}1{else}0{/if}</span></span>
			  </div>
			</div>	

			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Number of Columns: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
				<input type='text' name='settings[columns]' value='{$columns}' class='form-control col-md-7 col-xs-12' id="columns"
						onchange="$('columnsValue').update(this.value);" />
				<span class="color-primary-two" style="font-weight:bold;">columns=<span id="columnsValue"><script type="text/javascript">document.write($('columns').value);</script></span></span>
			  </div>
			</div>

			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Breadcrumb on Top: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
				<select name="settings[tree]" id="tree" onchange="$('treeValue').update(this.value);" class='form-control col-md-7 col-xs-12'>
					<option value="0"{if !$tree}selected="selected"{/if}>None</option>
					<option value="compact"{if $tree==compact}selected="selected"{/if}>Compact</option>
					<option value="full"{if $tree==full}selected="selected"{/if}>Full Breadcrumb</option>
				</select>
				<span class="color-primary-two" style="font-weight:bold;">tree='<span id="treeValue"><script type="text/javascript">document.write($('tree').value);</script></span>'</span>
			  </div>
			</div>			

			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'></label>
			  <div class='col-md-7 col-sm-7 col-xs-12'>
			    <input type='checkbox' name='settings[showSubs]' id="showSubs" value="1" {if $showSubs}checked="checked" {/if}
					onclick="$('showSubsValue')[((this.checked)? 'show':'hide')]();"/>&nbsp;
			    		Show Sub-Regions
					<br />
					<span id="showSubsValue" style="display: none;">To Hide Subregions: <span class="color-primary-two" style="font-weight:bold;">showSubs=0</span></span>
					<script type="text/javascript">if ($('showSubs').checked) { $('showSubsValue').show(); }</script>
					
			  </div>
			</div>	
			
			<div class="center">
				<input type="submit" name="auto_save" value="Save" />
			</div>
		</div>
	</fieldset>
	
	<fieldset>
		<legend>Navigation Template Tags</legend>
		<div>
			<div class="page_note">Be sure to place these tags in your template where you want the information to be displayed on the page.</div>

			<div class='form-group'>
			<label class='control-label col-md-4 col-sm-4 col-xs-12'>Geographic Navigation: </label>
			  <div class='col-md-8 col-sm-8 col-xs-12'>
			  <span class='color-primary-two vertical-form-fix'>{ldelim}addon author='geo_addons' addon='geographic_navigation' tag='navigation'}</span>
			  </div>
			</div>

			<div class='form-group'>
			<label class='control-label col-md-4 col-sm-4 col-xs-12'>Top Level Geographic Navigation: </label>
			  <div class='col-md-8 col-sm-8 col-xs-12'>
			  <span class='color-primary-two vertical-form-fix'>{ldelim}addon author='geo_addons' addon='geographic_navigation' tag='navigation_top'}</span>
			  </div>
			</div>
			
			<div class='form-group'>
			<label class='control-label col-md-4 col-sm-4 col-xs-12'>Link to Change Selected Region: <br />
					<span class="small_font">(Alternative space-saving option to normal navigation)</span></label>
			  <div class='col-md-8 col-sm-8 col-xs-12'>
			  <span class='color-primary-two vertical-form-fix'>{ldelim}addon author='geo_addons' addon='geographic_navigation' tag='change_region_link'}</span>
			  </div>
			</div>
			
			<div class='form-group'>
			<label class='control-label col-md-4 col-sm-4 col-xs-12'>Listing's Geographic Location: </label>
			  <div class='col-md-8 col-sm-8 col-xs-12'>
			  <span class='color-primary-two vertical-form-fix'>{ldelim}addon author='geo_addons' addon='geographic_navigation' tag='listing_regions'}</span>
			  </div>
			</div>

			<div class='form-group'>
			<label class='control-label col-md-4 col-sm-4 col-xs-12'>Currently Selected Location's Full Breadcrumb: </label>
			  <div class='col-md-8 col-sm-8 col-xs-12'>
			  <span class='color-primary-two vertical-form-fix'>{ldelim}addon author='geo_addons' addon='geographic_navigation' tag='breadcrumb'}</span>
			  </div>
			</div>			

			<div class='form-group'>
			<label class='control-label col-md-4 col-sm-4 col-xs-12'>Currently Selected Location Label: </label>
			  <div class='col-md-8 col-sm-8 col-xs-12'>
			  <span class='color-primary-two vertical-form-fix'>{ldelim}addon author='geo_addons' addon='geographic_navigation' tag='current_region'}</span>
			  </div>
			</div>	

			<div class='form-group'>
			<label class='control-label col-md-4 col-sm-4 col-xs-12'>Inserts JS/CSS into Head: <br />
					<span class="small_font">Allows for Advanced Customization</span></label>
			  <div class='col-md-8 col-sm-8 col-xs-12'>
			  <span class='color-primary-two vertical-form-fix'>{ldelim}addon author='geo_addons' addon='geographic_navigation' tag='insert_head'}</span>
			  </div>
			</div>
			
			<p class="page_note">
				<strong><i class="fa fa-lightbulb-o"></i> Tip:</strong> Place that last tag in a template. Then use the CSS class <strong>geographic_navigation_changeLink</strong> on any element
				to turn it into a "choose location" link.  This works on images or input buttons too!
			</p>
		</div>
	</fieldset>
</form>