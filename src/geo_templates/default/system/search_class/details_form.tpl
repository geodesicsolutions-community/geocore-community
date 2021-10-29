{* 16.09.0-24-gde6d4cb *}

{$errors}

<div class="search_content_box">
	<h1 class="title">{$messages.571}&nbsp;{$tooltip.2}</h1>
	<p class="page_instructions">{$messages.572}</p>

	{if $search_sql_query  eq 1}
		<div class="note_box">{$messages.589}</div>
	{/if}
</div>

<form action="{$classifieds_file_name}" method="get">
	<div>
		<input type='hidden' name='a' value='19' />
		<div class="search_content_box">
			<div class="center">
				<div class="form-wrapper cf">
				<input type="hidden" name="b[subcategories_also]" value="1" />
				<input type='text' id='search_text' name='b[search_text]' size='60' maxlength='80' class="field" placeholder="{$messages.573|escape}" />
				<button type="submit"><span class="glyphicon glyphicon-search"></span></button>
				</div>
				<select class="field" name="b[whole_word]">
					<option value="1">{$messages.580}</option>
					<option value="0"{if $je_search_setting != 1} selected="selected"{/if}>{$messages.581}</option>
					<option value="2"{if $je_search_setting == 1} selected="selected"{/if}>{$messages.1437}</option>
				</select>
				
				{if $is_auction eq 1}
					<select class="field" name="b[classified_auction_search]">
						<option value="0" selected="selected">{$messages.200021}</option>
						<option value="1">{$messages.200023}</option>
						<option value="2">{$messages.200022}</option>
						<option value="3">{$messages.500078}</option>
					</select>
				{else}
					<input type='hidden' name='b[classified_auction_search]' value='{$listing_type_allowed}' />
				{/if}
			</div>
			<div style="width:100%; text-align: center;">
			{if $queryFields.title}
				<div style="text-align:center; margin: 5px auto 10px auto; font-size: 0.8em; display: inline;">			
					<label><input type='checkbox' id='search_titles' name='b[search_titles]' value='1' checked='checked' />&nbsp;{$messages.575}&nbsp;&nbsp;</label>
				</div>
			{/if}
			{if $queryFields.description}
				<div style="text-align:center; margin: 5px auto 10px auto; font-size: 0.8em; display: inline;">
					<label><input type='checkbox' id='search_descriptions' name='b[search_descriptions]' value='1' checked='checked' />&nbsp;{$messages.576}</label>
				</div>
			{/if}
			</div>
			{if $show_close_24_hours and not ($queryFields.auction_time_left or $queryFields.classified_time_left)}
				<div style="text-align:center; margin: 5px auto 10px auto; font-size: 0.8em;">
					<label><input type='checkbox' id='ending_today' name='b[ending_today]' />&nbsp;{$messages.500079}</label>
				</div>
			{/if}
			{if $queryFields.auction_start or $queryFields.classified_start}
				<div style="text-align:center; margin: 5px auto 10px auto; font-size: 0.8em;">
					<label>{$messages.502441} <select class="field" name="b[start_date]">
						<option value="0">{$messages.502443}</option>
						<option value="1">1</option>
						<option value="2">2</option>
						<option value="7">7</option>
						<option value="14">14</option>
						<option value="30">30</option>
					</select> {$messages.502442}</label>
				</div>
			{/if}
			{if $queryFields.auction_time_left or $queryFields.classified_time_left}
				<div style="text-align:center; margin: 5px auto 10px auto; font-size: 0.8em;">
					<label>{$messages.502444} <select class="field" name="b[end_date]">
						<option value="0">{$messages.502443}</option>
						<option value="1">1</option>
						<option value="2">2</option>
						<option value="3">3</option>
					</select> {$messages.502442}</label>
				</div>
			{/if}
					
			{if $messages.1442}
				<h3 class="subtitle">{$messages.1442}</h3>
			{/if}
			
			<div class="{cycle values='row_odd,row_even'} rwd-center">
				<div class="leveled_cat">
					<label class="field_label spacer"></label>
					{$lev_field=$cats}
					{$leveledCatSearch=true}
					{foreach $cats.levels as $info}
						{include file='../order_items/shared/leveled_fields/level.tpl'}
					{/foreach}
					{$leveledCatSearch=false} {* make sure this doesn't bleed over into other leveled field thingys *}
					<input type='hidden' name='b[subcategories_also]' value='1' />
					{* to support legacy opt field scripts against new leveled category selector, use a hidden input: *}
					<input type='hidden' id='adv_searchCat' value='0' name='c' />	
				</div>
			</div>
			
			{if $queryFields.price}
				<h3 class="subtitle">{$messages.788}</h3>
				<div class="{cycle values='row_odd,row_even'} rwd-center">
					<label class="field_label spacer"></label>
					<label style="white-space: nowrap;">{$messages.1440}&nbsp;<input id="by_price_lower" name='b[by_price_lower]' size='10' maxlength='15' class="field" /></label>
					<label style="white-space: nowrap;">{$messages.1441}&nbsp;<input id="by_price_higher" name='b[by_price_higher]' size='10' maxlength='15' class="field" /></label>
				</div>
			{/if}
		</div>
	</div>
	
	{if $queryFields.business_type}
		<div>
			<div class="search_content_box">
				<h3 class="subtitle">{$messages.1439}</h3>
				<div class="{cycle values='row_odd,row_even'} rwd-center">
					<div>
						<label class="field_label spacer"></label>
						<label><input type='radio' id='a_by_business_type' name='b[by_business_type]' value='1' />{$messages.790}</label>
					</div>
					<div>
						<label class="field_label spacer"></label>
						<label><input type='radio' id='b_by_business_type' name='b[by_business_type]' value='2' />{$messages.789}</label>
					</div>
					<div>
						<label class="field_label spacer"></label>
						<label><input type='radio' id='c_by_business_type' name='b[by_business_type]' value='0' checked='checked' />{$messages.791}</label>
					</div>
				</div>
			</div>
		</div>
	{/if}
	
	{if $feeshare_active == 1}
		<h3 class="subtitle">by attached storefront</h3>
		<div class="{cycle values='row_odd,row_even'} rwd-center">
			<label class="field_label">{$feeshare_attachtouserlabel}</label>
			<select name="b[attached_user_search_id]" class="field"> 
				<option value="0"></option>
			{foreach from=$feeshare_userattachmentchoices item=name key=userid}
				<option value="{$userid}"{if $userid == $attached_user_search_id} selected="selected"{/if}>{$name}</option>
			{/foreach}
			</select>
		</div>			
	{/if}	
		
	
	<div class="clr"></div>
	{* NOTE: This is where category-specific questions will be inserted *}
	<div id="catQuestions" style="display: none;"></div>
	
	<div class="clr"></div>
	
	{if $region_selector || $use_zip_distance_calculator || $queryFields.city || $queryFields.zip || $show_optionals || $addonCriteria || $leveled_fields}
		{* only show this container if it's actually going to have something inside of it *}
		
		<div class="search_content_box">
			{if $use_zip_distance_calculator eq 1}
				<h3 class="subtitle">{$messages.1949}</h3>				
				<div class="{cycle values='row_odd,row_even'} rwd-center">
					{if $zipsearch_by_location}
						{$zipsearchByLocation_html}
					{else}

						<label class="field_label spacer"></label>
						<input type='text' id="by_zip_code" name='b[by_zip_code]' value='{$zip_filter}' class="field" />
						<select name='b[by_zip_code_distance]' class="field">
							<option value='0'>{$default_distance_text}</option>
							{foreach from=$basic_distances item=this_item}
								<option value='{$this_item}' {if $zip_filter_distance eq $this_item} selected="selected"{/if}>{$this_item}</option>
							{/foreach}
						</select>
						{$tooltip.3}
					{/if}
				</div>
			{/if}
			{if $region_selector || $queryFields.city || (!$use_zip_distance_calculator && $queryFields.zip)}
				<h3 class="subtitle">{$messages.500809}</h3>
			{/if}
			{if $region_selector}
				<div class="{cycle values='row_odd,row_even'} rwd-center">
					{$region_selector}
				</div>
			{/if}
			{if $queryFields.city}
				<div class="{cycle values='row_odd,row_even'} rwd-center">
					<label for="by_city" class="field_label">{$messages.500808}</label> 
					<input type="text" id="by_city" name="b[by_city]" maxlength="50" class="field" />
				</div>
			{/if}
			{if !$use_zip_distance_calculator && $queryFields.zip}
				<div class="{cycle values='row_odd,row_even'} rwd-center">
					<label for="by_zip_code" class="field_label">{$messages.577}</label> 
					<input type="text" id="by_zip_code" name="b[by_zip_code]" maxlength="50" class="field" />
				</div>
			{/if}
			
			
			{if $show_optionals || $addonCriteria || $leveled_fields}
				<h3 class="subtitle">{$messages.500807}</h3>
			{/if}
			
			{if $leveled_fields}
				{foreach $leveled_fields as $lev_id => $lev_field}
					{* Note: already checks for if should show in PHP *}
					<div class="{cycle values='row_odd,row_even'} rwd-center">
						{include g_resource='order_items' file='shared/leveled_fields/main.tpl'}
					</div>
				{/foreach}
			{/if}
			{if $addonCriteria}
				{foreach from=$addonCriteria item=criterias name='addonSearchCriteria'}
					{foreach from=$criterias item=criteriaData}
						<div class="{cycle values='row_odd,row_even'} rwd-center">
							<label class="field_label">{$criteriaData.label}</label>
							{$criteriaData.data}
						</div>
						{if !$criteriaData.skipBreakAfter}
							<div class="divider"></div>
						{/if}
					{/foreach}
				{/foreach}
			{/if}
			{if $show_optionals}
				{* Note: the "fields to use" checks are done in PHP, if optional
					is in the optionals array then it should be shown *}
				{foreach from=$optionals item=o}
					<div class="{cycle values='row_odd,row_even'} rwd-center">
						<label class="field_label">{$o.label}</label>
						{if $o.type == 'numbers'}
							<span style="white-space: nowrap;">{$messages.1440} <input name='b[optional_field_{$o.field_number}][lower]' size='15' maxlength='15' class="field" /></span>
							<span style="white-space: nowrap;">{$messages.1441} <input name='b[optional_field_{$o.field_number}][higher]' size='15' maxlength='15' class="field" /></span>
						{elseif $o.type=='date'}
							<span style="white-space: nowrap;">{$messages.501040} <input name='b[optional_field_{$o.field_number}][low_date]' size='10' maxlength='10' class="field dateInput" /></span>
							<span style="white-space: nowrap;">{$messages.501041} <input name='b[optional_field_{$o.field_number}][high_date]' size='10' maxlength='10' class="field dateInput" /></span>
						{elseif $o.type == 'text'}
							<input type='text' name='b[optional_field_{$o.field_number}]' id='optional_field_{$o.field_number}' class="field" />
						{elseif $o.type == 'select'}
							<div class="multiselect">
								<ul>
									{foreach $o.dropdown as $val}
										{if $val.value}
											<li><label><input type="checkbox" name="b[optional_field_{$o.field_number}][]" value="{$val.value|escape}" {if $val.selected}checked="checked"{/if} /> {$val.label}</label></li>
										{/if}
									{/foreach}
								</ul>
								<div class="clr"></div>
								
								{if $o.other_box}<input type="checkbox" class="other_dummy_checkbox" /> {$messages.1458} <input type="text" name="b[optional_field_{$o.field_number}][other]" class="field" />{/if}
							</div>
						{/if}
					</div>
				{/foreach}
			{/if}
		</div>
	{/if}
	
	<div class="center">
		<input type='submit' value='{$messages.584}' name='b[search]' class='button' />
	</div>
</form>
