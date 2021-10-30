{* 17.07.0-13-g1b8edf9 *}
<form class='form-horizontal' onsubmit='return false;'>
<fieldset>
	<legend>Language Import</legend>
	<div class='x_content'>
		<div class="form-group">
			<label class="control-label col-xs-12 col-sm-5">Data Type: </label>
			<div class="col-xs-12 col-sm-6">
				<select id="typeSelect" name="type" class="form-control col-xs-12 col-sm-7">
					<option value="text">Site Text</option>
					{if !$demo}<option value="text_legacy">Site Text (Legacy Uploader)</option>{/if}
					<option value="region_structure">Region Structure</option>
					<option value="region_data">Region Data</option>
					<option value="category_structure">Category Structure</option>
					<option value="category_data">Category Data</option>
				</select>
			</div>
		</div>
		<div class="form-group" id="lang-wrapper">
			<label class="control-label col-xs-12 col-sm-5">Target language: </label>
			<div class="col-xs-12 col-sm-6">
				<select id="langSelect" class="form-control col-xs-12 col-sm-7">
					{foreach $languages as $id => $name}
						<option value="{$id}" {if $smarty.get.l == $id}selected="selected"{/if}>{$name}</option>
					{/foreach}
				</select>
			</div>
		</div>
		
		<div class="center next-main">
			<div id="structure-warning" style="color: red; display: none;">This will erase existing structure data in your database. You usually only want to do this once for each Data Type (Regions and Categories), and only on a brand new site!</div>
			<br />
			{if $demo}<button id='none' class='button' disabled>Disabled for this demo</button>{else}<button id="upload" class="button">Select File</button>{/if}
			<br />
			<strong id="spinner" style="display: none;"><i class="fa fa-spinner fa-spin"></i> Pre-processing...please wait!</strong>
			<input type="file" accept="text/csv" id="fileIn" style="display: none;" />
		</div>
		<div class="center legacy-main" style="display: none;">
			<div style="margin-top: 20px; font-weight: bold;">On some servers, the normal text importer may not function correctly. It may help to use an older version of the importer, available by clicking the button below.</div>
			<a id="legacy-link" class="button" href="index.php?mc=languages&page=languages_import_legacy">Go to Legacy Text Importer</a>
			<script>
				var linkBase = "index.php?mc=languages&page=languages_import_legacy";
				jQuery('#langSelect').change(function() {
					jQuery('#legacy-link').prop('href',linkBase + "&l=" + jQuery('#langSelect').val()); 
				});
				jQuery('#typeSelect').change(function() {
					if(jQuery('#typeSelect').val() == "text_legacy") {
						jQuery(".legacy-main").show();
						jQuery(".next-main").hide();
					} else {
						jQuery(".legacy-main").hide();
						jQuery(".next-main").show();
					}
				});
				jQuery(document).ready(function(){
					jQuery('#langSelect').change();
					jQuery('#typeSelect').change();
				});
			</script> 
		</div>
	</div>
</fieldset>
</form>

<fieldset id="ready" style="display: none;">
	<legend>Pre-processing Complete</legend>
	<div style="text-align: center;">
		Identified <span id="records-identified"></span> records to be uploaded<br />
		<button class="button" id="confirm-upload">Begin</button> <button class="mini_cancel" id="cancel-upload">Cancel</button>
	</div>
</fieldset>

<div style="border: 1px solid #abd9ea; border-radius: 5px; width: 250px; margin: 0 auto; text-align: center; display: none;" id="result">
	<canvas width="150" height="80" id="completion-gauge" class="" style="width: 160px; height: 100px;"></canvas>
	<div class="goal-wrapper">
	<span id="complete" class="gauge-value">0</span> of <span id="total"></span>
	</div>  
</div>

<fieldset id="status" style="display: none;">
	<legend>Status</legend>
	<div style="text-align: center;">
		<strong id="status-txt">We are now processing your upload. Please stand by.</strong>
	</div>
</fieldset>

