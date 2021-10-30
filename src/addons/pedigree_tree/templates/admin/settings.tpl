{* 16.09.0-106-ge989d1f *}
{$adminMessages}
<fieldset>
	<legend>Pedigree Tree Settings</legend>
	<div>
		<div class="page_note">
			<strong>Fields to Use Settings:</strong>  Note that there are more settings specific to Pedigree Tree fields on the page <a href="index.php?page=fields_to_use">Listing Setup > Fields to Use</a> in the admin panel.
			If the Pedigree Tree does not show when placing or editing a listing, check there to make sure it is enabled site-wide or for that specific category/user group.
			<br /><br />
			<strong>Display in Listing:</strong>  Don't forget to add the addon tag to your listing details template(s) so that the pedigree tree information displays for each listing.  The tag to add will be:<br />
			<br />
			<div class="center">{ldelim}addon author='geo_addons' addon='pedigree_tree' tag='listing_tree'}</div>
		</div>
		<form action="" method="post" class='form-horizontal'>
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>
				Preserve Uppercase in Names?<br />
						<span class="small_font">(May affect searches)</span></label>
				<div class="col-xs-12 col-sm-6">
					<input type="checkbox" id="allowUppercase" name="allowUppercase"{if $allowUppercase} checked="checked"{/if} />
				</div>
			</div>
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>Max # Generations</label>
				<div class="col-xs-12 col-sm-6"><input type="number" min="0" class="form-control" name="maxGens" value="{$maxGens}" /></div>
			</div>
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>Required # Generations</label>
				<div class="col-xs-12 col-sm-6"><input type="number" min="0" class="form-control" name="maxReqGens" value="{$maxReqGens}" /></div>
			</div>
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>Image Icons to Use</label>
				<div class="col-xs-12 col-sm-6">
					{foreach from=$icon_sets item=set_url key=set_value}
						<input type="radio" name="iconSet" value="{$set_value}"{if $iconSet==$set_value} checked="checked"{/if} /> 
						<image src="../{external file=$set_url.sire}" alt="" style="vertical-align: middle; margin: 1px;" /> 
						<image src="../{external file=$set_url.dam}" alt="" style="vertical-align: middle; margin: 1px;" />
						<br />
					{/foreach}
					<input type="radio" name="iconSet" value="none"{if $iconSet==none} checked="checked"{/if} /> None
				</div>
			</div>
			<div class="center"><input type="submit" name="auto_save" value="Save" /></div>
		</form>
	</div>
</fieldset>