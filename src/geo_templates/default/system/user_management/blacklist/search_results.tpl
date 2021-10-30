{* 7.6.3-149-g881827a *}

<div class="content_box">
	<h1 class="title my_account">{$messages.500170}</h1>
	<h3 class="subtitle">{$messages.500172}</h3>
	<p class="page_instructions">{$messages.102788}</p>
</div>

<div class="content_box">
	{if $count > 0}
		<form action="{$formTarget}" method="post">
			<table style="border-style: none; width: 100%;">
				<tr class="column_header">
					<td>{$messages.102831}</td>
					{if $showEmail}<td>{$messages.102832}</td>{/if}
					<td style="text-align: center;">{$messages.102833}</td>
					<td style="text-align: center;">{$messages.102834}</td>
				</tr>
				{foreach from=$users item=user key=i}
					<tr class="{cycle values='row_odd,row_even'}">
						<td>{$user.username}</td>
						{if $user.email}<td>{$user.email}</td>{/if}
						<td style="text-align: center;">{$user.feedback}</td>
						<td style="text-align: center;"><input type="checkbox" name="d[user_id][{$i}]" value="{$user.id}" /></td>
					</tr>
				{/foreach}
			</table>
			<div class="center">
				<input type="hidden" name="d[insertcount]" value="{$count}" />
				<input type="submit" name="addUsers" value="{$messages.102842}" class="button" />
			</div>
		</form>
	{else}
		<div class="field_error_box">{$messages.102835}</div>
	{/if}
</div>