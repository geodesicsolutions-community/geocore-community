{* 7.5.3-36-gea36ae7 *}
<div style="display: inline-block;">
	<select name="{$name}[country]" id="{$name}_country_ddl" class="field">
		<option value=""></option>
		{foreach $countries as $id => $c}
			<option value="{$c.abbreviation}"{if $c.selected} selected="selected"{/if}>{$c.name}</option>
		{/foreach}
	</select>
	{add_footer_html}
	<script type="text/javascript">
	//<![CDATA[
	
		jQuery('#{$name}_country_ddl').change(function() {
		
			//remove the state selector (if it exists)
			if(jQuery('#{$name}_state_remove_me')) {
				jQuery('#{$name}_state_remove_me').remove();
			}
			
			//now make an AJAX call with the selected country to get its states and show their DDL
			
			if(jQuery('#{$name}_country_ddl').val().length == 0) {
				//empty selection -- nothing else to do here
				return false;
			}
			
			jQuery.post('{if $in_admin}../{/if}AJAX.php?controller=RegionSelect&action=getChildStatesForBilling', 
				{ country: jQuery('#{$name}_country_ddl').val(), name: '{$name}', }, 
				function(returned) {
					if(returned.length > 0) {
						jQuery('#billing_state_wrapper').append(returned);
					}		
				}, 
				'text'
			);
		});
		
	
	//]]>
	</script>
	{/add_footer_html}
</div>