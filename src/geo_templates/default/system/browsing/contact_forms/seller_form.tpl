{* 6.0.7-3-gce41f93 *}
{if $classified_ended}
	<div class="error_box center">{$messages.500077}</div>
{else}
	{include file="form_top.tpl" g_resource="browsing/contact_forms/common"}
	
		<div class="{cycle values="row_even,row_odd"}">
			<label class="field_label">{$labels.seller_name}</label>
			<strong class="text_highlight">{$values.seller_name}</strong>
		</div>

		<div class="{cycle values="row_even,row_odd"}">
			<label class="field_label">{$labels.listing_title}</label>
			<strong class="text_highlight">{$values.listing_title}</strong>	
		</div>

		<div class="{cycle values="row_even,row_odd"}">
			<label for="c_senders_name" class="field_label">{$labels.your_name}</label>
			<input type="text" name="c[senders_name]" id="c_senders_name" value="{$values.your_name}" class="field" />	
		</div>
		
		<div class="{cycle values="row_even,row_odd"}">
			<label for="c_senders_email" class="field_label">{$labels.your_email}</label>
			<input type="text" name="c[senders_email]" id="c_senders_email" value="{$values.your_email}" class="field" />	
		</div>

		<div class="{cycle values="row_even,row_odd"}">
			<label for="c_senders_phone" class="field_label">{$labels.your_phone}</label>
			<input type="text" name="c[senders_phone]" id="c_senders_phone" value="{$values.your_phone}" class="field" />
		</div>
		
		{if $canAskPublicQuestion}
			<div class="{cycle values="row_even,row_odd"}">
				<label for="c_public_question" class="field_label">{$labels.public_question}</label>
				<select name="c[public_question]" class="field">
					<option value="0">{$labels.public_question_no}</option>
					<option value="1">{$labels.public_question_yes}</option>
				</select>
			</div>
		{/if}	
		
		<div class="{cycle values="row_even,row_odd"}">
			<label for="c_senders_comments" class="field_label">{$labels.comment}</label>
			<textarea name="c[senders_comments]" id="c_senders_comments" cols="78" rows="7" class="field">{$values.comment}</textarea>	
		</div>	
		
	{include file="form_bottom.tpl" g_resource="browsing/contact_forms/common"}
{/if}
