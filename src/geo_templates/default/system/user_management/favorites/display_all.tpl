{* 7.6.3-149-g881827a *}

<div class="content_box">
	<h1 class="title my_account">{$messages.621}</h1>
	<h3 class="subtitle">{$messages.352} {$helpLink}</h3>
	<p class="page_instructions">{$messages.353}</p>

	{if $showFavorites}
		<table style="width: 100%;">
			<tr class="column_header">
				<td class="title">{$messages.358}</td>
				{if $fields.price}<td>{$messages.500224}</td>{/if}
				<td>{$messages.356}</td>
				<td>{$messages.357}</td>
				<td>{$messages.500148}</td>
				{if $use_time_left}
					<td>{$messages.500218}</td>
				{/if}
				<td>{$messages.500094}</td>
			</tr>
			{foreach from=$favs item=f}
				<tr class="{cycle values='row_odd,row_even'} bold">
					<td>
						<a href="{$f.link.href}">{$f.link.text|fromDB}</a>
					
						{foreach from=$f.images item=img}
							<img src="{$img}" alt="" />
						{/foreach}
					</td>
					{if $fields.price}
						<td class="nowrap">{$f.price}</td>
					{/if}
					<td class="nowrap">{$f.date_inserted}</td>
					<td class="nowrap">{$f.date}</td>
					<td class="nowrap">{$f.ends}</td>
					{if $use_time_left}
						<td class="nowrap">{$f.time_left}</td>
					{/if}
					<td class="nowrap"><a href="{$f.removeLink}" class="delete">{$messages.360}</a></td>
				</tr>
				{if $f.description}
					<tr class="{cycle values='row_odd,row_even'}">
						<td colspan="7" class="sub_note">{$f.description}</td>
					</tr>
				{/if}
			{/foreach}
		</table>	
	{else}
		<div class="note_box">{$messages.355}</div>
	{/if}
</div>
<br />
<div class="center">
	<a href="{$userManagementHomeLink}" class="button">{$messages.361}</a>
</div>
