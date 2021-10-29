{* 16.09.0-79-gb63e5d8 *}
<div class="page_note_error">
<strong>Warning</strong>: Changing settings on this page can have drastic effects if your server
is not configured correctly.  It is important that you <strong>consult the user manual</strong>,
 so that you may fully understand what is happening, before changing any settings on this page.
 </div>

<fieldset id="cache_stats_fieldset">
	<legend>Cache Stats</legend>
	<div class='form-horizontal form-label-left'>
	
		<div id="cache_stats_fieldsetContents" class='x_content'>

			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Cache Storage Method: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
				<span class='vertical-form-fix'>{$GEO_CACHE_STORAGE}</span>
			  </div>
			</div>

	{if $use_storage_cache ne 1}
		{if $GEO_CACHE_STORAGE eq 'filesystem'}
		
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Main Cache Directory: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
				<span class='vertical-form-fix'>{$CACHE_DIR}</span>
			  </div>
			</div>
		
			{if $geoCache_is_not_writable eq 1}
			
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Cache directory not writable!: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
				<span class='vertical-form-fix'>Make sure the directory exists, and is writable (Usually by using CHMOD 777).<br />The cache may not be able to be updated or added to until the directory is writable.</span>
			  </div>
			</div>

			{/if}
		{elseif $GEO_CACHE_STORAGE eq 'memcache'}
		
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Memcache Setting Prefix: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
				<span class='vertical-form-fix'>{$smarty.const.GEO_MEMCACHE_SETTING_PREFIX}</span>
			  </div>
			</div>

			{if $memcache_exists eq 0}
			
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'><strong style="color:red">Memcache PHP extension not installed!</strong>: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
				<span class='vertical-form-fix'>In your config.php, you currently have the setting GEO_CACHE_STORAGE set to memcache. However it appears that the Memcache extension for PHP is not installed.  Either install the Memcache extension (talk to your host for this), or change the setting to &quot;filesystem&quot; so that it uses file-based caching.</span>
			  </div>
			</div>
			
			{/if}
		{/if}
	{/if}

			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Total # Cached Items: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
				<span class='vertical-form-fix'>{$countTOT}</span>
			  </div>
			</div>

			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Cache Item Breakdown: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
				<span class='vertical-form-fix'>Module/Page Data <em>(per module, page)</em>: {$countM}<br />
					Module/Page Output <em>(per module, page, category, language, logged in status)</em>: {$countP}<br />
					Setting/Design Data <em>(per setting &quot;type&quot;)</em>: {$countS}<br />
					Text Data <em>(per page, module, language)</em>: {$countTXT}</span>
			  </div>
			</div>

			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Cache Controls: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
				<span class='vertical-form-fix'>					
					<form action='index.php?page=clear_cache' method='post'>
						<input type='submit' name='auto_save' value='Clear All Cache' /><br />
						<input type='submit' name='auto_save' value='Clear Output Cache'/><br />
						<input type='submit' name='auto_save' value='Clear Data Cache'/><br />
					</form></span>
			  </div>
			</div>			

		</div>
	</div>
</fieldset>
<form action='index.php?page=cache_config' method='post'>
	<fieldset id="cache_manage_general_settings_fieldset">
		<legend>General Settings</legend>
		<div class='form-horizontal form-label-left'>		
			<div class='x_content'>		
				<div class='form-group'>
				<label class='control-label col-md-5 col-sm-5 col-xs-12'>Cache System: </label>
				  <div class='col-md-6 col-sm-6 col-xs-12'>
					<input type="radio" name="use_cache" value="1" {if $use_cache eq 1}checked='checked' {/if}/>On&nbsp;
					<input type="radio" name="use_cache" value="0" {if $use_cache eq 0}checked='checked' {/if}/>Off
				  </div>
				</div>
			</div>
		</div>

	</fieldset>
	<div style="text-align: center;"><input type=submit value="Save" name="auto_save" /></div>
</form>