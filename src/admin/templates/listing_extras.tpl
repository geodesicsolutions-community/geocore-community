{* 16.09.0-79-gb63e5d8 *}
{literal}
<script type="text/javascript">
	//<![CDATA[
	Event.observe(window, 'load', function () {
		$('rotateEnabled').observe('click', toggleRotateSettings);
		toggleRotateSettings();
	});
	var toggleRotateSettings = function () {
		//show/hide the settings depending on if rotation is enabled/disabled.
		$('rotateSettings')[($('rotateEnabled').checked? 'show' : 'hide')]();
	}
	//]]>
</script>
{/literal}
<fieldset>
	<legend>Better Placement Extra Settings</legend>
	<div class='x_content'>
	
		<div class='form-group'>
		<label class='control-label col-md-5 col-sm-5 col-xs-12'></label>
		  <div class='col-md-7 col-sm-7 col-xs-12'>
		    <input type="checkbox" name="b[use_better_placement_feature]" id="use_better_placement_feature" value="1" {if $use_better_placement_feature} checked="checked"{/if} />&nbsp;
		    Enable Better Placement
		  </div>
		</div>	

		<div class='form-group'>
		<label class='control-label col-md-5 col-sm-5 col-xs-12'></label>
		  <div class='col-md-7 col-sm-7 col-xs-12'>
		    <input type="checkbox" name="b[{$prefix}rotate]" value="1" {if $rotate} checked="checked"{/if} id="rotateEnabled" />&nbsp;
		    Enable Better Placement Rotation
		  </div>
		</div>	
		
		<div id="rotateSettings">
		
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Rotate 1 Listing per: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
			    <input type="radio" name="b[{$prefix}perCategory]" value="1" {if $perCategory} checked="checked"{/if} /> Terminal Category<br />
			    <input type="radio" name="b[{$prefix}perCategory]" value="0" {if !$perCategory} checked="checked"{/if} /> Entire Site
			  </div>
			</div>
		
		     <div class='form-group'>
		      <label class='control-label col-md-5 col-sm-5 col-xs-12'>Interval - Rotate Listings every: </label>
			<div class='col-md-6 col-sm-6 col-xs-12 input-group'>
			<input type="text" name='b[rotationInterval]' size='5' value='{$adjustedInterval}' class='form-control col-md-7 col-xs-12 input-group-width40' />
			<select name='b[rotationIntervalUnit]' class='form-control col-md-7 col-xs-12 input-group-width60' style='border-right:1px solid #ccc;'>
				<option value='{$day}'{if $rotateUnit==$day} selected="selected"{/if}>Days</option>
				<option value='{$hour}'{if $rotateUnit==$hour} selected="selected"{/if}>Hours</option>
				<option value='{$minute}'{if $rotateUnit==$minute} selected="selected"{/if}>Minutes</option>
				<option value='1'{if $rotateUnit==1} selected="selected"{/if}>Seconds</option>
			</select>
			{if $rotate}
				{* Only show link if already turned on *}
				<a href="../cron.php?action=cron&amp;cron_key={$cronKey|escape}&amp;verbose=1&amp;tasks=better_placement_rotation" class="mini_button" onclick="window.open(this.href); return false;">
					Rotate Listings Now
				</a>
			{/if}
			</div>
		      </div>		

		</div>
	</div>
</fieldset>