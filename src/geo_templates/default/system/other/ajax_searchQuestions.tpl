{* 16.05.0-24-g33979a7 *}
{if count($optionals) > 0 || count($questions) > 0 || count($leveled_fields) > 0}
	<br />
	<div class="content_box_search">
		<h3 class="subtitle">{$messages.500806}</h3>
		{if $leveled_fields}
			{foreach $leveled_fields as $lev_id => $lev_field}
				{* Note: already checks for if should show in PHP *}
				<div class="{cycle values='row_odd,row_even'}">
					{include file='system/order_items/shared/leveled_fields/main.tpl'}
				</div>
			{/foreach}
		{/if}
		{foreach from=$optionals item=field key=i}
			<div class="{cycle values="row_even,row_odd"}">
				<label for="search_text" class="field_label">{$field.label|fromDB}</label>

				{if $field.type == 'numbers'}
					<span style="white-space: nowrap;">{$messages.1440} <input name='b[optional_field_{$i}][lower]' size='15' maxlength='15' class="field" /></span>
					<span style="white-space: nowrap;">{$messages.1441} <input name='b[optional_field_{$i}][higher]' size='15' maxlength='15' class="field" /></span>
				{elseif $field.type=='date'}
					<span style="white-space: nowrap;">{$messages.501040} <input name='b[optional_field_{$i}][low_date]' size='10' maxlength='10' class="field dateInput" /></span>
					<span style="white-space: nowrap;">{$messages.501041} <input name='b[optional_field_{$i}][high_date]' size='10' maxlength='10' class="field dateInput" /></span>
				{elseif $field.type == 'text'}
					<input type="text" name="b[optional_field_{$i}]" id="optional_field_{$i}" class="field" />
				{elseif $field.type == 'select'}
					<div class="multiselect">
						<ul>
							{foreach $field.dropdown as $val}
								{if $val.value}
									<li><label><input type="checkbox" name="b[optional_field_{$i}][]" value="{$val.value|escape}" {if $val.selected}checked="checked"{/if} /> {$val.value}</label></li>
								{/if}
							{/foreach}
						</ul>
						<div class="clr"></div>

						{if $field.other_box}<input type="checkbox" class="other_dummy_checkbox" /> {$messages.1458} <input type="text" name="b[optional_field_{$i}][other]" class="field" />{/if}
					</div>
				{/if}
			</div>
		{/foreach}

		{foreach from=$questions item=q}
			<div class="{cycle values="row_even,row_odd"}">
				<label for="search_text" class="field_label">{$q.label}</label>

				{if $q.type == 'date'}
					<span style="white-space: nowrap;">{$messages.501040} <input name='b[question_value][{$q.key}][low_date]' size='10' maxlength='10' class="field dateInput" /></span>
					<span style="white-space: nowrap;">{$messages.501041} <input name='b[question_value][{$q.key}][high_date]' size='10' maxlength='10' class="field dateInput" /></span>
				{elseif $q.type == 'numbers'}
					<span style="white-space: nowrap;">{$messages.1440} <input name='b[question_value][{$q.key}][lower]' size='15' maxlength='15' class="field" /></span>
					<span style="white-space: nowrap;">{$messages.1441} <input name='b[question_value][{$q.key}][higher]' size='15' maxlength='15' class="field" /></span>
				{elseif $q.type == 'check'}
					<input class="field" type="checkbox" name="b[question_value][{$q.key}]" value="{$q.label}" />
				{elseif $q.type == 'text'}
					<input class="field" type="text" name="b[question_value][{$q.key}]" />
				{elseif $q.type == 'select'}
					<div class="multiselect">
						{if $q.search_as_numbers == 1}
							<span style="white-space: nowrap;">
								{$messages.1440} <select name='b[question_value][{$q.key}][lower]'>
									{foreach $q.options as $opt}
										<option value="{$opt|escape}">{$opt}</option>
									{/foreach}
								</select>
							</span>
							<span style="white-space: nowrap;">
								{$messages.1441} <select name='b[question_value][{$q.key}][higher]'>
									{foreach $q.options as $opt}
										<option value="{$opt|escape}">{$opt}</option>
									{/foreach}
								</select>
							</span>
						{else}
							<ul>
								{foreach $q.options as $opt}
									{if $opt}
										<li><label><input type="checkbox" name="b[question_value][{$q.key}][]" value="{$opt|escape}" /> {$opt}</label></li>
									{/if}
								{/foreach}
							</ul>
							<div class="clr"></div>

							{if $q.other}<input type="checkbox" class="other_dummy_checkbox" /> {$messages.500659} <input class="field other_input_field" type="text" name="b[question_value][{$q.key}][other]" />{/if}
						{/if}
					</div>
				{/if}
			</div>
		{/foreach}
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
	</div>
{/if}
