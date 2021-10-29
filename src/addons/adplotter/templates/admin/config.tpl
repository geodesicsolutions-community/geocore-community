{* 16.09.0-92-gefaf632 *}
{$adminMsgs}
<form action="" method="post" class="form-horizontal">
	<fieldset>
		<legend>AdPlotter Configuration</legend>
		<div>
			<div class="form-group">
				<label class="control-label col-xs-12 col-sm-5">
					<strong>Activate AdPlotter Integration</strong><br />
					NOTE: This will transmit data about your site, including your Remote API Keys, to Adplotter.<br />However, you will not receive listings until they have fully processed and enabled your site.
				</label>
				<div class="col-xs-12 col-sm-6">
					<input type="checkbox" value="1" name="enabled" {if $enabled}checked="checked"{/if} onclick="if(this.checked){ jQuery('#aff').show(); jQuery('#cat-map').show(); } else { jQuery('#aff').show(); jQuery('#cat-map').hide(); }" />
				</div>
			</div>
			{if $enabled}
				<div class="form-group">
					<label class="control-label col-xs-12 col-sm-5">AdPlotter Integration Status</label>
					<div class="col-xs-12 col-sm-6 vertical-form-fix">
						{if $status_code == 0}
							{* enabled and active *}
							<span style="color: #00C000; font-weight: bold;">ACTIVE: </span>This site has an active AdPlotter subscription and will receive classified listings from AdPlotter. 
						{elseif $status_code == 1}
							{* pending adplotter approval *}
							<span style="color: #C0C000; font-weight: bold;">PENDING: </span>Your registration request has been received by AdPlotter, but not yet approved.
						{elseif $status_code <= -1}
							{* show error message to admin *}
							<span style="color: #FF0000; font-weight: bold;">ERROR: </span>Received an error when checking AdPlotter status. AdPlotter said: {$status_text}
						{elseif $status_code >= 2}
							{* show generic error message *}
							<span style="color: #FF0000; font-weight: bold;">ERROR: </span>Received an error when checking AdPlotter status.
						{/if}
					</div>
				</div>
			{/if}		
		</div>
	</fieldset>
	
	<fieldset id="aff" {if !$enabled}style="display: none;"{/if}>
		<legend>Adplotter Affiliate Code</legend>
		<div class="form-group">
			<label class="control-label col-xs-12 col-sm-5">
				AdPlotter Affiliate Code {$aff_tooltip}
			</label>
			<div class="col-xs-12 col-sm-6">
				<input type="text" name="affiliate_code" value="{$affiliate_code}" class='form-control' />
			</div>
		</div>
		<p style="color: #FF0000; text-align: center; font-weight: bold; font-size: 10pt;">
			NOTE: entering an affiliate code here will activate a "deep" user integration.
			Sellers who create listings on your site will automatically be registered as free users on adplotter.com.<br />
			This will share their names and email addresses with AdPlotter. You will be eligible to receive affiliate commision from purchases made by these created users.
		</p>
	</fieldset>
	
	<fieldset id="cat-map" {if !$enabled}style="display: none;"{/if}>
		<legend>Category Mapping</legend>
		<div>
			<div class="page_note">
				<p>Below is a list of AdPlotter's listing categories. Select which categories on your own site should match up with these.</p>
				<p>If you do not make a selection for a given AdPlotter Category, you will not receive listings in that category.</p>
				<p>If a category has a checkbox next to its name, you can click that checkbox to reveal subcategories.</p>
			</div>
		
			<div class="form-group">
				<label class="control-label col-xs-12 col-sm-5">AdPlotter Category</label>
				<div class="col-xs-12 col-sm-6 vertical-form-fix">GeoCore Category</div>
			</div>
		
		
			{foreach $ap_parents as $topLevel}
				<div class="form-group">
					<label class="control-label col-xs-12 col-sm-5">
						{$topLevel.name|fromDB}
						{if $topLevel.has_subs}
							<input type="checkbox" onclick="RevealSubs(this.checked, {$topLevel.parent_key});" />
						{/if}
					</label>
					<div class="col-xs-12 col-sm-6 vertical-form-fix">
						{$topLevel.ddl}
					</div>
				</div>
				{if $topLevel.has_subs}
					<div id="subs_{$topLevel.parent_key}"></div>
				{/if}
			{/foreach}
			<script type="text/javascript">
				RevealSubs = function(state, parent_key) {
					if(!state) {
						//unchecking the "reveal" box; hide cats if shown, then stop
						jQuery("#subs_"+parent_key).hide();
						return;
					} else if (jQuery("#subs_"+parent_key).html().length > 0) {
						//revealing subs, but we have already ajaxed them and they're just hidden
						jQuery("#subs_"+parent_key).show();
					} else {
						//get subs over ajax
						jQuery("#subs_"+parent_key).html("<div class='center'>Loading...</div>");
						jQuery.get("AJAX.php?controller=addon_adplotter&action=getSubcategories&parent_key="+parent_key, function(result) {
							jQuery("#subs_"+parent_key).html(result).show();
						});
					}
				}
			</script>
		</div>
	</fieldset>
	<div style="text-align: center;"><input type="submit" name="auto_save" value="Save" /></div>
</form>