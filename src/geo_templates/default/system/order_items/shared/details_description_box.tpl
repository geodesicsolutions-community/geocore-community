{* 7.4.4-157-gb7bbcd3 *}
{if $fields->description->is_enabled}
	<div class="{if $error_msgs.description}field_error_row {/if}{cycle values='row_odd,row_even'}">
		<label for="main_description" class="field_label">
			{$messages.114}
			{if !$use_rte}
				<br />
				<span class="sub_note">{$messages.500173} <span id="chars_remaining">{$max_length_description}</span></span>
			{/if}
		</label>
		
		{if $error_msgs.description}
			<span class="error_message">{$messages.120}</span>
		{/if}
		
		<div class="clr"><br /></div>
		
		{if $use_rte && $messages.500235|strip:'' != ''}
			<a href="#" onclick="gjWysiwyg.toggleTinyEditors(); return false;">{$messages.500235}</a>
			<br />
		{/if}
		<textarea id="main_description" name="b[description]"{if $field_config.textarea_wrap} style="white-space: pre-wrap;"{/if} class="editor field">{$desc_clean}</textarea>
	</div>
{/if}