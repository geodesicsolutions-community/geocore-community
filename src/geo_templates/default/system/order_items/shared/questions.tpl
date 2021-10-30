{* 17.01.0-17-ge288a00 *}

{if count($questions) > 0}
	{if $messages.131}
		<h3 class="subtitle">
			{$messages.131}
		</h3>
	{/if}
	{if $messages.132}
		<p class="page_instructions">
			{$messages.132}
		</p>
	{/if}
	{assign var=current_counter value=0}
	<table width="100%">
	{foreach from=$questions key="display_order" item="order_questions"}
		{foreach from=$order_questions item="question"}
			{if $listing_process_count_columns > 0 && $current_counter % $listing_process_count_columns == 0}<tr>{/if}
			{assign var="key" value=$question.key}
			
			{if (($listing_process_count_columns == 0) || ($listing_process_count_columns == 1))}<div class="{cycle values='row_odd,row_even'}">{else}<td>{/if}
				<label for="b[question_value][{$key}]" class="field_label">
					{$question.name|fromDB}
				</label>
				
				{if $question.type == 'none' || $question.type == 'url' || $question.type == 'number'}
					{* Question value will be from user input, which is already html escaped *}
					<input class="field" type="text" name="b[question_value][{$key}]" id="b[question_value][{$key}]" value="{$session_variables.question_value.$key}" size="30" maxlength="255" />
				{elseif $question.type == 'date'}
					{* Question is a date *}
					<input class="field dateInput" type="text" name="b[question_value][{$key}]" id="b[question_value][{$key}]" value="{$session_variables.question_value.$key}" size="10" maxlength="10" />
				{elseif $question.type == 'textarea'}
					{* Question value will be from user input, which is already html escaped *}
					<textarea name="b[question_value][{$key}]" id="b[question_value][{$key}]" cols="60" rows="15" class="field"
						{if $field_config.textarea_wrap}style="white-space: pre-wrap;"{/if}>{$session_variables.question_value.$key}</textarea>
				{elseif $question.type == "check"}
					{* Question value will be from DB value, which is NOT already html escaped,
						DO NOT remove the HTML escape for this specific type of question! *}
					<input type="checkbox"  id="b[question_value][{$key}]"
						name="b[question_value][{$key}]"
						value="{$question.name|escape}"
						{if $session_variables.question_value.$key}checked="checked"{/if} />
				{else}
					{if count($question.choices)}
						<select class="field" name="b[question_value][{$key}]" id="b[question_value][{$key}]">
							{foreach from=$question.choices item="choice"}
								<option{if $session_variables.question_value.$key|strip == $choice.value|strip} selected="selected"{/if}>
									{$choice.value}
								</option>
							{/foreach}
						</select>
					{/if}
					{if $question.other_box}
						&nbsp;{$messages.133}&nbsp;<input class="field" type="text" size="12" maxlength="50" name="b[question_value_other][{$key}]" id="b[question_value][{$key}]" value="{$session_variables.question_value_other.$key}" />
					{/if}
				{/if}
					{$question.help}
					<input type="hidden" name="b[question_display_order][{$key}]" value="{$display_order}" />
			{if (($listing_process_count_columns == 0) || ($listing_process_count_columns == 1))}</div>{else}</td>{/if}
			{assign var=current_counter value=$current_counter+1}
			{if $listing_process_count_columns > 0 && $current_counter % $listing_process_count_columns == 0}</tr>{/if}
		{/foreach}
		
	{/foreach}
	{if $listing_process_count_columns > 0 && $current_counter % $listing_process_count_columns != 0}</tr>{/if}
	</table>
{/if}