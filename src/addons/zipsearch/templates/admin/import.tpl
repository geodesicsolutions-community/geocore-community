{* 7.6.3-3-g86fea4d *}

{$adminMsgs}

{if !$continue||($choose && $alreadyExisting)}
	<fieldset>
		<legend>Previous Import Information</legend>
		<div>
			<p class="page_note">Existing zip/postal code information was found in the database.  This existing data will be removed upon importing new data.</p>
			<strong>Last Import Performed:</strong> {if $lastRun}{$lastRun|date_format}{else}Never{/if}<br /><br />
			<strong>Existing Zip/Postal Data:</strong> {if $alreadyExisting}{$alreadyExisting} Entries{else} N/A{/if}<br /><br />
		</div>
	</fieldset>
{/if}

<fieldset>
	<legend>Import Zip/Postal Code Data</legend>
	<div>
		<p class="page_note">Note that this can take some time, there is a lot of data to be imported.</p>
		{if $choose}
			<form method="get" action="">
				<input type="hidden" name="page" value="insertZipData" />
				<input type="hidden" name="step" value="0" />
				
				<strong>Choose Zip/Postal Data to Import</strong>{if $alreadyExisting}  (This will remove any previously imported data){/if}<br /><br />
				{foreach from=$types item=type}
					<label>
						<input type="checkbox" name="selectedTypes[]" value="{$type->getType()}" class="selectedTypes"
							{if $type->disableCheck()}disabled="disabled"{/if} /> {$type->getLabel()}
					</label>
					<br />
				{/foreach}
				<br /><br />
				<input type="submit" value="{$continue}" class="mini_button" />
			</form>
		{else}
			{if $type}
				<p class="page_note">
					<strong>Currently Importing: </strong> {$type->getLabel()}<br />
					<strong>Import Data Info:</strong> {$type->getInfo()}
				</p>
				<br />
			{/if}
			{$data}
			<br /><br />
			{if $continue}
				<a href="index.php?page=insertZipData&amp;step={$nextStep}&amp;currentType={$currentType}&amp;selectedTypes={$selectedTypes}" id="continueButton" class="mini_button">{$continue}</a>
			{/if}
		{/if}
	</div>
</fieldset>