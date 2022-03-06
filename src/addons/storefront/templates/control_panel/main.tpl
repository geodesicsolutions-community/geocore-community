{* 7.6.3-181-gd1b5623 *}
{include file='control_panel/header.tpl'}
	{* header.tpl starts a div for main column *}
	{if $show_traffic}
		<div id="storefront_stats">
			<div class="content_box">
				<h3 class="subtitle">{$msgs.usercp_stats_legend_header}</h3>
				<div class="{cycle values='row_even,row_odd'}">
					<ul class="horizontal_list clearfix">
						<li><strong class="highlight">{$msgs.usercp_stats_legend_uniquelabel}</strong></li>
						<li><div class="unique_visits_legend">{$msgs.usercp_stats_legend_uniquekey}</div></li>
						<li><strong class="highlight">{$msgs.usercp_stats_legend_totallabel}</strong></li>
						<li><div class="total_visits_legend">{$msgs.usercp_stats_legend_totalkey}</div></li>
					</ul>
					<div id="stats_controls">
						<a href="#" onclick="jQuery('#stats_days').show(); jQuery('#stats_months').hide(); jQuery('#stats_years').hide(); return false;" class="button">{$msgs.usercp_stats_legend_lastmonth}</a>  
						<a href="#" onclick="jQuery('#stats_days').hide(); jQuery('#stats_months').show(); jQuery('#stats_years').hide(); return false;" class="button">{$msgs.usercp_stats_legend_lastyear}</a>  
						<a href="#" onclick="jQuery('#stats_days').hide(); jQuery('#stats_months').hide(); jQuery('#stats_years').show(); return false;" class="button">{$msgs.usercp_stats_legend_lastthree}</a>
					</div>
				</div>
			</div>
			
			{* 
				The following variables are available for use and hold traffic data:
					$traffic.lastMonth (data in days)
					$traffic.lastYear (data in 30-day periods)
					$traffic.lastThreeYears (data in 365-day periods)
				
				each of them is constructed similarly. using $x = $traffic.lastYear as an example:
					$x.max => maximal number of total visits that happened during the last year
					$x.periods => array containing data for each month of the last year, constructed as follows:
						{foreach from=$x.periods item=p}
							$p.from => beginning of this month, in unix ticktime
							$p.to => end of this month, in unix ticktime
							$p.total => total visits during this month
							$p.unique => unique visits during this month
						{/foreach}
			*}
			
			
			{* Format string used for all dates used below. Follows the syntax of the smarty |date_format modifier.
			   More info:  http://www.smarty.net/manual/en/language.modifier.date.format.php *}

			<div class="content_box" id="stats_days">
				<h3 class="subtitle">{$msgs.usercp_stats_label_month}</h3>
				{foreach from=$traffic.lastMonth.periods item='day'}
				
					{math equation="ceil(total / max * full) + padding"
					total=$day.total
					max=$traffic.lastMonth.max
					full = 600
					padding = 30 
					assign='outerWidth'}
					
					{math equation="floor(100 * unique / total)"
					total=$day.total
					unique=$day.unique
					assign='innerWidth'}
								
					<div class="{cycle values='row_even,row_odd'} clearfix">
						<strong>
							{$day.from|date_format:$date_format} 
						</strong>
						
						<div class='total_visits' style='width: {$outerWidth}px;' title="{$day.total} total visits on {$day.from|date_format:$date_format}">
							{if $day.total > $day.unique}
								<div class='unique_visits' style='width: {$innerWidth}%; float:left; padding-right:1px;' title="{$day.unique} unique visits on {$day.from|date_format:$date_format}">
									{$day.unique}
								</div>
							{/if}
							{$day.total}
						</div>
					</div>
				{/foreach}
			</div>
			
			<div class="content_box" id="stats_months" style="display: none;">
				<h3 class="subtitle">{$msgs.usercp_stats_label_year}</h3>
				{foreach from=$traffic.lastYear.periods item='month'}
				
					{math equation="ceil(total / max * full) + padding"
					total=$month.total
					max=$traffic.lastYear.max
					full = 600
					padding = 30 
					assign='outerWidth'}
					
					{math equation="floor(100 * unique / total)"
					total=$month.total
					unique=$month.unique
					assign='innerWidth'}
								
					<div class="{cycle values='row_even,row_odd'} clearfix">
						<strong>
							{$month.from|date_format:$date_format} to {$month.to|date_format:$date_format}  
						</strong>
						
						<div class='total_visits' style='width: {$outerWidth}px;' title="{$month.total} total visits from {$month.from|date_format:$date_format} to {$month.to|date_format:$date_format}">
							{if $month.total > $month.unique}
								<div class='unique_visits' style='width: {$innerWidth}%; float:left; padding-right:1px;' title="{$month.unique} unique visits from {$month.from|date_format:$date_format} to {$month.to|date_format:$date_format}">
									{$month.unique}
								</div>
							{/if}
							{$month.total}
						</div>
					</div>
				{/foreach}
			</div>
			
			<div class="content_box" id="stats_years" style="display: none;">
				<h3 class="subtitle">{$msgs.usercp_stats_label_three}</h3>
				{foreach from=$traffic.lastThreeYears.periods item='year'}
				
					{math equation="ceil(total / max * full) + padding"
					total=$year.total
					max=$traffic.lastThreeYears.max
					full = 600
					padding = 30 
					assign='outerWidth'}
					
					{math equation="floor(100 * unique / total)"
					total=$year.total
					unique=$year.unique
					assign='innerWidth'}
								
					<div class="{cycle values='row_even,row_odd'} clearfix">
						<strong>
							{$year.from|date_format:$date_format} to {$year.to|date_format:$date_format}  
						</strong>
						
						<div class='total_visits' style='width: {$outerWidth}px;' title="{$year.total} total visits from {$year.from|date_format:$date_format} to {$year.to|date_format:$date_format}">
							{if $year.total > $year.unique}
								<div class='unique_visits' style='width: {$innerWidth}%; float:left; padding-right:1px;' title="{$year.unique} unique visits from {$year.from|date_format:$date_format} to {$year.to|date_format:$date_format}">
									{$year.unique}
								</div>
							{/if}
							{$year.total}
						</div>
					</div>
				{/foreach}
			</div>
			
		</div>
	{/if}
	<div class="center">
		<a class="button" href="{$classifieds_file_name}?a=4">{$msgs.usercp_back_to_my_account}</a>
	</div>
</div>
{* end of div started in header.tpl *}
