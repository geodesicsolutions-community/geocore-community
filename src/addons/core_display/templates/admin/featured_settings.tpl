{* 16.09.0-96-gf3bd8a1 *}
{$adminMsgs}
<form action="" method="post" class='form-horizontal form-label-left'>
	<fieldset>
		<legend>Featured Listing Gallery Settings</legend>
		<div class='x_content'>
		
			<div class='form-group'>
			<label class='control-label col-md-4 col-sm-4 col-xs-12'></label>
			  <div class='col-md-7 col-sm-7 col-xs-12'>
			    <input type="checkbox" name="featured_show_automatically" value="1"
						{if $featured_show_automatically}checked="checked"{/if} />&nbsp;
			   Show featured gallery automatically?
			  </div>
			</div>	

			<div class='form-group'>
			<label class='control-label col-md-4 col-sm-4 col-xs-12'></label>
			  <div class='col-md-7 col-sm-7 col-xs-12'>
			    <input type="checkbox" name="featured_2nd_page" value="1"
						{if $featured_2nd_page}checked="checked"{/if} />&nbsp;
			   Show gallery on 2nd page and up?
			  </div>
			</div>	

			{if $is.classifieds&&$is.auctions}
			<div class='form-group'>
			<label class='control-label col-md-4 col-sm-4 col-xs-12'></label>
			  <div class='col-md-7 col-sm-7 col-xs-12'>
			    <input type="checkbox" name="featured_show_listing_type" value="1"
							{if $featured_show_listing_type}checked="checked"{/if} />&nbsp;
			   Display Listing Type (Classified/Auction)?
			  </div>
			</div>
			{/if}

			<div class='form-group'>
			<label class='control-label col-md-4 col-sm-4 col-xs-12'></label>
			  <div class='col-md-7 col-sm-7 col-xs-12'>
			    <input type="checkbox" name="featured_carousel" value="1"
						{if $featured_carousel}checked="checked"{/if} />&nbsp;
			  Use jQuery simple carousel?
			  </div>
			</div>
			
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Max # of Featured Listings: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
			  <input type="number" name="featured_max_count" class='form-control col-md-7 col-xs-12' value="{$featured_max_count}" size="4" min="0" max="1000000" />
			  </div>
			</div>
			
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'># Columns in Each Row: <br><span class="small_font">(Recommended 5 or less)</span> </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
			  <input type="number" name="featured_column_count" class='form-control col-md-7 col-xs-12' value="{$featured_column_count}" size="4" min="1" max="1000" />
			  </div>
			</div>

			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Show Featured Level(s): </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
				{for $level=1 to 5}
					<label><input type="checkbox" name="featured_levels[{$level}]" value="1"
						{if $featured_levels.$level}checked="checked"{/if} /> Level {$level}</label>
				{/for}
			  </div>
			</div>

			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Thumbnail Max Size: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
			  	<div class='input-group'>
			  	<input type="checkbox" name="dynamic_image_dims" value="1" {if $dynamic_image_dims}checked="checked"{/if} onclick="if(this.checked)jQuery('#static_width').prop('disabled',true); else jQuery('#static_width').prop('disabled',false);" /> Use Dynamic Image Width
			  	</div>
			  	<div class='input-group'>
				<input type="number" id="static_width" name="featured_thumb_width" class='form-control col-md-7 col-xs-12' value="{$featured_thumb_width}" size="3" min="0" max="10000" /><div class='input-group-addon'>pixels (width)</div>
			  	</div>			  	
			 	<div class='input-group'>
				<input type="number" name="featured_thumb_height" class='form-control col-md-7 col-xs-12' value="{$featured_thumb_height}" size="3" min="0" max="10000" /><div class='input-group-addon'>pixels (height)</div>
			  	</div>
			  </div>
			</div>

			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Max Length of Title &amp; Optional Fields: <br> 
					<span class="small_font">(if set to show - 0 for no limit)</span> </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
			  <input type="number" name="featured_title_length" class='form-control col-md-7 col-xs-12' value="{$featured_title_length}" size="3" min="0" max="1000000" />
			  </div>
			</div>
			
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Max Length of Description: <br> 
					<span class="small_font">(if set to show - 0 for no limit)</span> </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
			  <input type="number" name="featured_desc_length" class='form-control col-md-7 col-xs-12' value="{$featured_desc_length}" size="3" min="0" max="1000000" />
			  </div>
			</div>

			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Edit Which Fields are Displayed: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
			  <span class='vertical-form-fix'>Edit on <a href="index.php?page=fields_to_use&amp;activeTab=addons">Listing Setup &rsaquo; Fields to Use</a></span>
			  </div>
			</div>			
			
			<div class="center">
				<input type="submit" name="auto_save" value="Save" />
			</div>
		</div>
	</fieldset>
</form>