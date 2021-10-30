{* 6.0.7-3-gce41f93 *}

{if $token_entries>1}
	<table class="content_box">
		<thead>
			<tr class="row_header">
				<td> {$msgs.user_info_count_column_header} </td>
				<td> {$msgs.user_info_expire_column_header} </td>
			</tr>
		</thead>
		<tbody>
			{foreach $allTokens as $token}
				{$tokencount=$tokencount+$token.token_count}
				<tr class="{cycle values='row_even,row_odd'}">
					<td class="center">{$token.token_count}</td>
					<td class="center">{$token.expire|format_date:$entry_date_configuration}</td>
				</tr>
			{/foreach}
			<tr class="row_header">
				<td colspan="2">{$msgs.user_info_total_column_header} {$tokencount}</td>
			</tr>
		</tbody>
	</table>
{else}
	{$allTokens.0.token_count} ({$msgs.user_info_expires_text} {$allTokens.0.expire|format_date:$entry_date_configuration})
{/if}