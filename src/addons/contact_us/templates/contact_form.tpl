{* 7.6.3-149-g881827a *}

<form action="{$classifieds_file_name}?a=ap&amp;addon=contact_us&amp;page=main{if $reportAbuse}&amp;reportAbuse={$reportAbuse}{/if}" method="post">
	<div{if !$is_ajax} class="content_box"{/if}>
		{if $is_ajax}<div class="closeBoxX"></div>{/if}
		<h2 class="title{if $is_ajax} lightUpTitle{/if}">{$msgs.section_title}</h2>
		<p class="page_instructions">{$msgs.section_desc}</p>
		{if $show_ip}
			<div class="{cycle values='row_even,row_odd'}">
				<label class="field_label">{$msgs.ip_label}</label>
				<strong class="text_highlight">{$ip}</strong>
			</div>
		{/if}
		<div class="{if $errors.dept}field_error_row {/if}{cycle values='row_even,row_odd'}">
			<label class="field_label" for="contact_dept">{$msgs.dept_label}</label>
			{if $reportAbuse}
				<input type="hidden" name="contact[dept]" value="1" />
				<span class="error_message">{$msgs.dept_abuse}</span>
			{else}
				<select class="field" id="contact_dept" name="contact[dept]">
					<option value="1"{if $vals.dept==1} selected="selected"{/if}>{$msgs.dept_1}</option>
					<option value="2"{if $vals.dept==2} selected="selected"{/if}>{$msgs.dept_2}</option>
				</select>
			{/if}
			{if $errors.dept}
				<br />
				<span class="error_message">{$errors.dept}</span>
			{/if}
		</div>
		<div class="{cycle values='row_even,row_odd'}">
			<label class="field_label" for="contact_name">{$msgs.name_label}</label>
			<input name="contact[name]" id="contact_name" value="{$vals.name|escape}" class="field" type="text" />
		</div>
		<div class="{if $errors.email}field_error_row {/if}{cycle values='row_even,row_odd'}">
			<label class="required field_label" for="contact_email">{$msgs.email_label} *</label>
			<input name="contact[email]" id="contact_email" value="{$vals.email|escape}" class="field" type="text" />
			{if $errors.email}
				<br />
				<span class="error_message">{$errors.email}</span>
			{/if}
		</div>
		<div class="{if $errors.subject}field_error_row {/if}{cycle values='row_even,row_odd'}">
			<label class="required field_label" for="contact_subject">{$msgs.subject_label} *</label>
			<input name="contact[subject]" id="contact_subject" value="{$vals.subject|escape}" class="field" type="text" size="50" />
			{if $errors.subject}
				<br />
				<span class="error_message">{$errors.subject}</span>
			{/if}
		</div>
		<div class="{if $errors.message}field_error_row {/if}{cycle values='row_even,row_odd'}">
			<label class="required" for="contact_message">{$msgs.message_label} *</label>
			<textarea name="contact[message]" id="contact_message" cols="78" rows="7" class="field">{$vals.message|escape}</textarea>
			{if $errors.message}
				<br />
				<span class="error_message">{$errors.message}</span>
			{/if}
		</div>
		
		{$security_image}
		
		<div class="center">
			<input type="submit" value="{$msgs.send_button}" class="button" />
			<input type="reset" value="{$msgs.reset_button}" class="cancel" />
			{if $is_ajax}
				<input type="submit" value="{$msgs.ajax_cancel}" class="cancel closeLightUpBox" />
			{/if}
		</div>
	</div>
</form>