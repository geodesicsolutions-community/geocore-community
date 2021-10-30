{* 7.0.2-66-g28e6e7b *}
{if $error}
	<div class="error_message">{$error}</div>
	<div class="user_management_home_link"><a href="{$userManagementHomeLink}" class="user_management_home_link">{$messages.408}</a></div>
{else}
	<form action="{$formTarget}" method="post">
		<table style="width: 100%; border: none;">
			<tr class="section_title">
				<td colspan="2">{$messages.623}</td>
			</tr>
			<tr class="user_management_page_title">
				<td colspan="2">{$messages.399}</td>
			</tr>
			<tr class="page_description">
				<td colspan="2">{$messages.400}</td>
			</tr>
			
			<tr>
				<td align="right" style="width: 50%;" class="field_labels">{$messages.1190}</td>
				<td style="width: 50%;" class="data_values">{$messageTo}{if $toMe} ({$messages.1195}){/if}</td>
			</tr>
			<tr>
				<td align="right" style="width: 50%;" class="field_labels">{$messages.401}</td>
				<td style="width: 50%;" class="data_values">
					{if $fromKnown}
						{$messageFrom}<input type="hidden" name="d[from]" value="{$user.id}" />
						<input type="hidden" name="d[from_user]" value="{$user.id}" />
					{else}
						<input type="text" name="d[from]" class="data_values" />
					{/if}
				</td>
			</tr>
			
			<tr>
				<td align="right" style="width: 50%;" class="field_labels">{$messages.403}</td>
				<td style="width: 50%;" class="data_values">
					<input type="text" name="d[subject]" value="{$listingTitle}{if $classified_id} - {$classified_id}{/if}" />
					{if $classified_id}<input type="hidden" name="d[classified_id]" value="{$classified_id}" />{/if}
				</td>
			</tr>
			
			<tr class="message_header_row">
				<td colspan="2" align="center">
					{$messages.405}<br />
					<textarea cols="50" rows="15" name="d[message]" class="data_values">{$messages.404}</textarea>
				</td>
			</tr>
			
			<tr class="send_reply_button" style="text-align: center;">
				<td colspan="2"><input type="submit" class="send_reply_button" name="z" value="{$messages.1333}" /></td>
			</tr>
			
			<tr class="user_management_home_link">
				<td colspan="2"><a href="{$userManagementHomeLink}" class="user_management_home_link">{$messages.408}</a></td>
			</tr>
		</table>
	</form>
{/if}