{* 16.09.0-96-gf3bd8a1 *}

{$adminMessages}

{literal}
<script type="text/javascript">
	//<![CDATA[
	//simple JS to show/hide fields
	Event.observe(window,'load', function () {
		var toggleDomains = function (action) {
			$('subdomainSettingsLink')[($('subdomainOff').checked)? 'hide' : 'show']();
			$('subdomainSettingsAdd')[($('subdomainOff').checked)? 'hide' : 'show']();
			$('forceSubdomainListing')[($('subdomainOn').checked)? 'show' : 'hide']();
		};
		$('subdomainOff').observe('change', toggleDomains);
		$('subdomainConfig').observe('change', toggleDomains);
		$('subdomainOn').observe('change', toggleDomains);
		toggleDomains(false);
	});
	
	//]]>
</script>

{/literal}
<form action="" method="post" id="subdomainsForm" class='form-horizontal form-label-left' >

<fieldset>
	<legend>Sub Domains</legend>
	<div class='x_content'>
		<p class="page_note_error"><strong>Warning:</strong> The sub-domains listed at bottom of this page must be configured in your hosting control panel to "point" to this installation location prior to enabling the sub-domain feature.  If you do not configure your host properly, enabling sub domains will result in broken links or even a non-functioning site.</p>

		<div class='form-group'>
		<label class='control-label col-md-5 col-sm-5 col-xs-12'>Sub Domain Usage: </label>
		  <div class='col-md-6 col-sm-6 col-xs-12'>
			<input type="radio" name="subdomains" id="subdomainOff" value="0"{if !$subdomains} checked="checked"{/if} /> Not Used<br />
			<input type="radio" name="subdomains" id="subdomainConfig" value="configure"{if $subdomains=='configure'} checked="checked"{/if} /> Configure, but Not Enabled<br />
			<input type="radio" name="subdomains" id="subdomainOn" value="on"{if $subdomains=='on'} checked="checked"{/if} /> Enabled<br />
		  </div>
		</div>

		<div class='form-group' id="forceSubdomainListing">
		<label class='control-label col-md-4 col-sm-4 col-xs-12'></label>
		  <div class='col-md-7 col-sm-7 col-xs-12'>
		    <input type="checkbox" name="forceSubdomainListing"{if $forceSubdomainListing} checked="checked"{/if} />&nbsp;
		    Force full sub-domain for listings?
		  </div>
		</div>	

		<div class='form-group' id="subdomainSettingsLink">
		<label class='control-label col-md-5 col-sm-5 col-xs-12'>Sub Domain Settings: </label>
		  <div class='col-md-7 col-sm-7 col-xs-12'>
		    Set subdomain for each region as the "Unique Name" on page:<br />
				<a href="index.php?page=regions&mc=geographic_setup">Geographic Setup > Regions</a>
		  </div>
		</div>	


		<div class="center">
			<br /><br />
			<input type="submit" name="auto_save" value="Save Settings" class="mini_button" />
			<div id="subdomainSettingsAdd">
				<br /><br />
				<input type="hidden" name="autoAdd" id="autoAdd" value="0" />
				<input type="submit" name="auto_save" value="Auto-Set Subdomains" class="btn btn-primary btn-xs" onclick="$('autoAdd').value='add'; return true;" />
				<input type="submit" name="auto_save" value="Clear All Subdomains" class="btn btn-warning btn-xs" onclick="$('autoAdd').value='clear'; return true;" />
			</div>
		</div>
	</div>
</fieldset>
</form>