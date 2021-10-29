{* 7.6.3-149-g881827a *}

{if $no_feedbacks} 
	<div class="content_box">
		<h1 class="title">{$messages.102416}</h1>
		<div class="note_box">{$messages.102499}</div>
	</div>
	
	<br />
	<div class="center">
		{if $feedback_home_link}<a href="{$feedback_home_link}" class="button">{$messages.102422}</a>{/if}
		{if $auction_link}<a href="{$auction_link}" class="button">{$messages.103372}</a>{/if}
	</div>
{else}

	<div class="content_box feedback-header">
		<div style="display:inline; font-size: 2em;"><span class="glyphicon glyphicon-star-empty"></span></div>
		<div style="font-weight: bold; display:inline-block;">
			<div style="font-size: 12px;"><span style="font-size: 16px;">{$messages.500811} <span style="color: #7DAA3B;">{$rated_user_name}</span>{listing tag='seller_rating'}{$seller_rating_scale_explanation}</span><br /> 
				{$messages.102736} {$member_since}</div>
		</div>
	</div>
	
	<div class="half_column_left">
		<div class="content_box">
			<h1 class="title">{$messages.102416}</h1>
		
			<div class="row_even">
				<label class="field_label">{$messages.102967}</label>
				{$feedback_score}
			</div>
			
			<div class="row_odd">
				<label class="field_label">{$messages.102972}</label>
				{$feedback_percentage}%
			</div>
		
			<div class="row_even">
				<label class="field_label">{$messages.102974}</label>
				<strong class="positive">{$pos_count}</strong>
			</div>
		
			<div class="row_odd">
				<label class="field_label">{$messages.102976}</label>
				<strong class="negative">{$neg_count}</strong>
			</div>
		</div>	
	</div>
	
	<div class="half_column_right">
		<div class="content_box">
			<table style="width: 100%;">
				<tr class="column_header">
					<td>{$messages.102968}</td>
					<td class="cntr">{$messages.102969}</td>
					<td class="cntr">{$messages.102970}</td>
					<td class="cntr">{$messages.102971}</td>
				</tr>
				<tr class="row_even">
					<td class="positive">{$messages.102973}</td>
					<td class="positive cntr">{$one_month_pos}</td>
					<td class="positive cntr">{$six_month_pos}</td>
					<td class="positive cntr">{$twelve_month_pos}</td>
				</tr>
				<tr class="row_odd">
					<td class="neutral">{$messages.102975}</td>
					<td class="neutral cntr">{$one_month_neu}</td>
					<td class="neutral cntr">{$six_month_neu}</td>
					<td class="neutral cntr">{$twelve_month_neu}</td>
				</tr>
				<tr class="row_even">
					<td class="negative">{$messages.102977}</td>
					<td class="negative cntr">{$one_month_neg}</td>
					<td class="negative cntr">{$six_month_neg}</td>
					<td class="negative cntr">{$twelve_month_neg}</td>
				</tr>
			</table>
		</div>
		
		{if $score_percentage}
	
			<div class="content_box">
				<div class="row_even">
					<label class="field_label">{$messages.102498}</label> {$feedback_score} ({$score_percentage}%)
				</div>
			</div>
		{/if}
	</div>
	
	<div class="clear"></div>
	
	
	<div class="content_box">
		<table style="border: none; width: 100%;">
			<tr class="column_header">
				<td class="nowrap">{$messages.102417}</td>
				<td class="title">{$messages.102418}</td>
				<td class="nowrap center">{$messages.102419}</td>
				<td class="nowrap center">{$messages.102421}</td>
			</tr>
		
			{foreach from=$display_feedbacks item=fb}
				<tr class="{cycle values="row_even,row_odd" advance=false} bold feedback_cells" style="font-weight: bold;">
					<td class="nowrap"><span style="color: #4174a6;">{$fb.rater_username}</span> <span style="text-transform: uppercase; font-size: .8em;">{if $fb.user_is_seller}{$messages.103361}{else}{$messages.103362}{/if}</span></td>
					<td>{$fb.title} - {$fb.auction_id}</td>
					<td class="nowrap" style="text-align:center;">{$fb.rating}</td>
					<td class="nowrap">{$fb.date}</td>
				</tr>
				<tr class="{cycle values="row_even,row_odd"}" style="border-bottom: 1px solid #DDD;">
					<td colspan="4"><p class="sub_note" style="margin: 0.2em 0.5em"><strong>{$messages.102497}</strong>: {$fb.feedback}</td>
				</tr>
			{/foreach}
		</table>
	</div>
	
	{if $pagination}
		<br />
		{$messages.200175} {$pagination}
	{/if}
	
	<br />
	<div class="center">
		<a href="{$classifieds_url}?a=4&amp;b=22&amp;c=1" class="button">{$messages.102500}</a>
		{if $auction_link}<a href="{$auction_link}" class="button">{$messages.103372}</a>{/if}
	</div>
{/if}