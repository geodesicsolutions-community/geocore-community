{* 16.07.0-80-g8299416 *}

{for $addIndex=0 to $details.max-1}
	<div class="{if $details.errors.$addIndex}field_error_row {/if}{cycle values='row_odd,row_even'} additional_regions_wrapper" style="display:none;">
		<label class="field_label">
			{$addIndex+1}.)
			{if $details.cost>0 && $details.max>$details.free}
				{* Show the cost! *}
				{$messages.502054} {if $details.free>$addIndex}{$messages.502055}{else}{$details.cost|displayPrice:false:false:'cart'}{/if}
			{/if}
		</label>
		
		{if $details.preselect_regions.$addIndex}
			{$details.preselect_regions.$addIndex}
		{else}
			<div class="regionPlaceholder"></div>
		{/if}
		
		<a href="#remove_region" class="cancel additional_region_remove" style="display: none;">{$messages.502053}</a>
		{if $details.errors.$addIndex}
			<br />
			<span class="error_message">{$details.errors.$addIndex}</span>
		{/if}
		<input type="hidden" class="additional_index" value="{$addIndex}" />
		<input type="hidden" name="additional_use[{$addIndex}]" class="additional_use"
			value="{if $details.preselect_regions.$addIndex}1{else}0{/if}" />
	</div>
{/for}

<div class="{cycle values='row_odd,row_even'}">
	<label class="field_label"><a href="#add_region" class="button additional_region_add">{$messages.502052}</a></label>
	{$details.value}
</div>
{add_footer_html}
<script>
//<![CDATA[
	var gjAddRegions = {
		max: {$details.max},
		free: {$details.free},
		cost: '{$details.cost|displayPrice}',
		
		updateButtons: function () {
			if (jQuery('.additional_use[value="0"]').length) {
				//there are still a few hidden...
				jQuery('.additional_region_add').show();
			} else {
				//none showing any more!
				jQuery('.additional_region_add').hide();
			}
			//hide all of the remove buttons
			jQuery('.additional_region_remove').hide();
			//now show the last one that is, well, showing.
			jQuery('.additional_use').gj('filterValue', '1').filter(':last').closest('.additional_regions_wrapper')
				.find('.additional_region_remove').show();
		},
		
		addButtonClick : function () {
			if (!jQuery('.additional_use').gj('filterValue', '0').length) {
				//There is nothing to show!
				gjAddRegions.updateButtons();
				return false;
			}
			//get the first one...
			var wrapper = jQuery('.additional_use').gj('filterValue','0').filter(':first').closest('.additional_regions_wrapper');
			if (wrapper.find('.region_selector').length) {
				//this one already has something in it...
				wrapper.show('fast');
			} else {
				var thisIndex = wrapper.find('.additional_index').val();
				jQuery.ajax('{if $details.in_admin}../{/if}AJAX.php?controller=RegionSelect&action=addAdditionalRegion',{
					data: {
						regionIndex: thisIndex,
						fieldName: 'additional_regions',
						is_a: {$details.in_admin}
					},
					success: function (data) {
						if (data) {
							//append the results to the wrapper
							wrapper.find('.regionPlaceholder').replaceWith(data);
							wrapper.show('fast');
						}
					},
					type: 'POST',
					dataType: 'html',
				});
			}
			wrapper.find('.additional_use').val('1');
			gjAddRegions.updateButtons();
			
			return false;
		},
		
		removeButtonClick : function () {
			var wrapper = jQuery(this).closest('.additional_regions_wrapper');
			var thisIndex = wrapper.find('.additional_index').val();
			
			wrapper.hide('fast')
				.find('.additional_use').val('0');
			
			//reset the start to blank
			wrapper.find('.region_level_1_additional_regions_'+thisIndex+'_').val('')
				.change()
				.closest('.additional_regions_wrapper.field_error_row').removeClass('field_error_row').find('.error_message').hide();
			
			gjAddRegions.updateButtons();
			
			return false;
		}
	};
	jQuery(document).ready(function () {
		jQuery('.additional_region_add').click(gjAddRegions.addButtonClick);
		jQuery('.additional_region_remove').click(gjAddRegions.removeButtonClick);
		//make sure to show regions that are populated
		jQuery('.additional_use').gj('filterValue','1').closest('.additional_regions_wrapper').show('fast');
		gjAddRegions.updateButtons();
	});
//]]>
</script>
{/add_footer_html}
