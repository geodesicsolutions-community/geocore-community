{* 7.6.3-149-g881827a *}

<div class="content_box">
	<h1 class="title">{$messages.2000}</h1>
	<p class="page_instructions">{$messages.2288}</p>

	<h3 class="subtitle">{$listing.title|fromDB}</h3>
	<div class="row_odd">
		<label class="field_label">{$messages.2004}</label>
		<strong class="text_highlight">{$totalVotes}</strong>
	</div>	
	<div class="row_even">
		<label class="field_label">{$messages.2001}</label>
		<strong class="text_highlight">{$oneVotesPercentage}</strong>
	</div>
	<div class="row_odd">
		<label class="field_label">{$messages.2002}</label>
		<strong class="text_highlight">{$twoVotesPercentage}</strong>
	</div>
	<div class="row_even">
		<label class="field_label">{$messages.2003}</label>
		<strong class="text_highlight">{$threeVotesPercentage}</strong>
	</div>
	{if $totalVotes > 0}
		<table style="width: 100%; margin-top:3px;">
			<tr class="column_header">
				<td>{$messages.2005}</td>
				<td>{$messages.2007}</td>
				{if $canDeleteVotes}<td></td>{/if}
			</tr>
			{foreach from=$votes item=vote}
				<tr class="row_{cycle values="even,odd"}">
					<td class="nowrap"><span style="font-size:2em; font-weight: bold;">{$vote.voteType}</span></td>
					
					<td style="width: 100%;">
						<strong><a href="{$classifieds_file_name}?a=6&amp;b={$vote.voter_id}" class="text_highlight">{$vote.voter}</a></strong> ({$vote.date})
						<br />
						<strong>{$vote.title}</strong>
						<br />
						<span class="sub_note">{$vote.comment}</span>
					</td>
					{if $canDeleteVotes}<td class="nowrap"><a href="{$classifieds_file_name}?a=27&amp;b={$listing.id}&amp;d={$vote.id}" class="mini_button">Remove Vote</a></td>{/if}
				</tr>
			{/foreach}
		</table>
	{/if}
</div>
{if $showPagination}<br />
	{$messages.24} {$pagination}<br />
{/if}
<div class="center">
	<a href="{$backToCurrentAdLink}" class="button">{$messages.2012}</a>
</div>