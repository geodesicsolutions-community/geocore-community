{* 6.0.7-3-gce41f93 *}
{include file="system/browsing/contact_forms/common/form_top.tpl"}

		<div class="row_even">
			<label for="c_friends_name" class="field_label">{$labels.friends_name}</label>
			<input type="text" name="c[friends_name]" id="c_friends_name" value="{$values.friends_name}" class="field" />
		</div>
		<div class="row_odd">
			<label for="c_friends_email" class="field_label">{$labels.friends_email}</label>
			<input type="text" name="c[friends_email]" id="c_friends_email" value="{$values.friends_email}" class="field" />
		</div>
		<div class="row_even">
			<label for="c_senders_name" class="field_label">{$labels.your_name}</label>
			{if $values.your_name}
				{$values.your_name}
			{else}
				<input type="text" name="c[senders_name]" id="c_senders_name" value="{$values.your_name}" class="field" />
			{/if}
		</div>
		<div class="row_odd">
			<label for="c_senders_email" class="field_label">{$labels.your_email}</label>
			{if $values.your_email}
				{$values.your_email}
			{else}
				<input type="text" name="c[senders_email]" id="c_senders_email" value="{$values.your_email}" class="field" />
			{/if}
		</div>
		<div class="row_even">
			<label for="c_senders_comments" class="field_label">{$labels.comment}</label>
			<textarea name="c[senders_comments]" id="c_senders_comments" cols="78" rows="7" class="field">{$values.comment}</textarea>
		</div>


{include file="system/browsing/contact_forms/common/form_bottom.tpl"}
