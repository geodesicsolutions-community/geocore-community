{* 7.6.3-149-g881827a *}

<div class="content_box">
	<h1 class="title my_account">{$messages.500170}</h1>
	<h3 class="subtitle">{$messages.102830}</h3>
	<p class="page_instructions">{$messages.103000}</p>
</div>

<div class="content_box">
	{if $showUsers}
		<form action="{$formTarget}" method="post">
			<table style="width: 100%;">
				<tr class="column_header">
					<td>{$messages.102831}</td>
					{* By default, should not show e-mail addresses!  Un-comment to show anyways *}
					{*<td>{$messages.102832}</td>*}
					<td style="text-align:center;">{$messages.102833}</td>
					<td style="text-align:center;">{$messages.102836}</td>
				</tr>
				
				{foreach from=$users item=user key=i}
					<tr class="row_{cycle values="even,odd"}">
						<td>{$user.username}</td>
						{* By default, should not show e-mail addresses!  Un-comment to show anyways *}
						{* <td>{$user.email}</td> *}
						<td style="text-align:center;">{$user.feedback}</td>
						<td style="text-align:center;"><input type="checkbox" name="d[user_id][{$i}]" value="{$user.id}" /></td>
					</tr>
				{/foreach}
			</table>
			<div class="center">
				<input type="hidden" name="d[updatecount]" value="{$count}" />
				<input type="submit" name="submit_value" value="{$messages.102836}" class="button" />
			</div>
		</form>
	{else}
		{* no users to show *}
		<div class="note_box">{$messages.103001}</div>
	{/if}
</div>