{if !$demo}
	<script src="js/gauge.js"></script>
	<script>
		fileContents = '';
		function readFile(input) {
			if (input.files && input.files[0]) {
				var reader = new FileReader();
				reader.onloadstart = function(e) {
					jQuery('#final-result').hide(); //hide the final result if we're doing this a second time...
					jQuery('#spinner').show(); //show a spinner while preloading the file
					//disable the inputs until we're done
					jQuery('#upload').prop('disabled',true);
					jQuery('#typeSelect').prop('disabled',true);
					jQuery('#langSelect').prop('disabled',true);
					//in case "upload complete" message lingers from a previous upload, reset it
					jQuery('#status').hide();
					jQuery('#status-txt').html("We are now processing your upload. Please stand by.");
				}
				reader.onload = function (e) {
					//once the file has finished reading, show preprocessing results
					fileContents = e.target.result.split(/[\r\n]+/);
					jQuery('#records-identified').html(fileContents.length);
					jQuery('#ready').show();
					jQuery('#spinner').hide();
				}
				reader.readAsText(input.files[0]);
			} else {
				alert('This browser lacks FileReader ability. Upgrade to a newer browser to use this tool.');
			}
		}
		
		gauge = new Gauge(document.getElementById('completion-gauge')).setOptions({
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
			  });
		
		totalComplete = 0;
		totalGood = 0;
		totalBad = 0;
		function processFile(begin=0) {
			if(begin == 0) {
				//at the very beginning, do some display stuff to show progress bar and status boxes and things
				initGauge(fileContents.length);
			}
			
			//split into an array of lines and ship each one off to ajax separately
			
			maxAtOnce = 100; //wait for this many ajax calls to complete, then recurse!
			
			completeThisRun = 0;
			var end = Math.min(begin + maxAtOnce, fileContents.length);
			for(var l = begin; l < end; l++) {
				
				jQuery.post("AJAX.php?controller=DataImport&action="+jQuery('#typeSelect').val(),
				{
					lang: jQuery('#langSelect').val(),
					data: fileContents[l]
				},
				function(response) {
					completeThisRun++;
					totalComplete++;
					gauge.set(begin + completeThisRun);
					//track overall success stats
					if(response.status == 'ok') {
						totalGood++;
					} else {
						//console.log(response);
						totalBad++;
					}
					
					if(totalComplete == fileContents.length) {
						//last one overall					
						resetAll();
						jQuery('#status-txt').html("<span style='color: #1ABC9C;'>Upload complete!</span><br />Records modified: "+totalGood+"<br />Records skipped: "+totalBad+"<br /><span style='color: #878787;'>(A small number of skipped records is normal, to account for CSV column headers and/or blank lines)</span>");
					} else if(completeThisRun == maxAtOnce) {
						//last one of this set; begin the next group
						processFile(end);
					}
				},
				'json');
			}
		}
		jQuery("#upload").click(function() { jQuery("#fileIn").click(); });
		jQuery("#fileIn").change(function() { readFile(this); });
		jQuery("#confirm-upload").click(function() { 
			totalComplete = totalGood = totalBad = 0; //make sure counters are reset to 0 for subsequent runs
			//if starting a "structure" upload, clear out any existing structure first
			if(jQuery('#typeSelect').val() == 'region_structure' || jQuery('#typeSelect').val() == 'category_structure') {
				//don't begin processing until the clear ajax call returns
				jQuery.post("AJAX.php?controller=DataImport&action=clear_structure",
				{
					clear_type: jQuery('#typeSelect').val()
				},
				function(ret) {				
					if(ret.status == 'ok') {
						processFile();
					} else {
						//something went very wrong...
						console.log("something is broken");
					}
				},
				'json');
			} else {
				//go ahead and process now
				processFile();
			}
		});
		function initGauge(max) {

			  gauge.maxValue = max;
			  gauge.animationSpeed = 1;
			  gauge.set(1); //start at 1 because it looks funky if you give this a 0
			  gauge.setTextField(document.getElementById("complete")); //bind to a text field that animates onload
			  
			  jQuery('#total').html(max);
			  jQuery('#result').show();
			  jQuery('#status').show();
			  jQuery("#confirm-upload").prop('disabled',true);
			  jQuery('#ready').hide();
			  
			  return gauge;
		}
		jQuery('#typeSelect').change(function(){
			if(jQuery('#typeSelect').val() == 'region_structure' || jQuery('#typeSelect').val() == 'category_structure') {
				jQuery('#lang-wrapper').hide();
				jQuery('#structure-warning').show();
			} else {
				jQuery('#structure-warning').hide();
				jQuery('#lang-wrapper').show();
			}
		});
		
		function resetAll() {
			//convenience function to make sure the forms are reset when the page reloads or Cancel is clicked
			jQuery('#ready').hide();
			jQuery('#upload').prop('disabled',false);
			jQuery('#typeSelect').prop('disabled',false);
			jQuery('#langSelect').prop('disabled',false);
			jQuery("#confirm-upload").prop('disabled',false);
			jQuery('#typeSelect').change();
		}
		jQuery('#cancel-upload').click(function() { resetAll() });
		jQuery(document).ready(function(){ resetAll(); });
	</script>
{/if}
