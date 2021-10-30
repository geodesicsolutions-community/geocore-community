<fieldset class="admin_home_stat_box">
	<legend><i class="fa fa-flag"></i> Getting Started</legend>
	<div style="border: 1px solid #abd9ea; border-radius: 5px; width: 250px; margin: 0 auto; text-align: center;">
	{if $getting_started_completion > 0}<canvas id="completion-gauge" style="max-width: 160px; max-height: 80px;"></canvas>{/if}
	<div class="goal-wrapper">
	<span id="gauge-text" class="gauge-value">{$getting_started_completion}</span>% Complete - <a href="index.php?page=checklist&mc=getting_started">View Checklist</a>
	</div>
	{if $getting_started_completion > 0} {* gauge.js breaks at 0 *}
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
	      gauge.set({$getting_started_completion}); //set gauge to have the correct amount filled
	      gauge.setTextField(document.getElementById("gauge-text")); //bind to a text field that animates onload
	    </script>
	   {/if}    
</div>
</fieldset>