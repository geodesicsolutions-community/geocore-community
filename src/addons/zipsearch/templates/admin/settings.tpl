{* 16.09.0-106-ge989d1f *}

{$adminMsgs}

<fieldset>
	<legend>Data Import Information</legend>
	{if $alreadyExisting > 0}
		<div>
			<p class="page_note">Imported zip/postal code information was found in the database.</p>
			<strong>Last Import Performed:</strong> {if $lastRun}{$lastRun|date_format}{else}Never{/if}<br /><br />
			<strong>Existing Zip/Postal Data:</strong> {if $alreadyExisting}{$alreadyExisting} Entries{else} N/A{/if}<br /><br />
		</div>
	{else}
		<div>No zip/postal code information was found in the database. Be sure to <a href="index.php?page=insertZipData">import</a> some.</div>
	{/if}
</fieldset>

<fieldset>
	<legend>Settings</legend>
	<div>
		<form action="" method="post" class='form-horizontal'>
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>Zipsearch Enabled</label>
				<div class="col-xs-12 col-sm-6">
					<input type="checkbox" name="enabled" value="1" {if $enabled == 1}checked="checked"{/if} />
				</div>
			</div>
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>Distance Units</label>
				<div class="col-xs-12 col-sm-6">
					<input type="radio" name="units" value="M"{if $units=='M'} checked="checked"{/if} /> miles<br />
					<input type="radio" name="units" value="km"{if $units=='km'} checked="checked"{/if} /> kilometers
				</div>
				<div class="clearColumn"></div>
			</div>
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>Search Method</label>
				<div class="col-xs-12 col-sm-6">
					<input type="radio" name="search_method" value="exact"{if $search_method=='exact'} checked="checked"{/if} onclick="if(this.checked)jQuery('#ht').hide();" /> Exact Match (US/Germany/similar)<br />
					<input type="radio" name="search_method" value="hierarchical"{if $search_method=='hierarchical'} checked="checked"{/if} onclick="if(this.checked)jQuery('#ht').show();" /> Hierarchical Match (UK/Canada/similar)
					<div id="ht" style="{if $search_method!='hierarchical'}display: none;{/if} margin-left: 40px;">
						Advanced: omit <input type="number" size="1" name="hierarchical_trim" value="{$hierarchical_trim}" /> trailing characters omitted from data (most use the default: 3)
					</div>
				</div>
			</div>
			
			<div class="center">
				<input type="submit" name="auto_save" value="Save" />
			</div>
		</form>
	</div>
</fieldset>