{* 6.0.7-3-gce41f93 *}

<div>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn"><input type="checkbox" name="listing_renew_upgrade[renew_reset_start]" value="1" {if $renew_reset_start}checked="checked"{/if} /></div>
		<div class="rightColumn">Renew: Always Reset Start Time</div>
		<div class="clearColumn"></div>
	</div>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn"><input type="checkbox" name="listing_renew_upgrade[no_live_downgrade]" value="1" {if $no_live_downgrade}checked="checked"{/if} /></div>
		<div class="rightColumn">Renew: Block removing extras early</div>
		<div class="clearColumn"></div>
	</div>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn"><input type="checkbox" name="listing_renew_upgrade[upgrade_reset_start]" value="1" {if $upgrade_reset_start}checked="checked"{/if} /></div>
		<div class="rightColumn">Upgrade: Reset Start & End Times</div>
		<div class="clearColumn"></div>
	</div>
</div>