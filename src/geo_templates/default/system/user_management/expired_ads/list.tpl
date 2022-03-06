{* 7.6.3-149-g881827a *}

<div class="content_box">
	<h1 class="title my_account">{$messages.628}</h1>
	<h3 class="subtitle">{$messages.438}</h3>
	
	{if $showExpiredAds}
		<p class="page_instructions">{$messages.439}</p>
	{else}
		{* no expired ads to show *}
		<div class="note_box">{$messages.440}</div>
	{/if}
</div>
{if $showExpiredAds}

	
	<div class="content_box">
		<table style="width: 100%;">
			<tr class="column_header">
				{if $is_ca && $bothListingTypes}<td>{$messages.200003}</td>{/if}
				<td>{$messages.441}</td>
				<td>{$messages.442}</td>
				<td>{$messages.443}</td>
			</tr>
			{foreach from=$expired item=exp key=k}
				{cycle values='row_odd,row_even' assign=row}
				<tr class="{$row}">
					{if $is_ca && $bothListingTypes}
						<td>
							{if $exp.type == 1}
								{$messages.200005}
							{elseif $exp.type == 2}
								{$messages.200004}
							{/if}
						</td>
					{/if}
					<td width="100%">
						<a href="{$exp.link}" class="{$exp.css}">
							<strong>{$exp.title|fromDB}</strong></a> 
							<span style="font-size: 0.8em; color: #666;">({$exp.id})</span>
						
						<br />
						<div style="height: 4px;"> </div>
						<div id="button_{$k}">
							<input type="button" onclick="showActionsForRow({$k}); return false;" class="mini_button" value="{$messages.500897}" />
						</div>
						<div id="actions_{$k}" style="display: none;">
							{if $exp.renewLink}<a href="{$exp.renewLink}" title="{$messages.866}" class="mini_button"><img src="{external file='images/buttons/listing_renew.png'}" alt="{$messages.866}" /></a>{/if}
							<a href="{$exp.detailsLink}" title="{$messages.444}" class="mini_button"><img src="{external file='images/buttons/listing_view.png'}" alt="{$messages.444}" /></a>
							{if $allow_copy}<a href="{$exp.copyLink}" title="{$messages.200177}" class="mini_button"><img src="{external file='images/buttons/listing_copy.png'}" alt="{$messages.200177}" /></a>{/if}
							<a href="{$exp.deleteLink}" title="{$messages.500086}" class="mini_button"><img src="{external file='images/buttons/listing_delete.png'}" alt="{$messages.500086}" /></a>
						</div>
					</td>
					<td class="nowrap center">{$exp.start_date}</td>
					<td class="nowrap center">{$exp.end_date}</td>
							
				</tr>
				{if $exp.type == 2 && ($exp.showStandardWinner || $exp.showDutchWinner)}
					{* auction -- show wining bidders row *}
					<tr class="{$row}">
						<td></td>
						<td colspan="4">
							{if $exp.showStandardWinner}
								{$exp.winner} ({$exp.winnerMail}) - {$exp.amount}
							{elseif $exp.showDutchWinner}
								{foreach from=$exp.winners item=win}
									{$win.quantity} - {$win.username} {$win.email} - {$win.amount}<br />
								{/foreach}
							{/if}
						</td>
					</tr>
				{/if}
			{/foreach}
		</table>
	</div>
	{if $pagination != ''}
		{$pagination}
	{/if}
{/if}

{add_footer_html}
<script>
	showing = false;
	var showActionsForRow = function(row) {
		
		//hide previously chosen action bar
		if(showing !== false) {
			jQuery('#actions_'+showing).hide();
			jQuery('#button_'+showing).show();
		}
		
		//hide this row's manage button, and show its action bar
		jQuery('#button_'+row).hide();
		jQuery('#actions_'+row).show();
		showing = row;
	
	};
</script>
{/add_footer_html}

{if count($pending) > 0}
	<br />
	<div class="content_box">
		<h3 class="title">{$messages.102854}</h3>
		<p class="page_instructions">{$messages.102855}</p>
	
		<table style="border: none; width: 100%; margin: 0 auto;">
			<tr class="column_header">
				<td>{$messages.100441}</td>
				<td>{$messages.100443}</td>
				<td>{$messages.102856}</td>
				<td>{$messages.102785}</td>
			</tr>
			{foreach from=$pending item=p}
				<tr class="{cycle values='row_odd,row_even'}">
					<td><a href="{$p.link}">{$p.title|fromDB}</a></td>
					<td>{$p.ends}</td>
					<td>{$p.date}</td>
					<td><a href="{$p.link}">{$messages.444}</a></td>
				</tr>	
			{/foreach}
		</table>
	</div>
{/if}
	
{if count($finalFees) > 0}
	<br />
	<div class="content_box">
		<h3 class="title">{$messages.103074}</h3>
	
		<table style="width: 100%;">
			<tr class="column_header">
				<td>{$messages.103075}</td>
				<td>{$messages.103076}</td>
				<td>{$messages.103077}</td>
				<td>{$messages.103078}</td>
			</tr>
			{foreach from=$finalFees item=ff}
				<tr class="{cycle values='row_odd,row_even'}">
					<td><a href="{$ff.link}">{$ff.title|fromDB}</a></td>
					<td>{$ff.date}</td>
					<td>{$ff.ends}</td>
					<td>{$ff.amount}</td>
				</tr>
			{/foreach}
		</table>
	</div>
{/if}
<br />
<div class="center">
	<a href="{$userManagementHomeLink}" class="button">{$messages.445}</a>
</div>
