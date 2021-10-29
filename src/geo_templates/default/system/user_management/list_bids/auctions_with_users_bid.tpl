{* 7.5.3-125-gf5f0a9a *}

<div class="content_box">
	<h1 class="title my_account">{$messages.500169}</h1>
	<h3 class="subtitle">{$messages.102787}</h3>
	<p class="page_instructions">{$messages.102788}</p>
	{if $showAuctions}
		<table style="border-style: none; width: 100%;">
			<tr class="column_header">
				<td>{$messages.102789}</td>
				<td>{$messages.102790}</td>
				<td>{$messages.102791}</td>
				<td>{$messages.102792}</td>
				<td>{$messages.501011}</td>
				<td>{$messages.102794}</td>
			</tr>
			{foreach from=$auctions item=auc}
				<tr class="row_{cycle values='even,odd'}">
					<td>
						<a href="{$auc.link}">{$auc.title|fromDB}</a>{if $auc.expired} - {$messages.102795}{/if}
					</td>
					<td>{$auc.ends}</td>
					<td>{$auc.display_amount}</td>
					<td>
						{$auc.quantity}
					</td>
					{if $auc.auction_type == 1 or $auc.auction_type==3}
						{*standard or reverse auction*}
						<td>
							{if $auc.maxbid}
								{$auc.maxbid} {if $auc.auction_type==1}{$messages.501012}{else}{$messages.501013}{/if}
							{else}
								-
							{/if}
						</td>
						<td>{$auc.payment_link}</td>
					{else}
						{*dutch auction*}
						<td> - </td>
						<td>{$auc.quantity_winning}</td>
					{/if}
				</tr>
			{/foreach}
		</table>
	{else}
		{* no auctions to show *}
		<div class="note_box">{$messages.102801}</div>
	{/if}
</div>

<div class="center">
	<a href="{$userManagementHomeLink}" class="button">{$messages.102800}</a>
</div>