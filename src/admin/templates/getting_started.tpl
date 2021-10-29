{* 16.09.0-79-gb63e5d8 *}

{$admin_msgs}
{if $completion > 0} {* gauge.js breaks at 0 *}
	<div style="border: 1px solid #abd9ea; border-radius: 5px; width: 250px; margin: 0 auto; text-align: center;">
		<canvas width="150" height="80" id="completion-gauge" class="" style="width: 160px; height: 100px;"></canvas>
		<div class="goal-wrapper">
		<span id="gauge-text" class="gauge-value"></span>% Complete
		</div>
		<script src="js/gauge.js"></script>
	    <script>
	      var opts = {
	          lines: 12,
	          angle: 0,
	          lineWidth: 0.4,
	          pointer: {
	              length: 0.75,
	              strokeWidth: 0.042,
	              color: '#1D212A'
	          },
	          limitMax: 'false',
	          colorStart: '#1ABC9C',
	          colorStop: '#1ABC9C',
	          strokeColor: '#F0F3F3',
	          generateGradient: true
	      };
	      var target = document.getElementById('completion-gauge'),
	          gauge = new Gauge(target).setOptions(opts);
	
	      gauge.maxValue = 100; //max of 100, because showing as a percentage
	      gauge.animationSpeed = 32;
	      gauge.set({$completion}); //set gauge to have the correct amount filled
	      gauge.setTextField(document.getElementById("gauge-text")); //bind to a text field that animates onload
	    </script>    
	</div>
{/if}

<fieldset>
<legend>Welcome to {if !$white_label}GeoCore{else}the software{/if}!</legend>
<div>

<p style="margin: 5px 0px 0px 20px;">This checklist will help you review the most common configuration options for getting started {if !$white_label}with GeoCore{/if}. We recommend working through this checklist before launch to ensure that your site is fully customized to your liking.
Get started by clicking the <strong>Select a Section</strong> buttons below. You are NOT REQUIRED to enable/utilize any of the checklist items that we have listed. The intent is for you to at least review each item, and check the box to continue.<br /><br />
If you have already done some work customizing your site (or have just upgraded from an older version of {if !$white_label}GeoCore{else}the software{/if}), the checklist can automatically detect any progress you have already made! <a href="index.php?page=checklist&mc=getting_started&sync=yes" class="button">Auto-Detect</a><br /> 
Or, if you like your site just the way it is, you can "Dismiss" this Checklist from displaying. <a href="index.php?page=home&dismiss_gs=yes" class="button">Dismiss</a>
</div>
</fieldset>

<fieldset>
	<legend>Select a Section</legend>
	<div class="center" style="margin: 0px 0px 8px 0px;">
		{foreach $checks as $sectionName => $section}
			{counter name='done' assign='done' print=false start='0'}
			{counter name='total' assign='total' print=false start='0'}
			{foreach $section as $checkName => $check}
				{if $check.isChecked}{counter name='done'}{/if}
				{counter name='total'}
			{/foreach}
			<a onclick="jQuery('.sw').hide('fast'); jQuery('#{$sectionName|replace:' ':''}_wrapper').show('fast'); return false;" class="button" href="#">{$sectionName} ({$done}/{$total})</a> 
		{/foreach}
	</div>
</fieldset>
	
<form action="" method="post">
	
	{foreach $checks as $sectionName => $section}
		<div class="sw" id="{$sectionName|replace:' ':''}_wrapper" style="display: none;">
			<fieldset>
				<legend>{$sectionName}</legend>
				<div>
				
					{foreach $section as $checkName => $check}
						<div class='{cycle values="row_color1,row_color2"}' style="margin: 0 auto; padding: 2px;">
							<input type="hidden" value="0" name="checkboxes[{$sectionName}][{$checkName}]" />
							<input type="checkbox" value="1" id="{$checkName}" name="checkboxes[{$sectionName}][{$checkName}]" {if $check.isChecked}checked="checked"{/if} onclick="checklist_checkItem(this, '{$checkName}')" />
							<label for="{$checkName}" style="cursor: pointer;">
								<strong id="title_{$checkName}" {if $check.isChecked}style="color: #909090;"{/if}>({$check.percentage}%) {$check.name}</strong>
								{if $check.isChecked && !$check.isComplete}
									<span style="color: red;"> - WARNING: This item appears to be incomplete</span>
								{elseif !$check.isChecked && $check.isComplete}
									<span style="color: blue;"> - NOTICE: This item appears to be complete</span>
								{/if}
							</label> 
							<br />
							<p style="padding-left: 50px; {if $check.isChecked}display: none;{/if}" id="description_{$checkName}">{$check.description}</p>
						</div>
					{/foreach}
				
				</div>
			</fieldset>
		</div>
	{/foreach}
	<script type="text/javascript">
		checklist_checkItem = function(e, checkName) {
			if(e.checked) {
				jQuery('#description_'+checkName).hide('fast');
				jQuery('#title_'+checkName).css('color','#909090');
			} else {
				jQuery('#description_'+checkName).show('fast');
				jQuery('#title_'+checkName).css('color','#3C3C3C');
			}
		};
	</script>
	
	<div class="center"><input type="submit" class="button" value="Save" name="auto_save" /></div>
</form>