{* 16.09.0-106-ge989d1f *}

{$admin_msgs}

<form action="" method="post" class='form-horizontal'>
	<fieldset>
		<legend>General Storefront Settings</legend>
		<div class='x_content'>
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>
					Search Engine Friendly URL<br />
					(Requires SEO Addon)
				</label>
				<div class="col-xs-12 col-sm-6">
					<input type='checkbox' name='storefront[sef]' value='1'{if !$seo} disabled="disabled"{elseif $sef} checked="checked"{/if} />
				</div>
			</div>
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>
					Geographic Navigation filter applies to Storefront List<br />
					(Requires Geographic Navigation Addon)	
				</label>
				<div class="col-xs-12 col-sm-6">
					<input type='checkbox' name='storefront[geonav_filter_storefronts]' value='1'{if !$geographic_navigation} disabled="disabled"{elseif $geonav_filter_storefronts} checked="checked"{/if} />
				</div>
			</div>
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>
					Show Traffic Reports
				</label>
				<div class="col-xs-12 col-sm-6"><input type="checkbox" name="storefront[show_traffic]" value="1"{if $show_traffic} checked="checked"{/if} /></div>
			</div>
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>
					Allow Sending Newsletters
				</label>
				<div class="col-xs-12 col-sm-6"><input type="checkbox" name="storefront[allow_newsletter]" value="1"{if $allow_newsletter} checked="checked"{/if} /></div>
			</div>
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>
					Use "Company Name" as default store name
				</label>
				<div class="col-xs-12 col-sm-6"><input type="checkbox" name="storefront[default_storename_to_company]" value="1"{if $default_storename_to_company} checked="checked"{/if} /></div>
			</div>
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>Max logo width (in store) {$size_tooltip}</label>
				<div class="col-xs-12 col-sm-6">
					<div class='input-group'>
						<input type="text" name="storefront[max_logo_width_in_store]" value="{$max_logo_width_in_store}" class='form-control' />
						<div class='input-group-addon'>pixels</div>
					</div> 
				</div>
			</div>
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>Max logo height (in store) {$size_tooltip}</label>
				<div class="col-xs-12 col-sm-6">
					<div class='input-group'>
						<input type="text" name="storefront[max_logo_height_in_store]" value="{$max_logo_height_in_store}" class='form-control' />
						<div class='input-group-addon'>pixels</div>
					</div>
				</div>
			</div>
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>
					Replace Store links in browsing results with each store's logo
				</label>
				<div class="col-xs-12 col-sm-6"><input type="checkbox" name="storefront[use_logo_for_store_links]" value="1"{if $use_logo_for_store_links} checked="checked"{/if} onclick="if(this.checked)jQuery('#browse_logo_sizes').show('fast'); else jQuery('#browse_logo_sizes').hide('fast');"/></div>
			</div>
			<div id="browse_logo_sizes" {if !$use_logo_for_store_links}style="display: none;"{/if}>
				<div class="form-group">
					<label class='control-label col-xs-12 col-sm-5'>Max logo width (in browsing results)</label>
					<div class="col-xs-12 col-sm-6">
						<div class='input-group'>
							<input type="text" name="storefront[max_logo_width_in_browsing]" value="{$max_logo_width_in_browsing}" class='form-control' />
							<div class='input-group-addon'>pixels</div>
						</div>
					</div>
				</div>
				<div class="form-group">
					<label class='control-label col-xs-12 col-sm-5'>Max logo height (in browsing results)</label>
					<div class="col-xs-12 col-sm-6">
						<div class='input-group'>
							<input type="text" name="storefront[max_logo_height_in_browsing]" value="{$max_logo_height_in_browsing}" class='form-control' />
							<div class='input-group-addon'>pixels</div>
						</div>
					</div>
				</div>
			</div>
			<div class="center"><input type="submit" name="auto_save" value="Save" class="mini_button" /></div>
		</div>
	</fieldset>
</form>