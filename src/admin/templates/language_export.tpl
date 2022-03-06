{* 16.09.0-79-gb63e5d8 *}
{$admin_msgs}
<form action="index.php?mc=languages&page=languages_export" method="post" class='form-horizontal'>
	<fieldset>
		<legend>Export Options</legend>
		<div class='x_content'>
			<div class="form-group">
				<label class="control-label col-xs-12 col-sm-5">Export Type: </label>
				<div class="col-xs-12 col-sm-6">
					<select id="typeSelect" name="type" class="form-control col-xs-12 col-sm-7">
						<option value="text">Site Text</option>
						<option value="region_structure">Region Structure</option>
						<option value="region_data">Region Data</option>
						<option value="category_structure">Category Structure</option>
						<option value="category_data">Category Data</option>
					</select>
				</div>
			</div>
			<div class="form-group" id="lang-wrapper">
				<label class="control-label col-xs-12 col-sm-5">Language: </label>
				<div class="col-xs-12 col-sm-6">
					<select id="langSelect" name="lang" class="form-control col-xs-12 col-sm-7">
						{foreach $languages as $id => $name}
							<option value="{$id}" {if $smarty.get.l == $id}selected="selected"{/if}>{$name}</option>
						{/foreach}
					</select>
				</div>
			</div>
			<input type="hidden" name="download" value="1" />
			<div class="center"><input type="submit" value="Download" class="button" /></div>
		</div>
	</fieldset>
</form>
<script>
	jQuery('#typeSelect').change(function() {
		if(jQuery('#typeSelect').val() == 'region_structure' || jQuery('#typeSelect').val() == 'category_structure') {
			jQuery('#lang-wrapper').hide();
		} else {
			jQuery('#lang-wrapper').show();
		}
	});
	jQuery(document).ready(function() { jQuery('#typeSelect').change(); });
</script>