{* 7.3.1-64-g9f6e044 *}

<div>
	<div class="leftColumn">Tokens</div>
	<div class="rightColumn">
		<form action="index.php?page=users_view&amp;b={$user_id}" method="post">
			<table style="width: auto; border: 1px solid black;">
				<thead>
					<tr class="col_hdr_top">
						<th># Tokens</th>
						<th>Expiration</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					{foreach $allTokens as $token}
						{$tokencount=$tokencount+$token.token_count}
						<tr class="{cycle values='row_color1,row_color2'}">
							<td class="center">{$token.token_count}</td>
							<td class="center">{$token.expire|format_date:'M d, Y'}</td>
							<td class="center"><a href="index.php?page=users_view&amp;b={$user_id}&amp;tokens[delete]={$token.id}&amp;auto_save=1" class="mini_cancel lightUpLink">delete</a></td>
						</tr>
					{foreachelse}
						{$tokencount=0}
						<tr><td colspan="3"><div class="page_note">User has no tokens</div></td></tr>
					{/foreach}
					{if $tokencount}
						<tr class="col_ftr">
							<td colspan="3">
								<strong>Total Tokens:</strong> {$tokencount}
								<br /><br />
							</td>
						</tr>
					{/if}
					<tr class="col_ftr">
						<td><input type="text" name="tokens[count]" value="10" size="4" /></td>
						<td>
							<input type="text" name="tokens[expire]" placeholder="YYYY-MM-DD" id="expire" class="dateInput" value="{$smarty.now|format_date:'Y-m-d'}" style="width: 110px;" />
						</td>
						<td><input type="submit" name="auto_save" class="mini_button" value="Add Tokens" /></td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
	<div class="clearColumn"></div>
</div>