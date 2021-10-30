{* 7.5.3-36-gea36ae7 *}
<div class="region_selector region_wrapper_{$level.id}_{$fieldName_class}" {if $buildDown}style="display: block;"{/if}>
	{if $level.use_label === 'yes'}<label class="field_label region_label{if $isScalarLevel && $level.id != $bottomLevel} region_scalar_label{/if}">{$level.label}</label>{/if}<br />
	
	{if $isScalarLevel && $level.id != $bottomLevel}
		{* This is the only region on this level -- Print the region's name directly (and store its ID for form submission) *}
		{* EXCEPTION: if this is the bottom level, show the full dropdown anyway because it makes more sense for UX *}
		<span class="region_scalar_name region_scalar_name_level_{$level.id}_{$fieldName_class}">{$regions.0.name}</span>
		<input type="hidden" name="{$fieldName}[{$level.id}]" value="{$regions.0.id}" />
		
		{add_footer_html}
		{* now go ahead and directly fire the ajax call to get the next level *}
		<script type="text/javascript">
			//<![CDATA[
			jQuery.ajax('{if $in_admin}../{/if}AJAX.php?controller=RegionSelect&action=getChildElements',{
				data: {
					parent: {$regions.0.id},
					prevalue: '{$prevalue}',
					fieldName: '{$fieldName}',
					{if $maxLevel}maxLevel: {$maxLevel},{/if}
					is_a: {$in_admin},
					skipEmptyRegions: {$skipEmptyRegions}
				},
				success: function (data) {
					if (data) {
						//append the results to teh wrapper
						jQuery('.region_selector_wrapper_{$fieldName_class}').append(data);
						jQuery('.region_fake_{$level.id + 1}_{$fieldName_class}').remove();
					}
				},
				type: 'POST',
				dataType: 'html'
			});
			//]]>
		</script>
		{/add_footer_html}
	{else}
	
		<select name="{$fieldName}[{$level.id}]" class="field region_level_{$level.id}_{$fieldName_class}">
			<option value=""></option>
			{foreach $regions as $r}
				<option value="{$r.id}"{if $r.selected} selected="selected"{/if}>{$r.name}</option>
			{/foreach}
		</select>
		{add_footer_html}
		<script type="text/javascript">	
		//<![CDATA[
			var change_level_{$level.id}_{$fieldName_class} = function() {
				
				//clean up any children dropdowns of this one
				for (var i = {$level.id} + 1; i <= {$bottomLevel}; i++) {
					jQuery('.region_wrapper_'+i+'_{$fieldName_class}').remove();
					jQuery('.region_fake_'+i+'_{$fieldName_class}').remove();
				}
				
				if (jQuery('.region_level_{$level.id}_{$fieldName_class}').val() == '') {
					//not a valid choice -- do nothing
					return false;
				}
				
				//get next dropdown via ajax
				jQuery.ajax('{if $in_admin}../{/if}AJAX.php?controller=RegionSelect&action=getChildElements',{
					data: {
						parent: jQuery('.region_level_{$level.id}_{$fieldName_class}').val(),
						prevalue: '{$prevalue}',
						fieldName: '{$fieldName}',
						{if $maxLevel}maxLevel: {$maxLevel},{/if}
						is_a: {$in_admin},
						skipEmptyRegions: {$skipEmptyRegions}
					},
					success: function (data) {
						//append the results to the wrapper, but first make sure they're not already there
						//(prevents multiple super-speedy ajax requests from gumming up the works)
						if(jQuery('.region_wrapper_{$level.id + 1}_geoRegion_user_location').length==0) {
							jQuery('.region_selector_wrapper_{$fieldName_class}').append(data);
						}
					},
					type: 'POST',
					dataType: 'html'
				});
			};
		
			if (jQuery('.region_level_{$level.id}_{$fieldName_class}').val() != '') {
				//it has something selected!
				change_level_{$level.id}_{$fieldName_class}();
			}
			jQuery('.region_level_{$level.id}_{$fieldName_class}').change(change_level_{$level.id}_{$fieldName_class});
		//]]>
		</script>
		{/add_footer_html}
	{/if}
</div>
