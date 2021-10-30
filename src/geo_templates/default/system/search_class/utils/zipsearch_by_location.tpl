{* 7.4beta1-374-gff9c6ee *}
		<td>
			<script type="text/javascript">
				function ziploc_loadCities(state)
				{ldelim}
					optionHTML = '';
					switch(state) {ldelim}
						{foreach from=$locations key=state_name item=cities}
							case '{$state_name}':
								optionHTML = '<option value="">{$allCitiesLabel}</option>{foreach from=$cities key=city_name item=zipcode}<option value="{$zipcode}">{$city_name}</option>{/foreach}'; 
								break;
						{/foreach}
						default:
							optionHTML = '<option value="">{$allCitiesLabel}</option>';
					{rdelim}
					
					jQuery('#ziploc_cities_ddl').html(optionHTML);
					
				{rdelim}
			</script>
			
			<input type="radio" name="b[ziploc_state_select]" value="" checked="checked" id="ziploc_default" onclick="ziploc_loadCities('all');" /> {$allStatesLabel}<br />
			{foreach from=$locations key=state_name item=cities}
				<input type="radio" name="b[ziploc_state_select]" value="{$state_name}" onclick="ziploc_loadCities('{$state_name}');" /> {$state_name}<br />
			{/foreach}
			{add_footer_html}
			{* make sure radio buttons and dropdown don't de-sync *}
			<script type="text/javascript">
			//reset radio button for FF users who hit refresh
			jQuery('#ziploc_default').prop('checked', true);
			
			//reset form for IE users who come Back to the page
			jQuery(function () {
				myForm = jQuery('#search_form');
				if(myForm.length) {
					myForm.reset();
				} else {
					jQuery('#ziploc_default').prop('checked', true);
				}
			});
			</script>
			{/add_footer_html}
		</td>

		<td>
			<select id="ziploc_cities_ddl" name="b[by_zip_code]" class="search_data_values">
				<option value="">{$allCitiesLabel}</option>
			</select>

		<br />
		
			<select name='b[by_zip_code_distance]' class='search_data_values'>
				<option selected="selected" value='{$default_distance}'>{$distanceLabel}</option>
				{foreach from=$basic_distances item=this_item}
					<option value='{$this_item}'>{$this_item}</option>
				{/foreach}
			</select>
		</td>
		
		{if $showResetButton}
			<td>
				<input type="reset" value="{$resetButtonLabel}" onclick="ziploc_loadCities('all')"/>
			</td>
		{/if}