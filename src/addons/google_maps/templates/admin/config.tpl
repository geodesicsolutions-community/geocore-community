{* 17.10.0-23-g40dab80 *}
{$errors}
<style>

.map-container {
	position: relative;
	width: 37em;
	height: 25em;
	max-width: 95%;
	margin: 0 auto;
}

</style>
<fieldset>
	<legend>Google Maps</legend>
	<div class='x_content'>
		<form action="" method="post" class='form-horizontal'>
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>Google API Key Instructions (Requires Google Maps API v3)</label>
				<div class="col-xs-12 col-sm-6 vertical-form-fix"><a href="https://developers.google.com/maps/documentation/javascript/tutorial#api_key" onclick="window.open(this.href); return false;">Click here for directions.</a></div>
			</div>
			{if !$googleApiKey}
				<input type="hidden" name="noApiKey" value="1" />
			{else}
				<div class="form-group">
					<label class='control-label col-xs-12 col-sm-5'>Template Tag</label>
					<div class="col-xs-12 col-sm-6 vertical-form-fix">
						{ldelim}listing addon='google_maps' tag='listing_map'}
					</div>
				</div>
			{/if}
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>Google API key</label>
				<div class="col-xs-12 col-sm-6">
					<input type="text" name="googleApiKey" value="{$googleApiKey}" class='form-control' />
				</div>
			</div>
			{if $googleApiKey}
				<div class="form-group">
					<label class='control-label col-xs-12 col-sm-5'>Map Size</label>
					<div class="col-xs-12 col-sm-6 vertical-form-fix">
						Change the map size by setting width / height in your
						custom.css file, for the CSS class <strong>.map-container</strong> -
						note that the default size is used below in the preview.
					</div>
				</div>
				<div class="form-group">
					<label class='control-label col-xs-12 col-sm-5'>Google Maps Enabled</label>
					<div class="col-xs-12 col-sm-6 ">
						<input type="checkbox" name="on" value="1" {if !$off}checked="checked"{/if} />
					</div>
				</div>
			{/if}
			<div class='center'>
				<input type="submit" name="auto_save" value="Save" />
			</div>
		</form>
	</div>
</fieldset>


{if $preview}
	<fieldset>
		<legend>Maps Preview</legend>
		<div>
			{$preview}
		</div>
	</fieldset>
{/if}
{if $googleApiKey && $jsonResponse !== "OK"}
	<fieldset>
		<legend>Google Response Details</legend>
		<p class="page_note">The preview request to Google's API returned a response other than "OK." The exact response is shown below to aid in debugging. If you need assistance, contact Support.</p>
		<div style="border: thin solid; padding: 2px;">
			Google Response:<br>
			{if $jsonResponse == "OK"}
				<span STYLE="font-size: x-large; color: green;">{$jsonResponse}</span>
			{elseif $jsonResponse|count_characters != 0 }
				<span STYLE="font-size: x-large; color: red;">{$jsonResponse}</span>
			{elseif $jsonResponse|count_characters == 0 }
				<span STYLE="font-size: x-large; color: red;">No response from Google -- the entered API key is likely invalid.</span>
			{/if}
		</div>		
		{if $jsonResponse != "OK" && $jsonResponse|count_characters != 0}				
		<div style="border: thin solid; padding: 2px;">
			Google Url Response:<br><pre>{$googleResponse}</pre>
		</div>
		{/if}
	</fieldset>
{/if}
