{* 7.6.3-149-g881827a *}
<div class="sidebar-collapse">
	<div class="content_box browsing_filter_container">
		<h1 class="title browsing_filter_title section-collapser"><span class="glyphicon glyphicon-check"></span>&nbsp;{$msgs.browsing_filters_sidebar_title}</h1>
		<div class="content_box browsing_filter_box">
			{foreach $activeFilters as $target => $breadcrumb}
				<div class='{cycle values="row_odd,row_even"}'>
					{$breadcrumb}
				</div>
			{/foreach}
			
			{foreach $filters as $target => $f}
				<div class='{cycle values="row_odd,row_even"}'>
					<div class="browsing_filter_fieldName">{$friendlyNames.$target}</div>
					<div class="browsing_filter_selections">
						{if $f.value === 'RANGE'}
							<form action='{$self}' method='post'>
								<div style="text-align: center; margin: 3px 0;">
									<input type="text" class="field" name="filterRange[{$target}][low]" size="4" placeholder="{$msgs.browsing_filters_placeholder_low}" /> - 
									<input type="text" class="field" name="filterRange[{$target}][high]" size="4" placeholder="{$msgs.browsing_filters_placeholder_high}" />
								</div>
								<div style="text-align: center;"><input type="submit" class="button-compact" value="{$msgs.browsing_filters_filter_button}" /></div>
							</form>
						{elseif $f.value === 'DATE_RANGE'}
							<form action='{$self}' method='post'>
								Start: <input type="text" class="field datepicker" name="filterDate[{$target}][start]" size="10" /><br />
								End: <input type="text" class="field datepicker" name="filterDate[{$target}][end]" size="10" /><br />
								<div class="center"><input type="submit" class="button-compact" value="{$msgs.browsing_filters_filter_button}" /></div>
							</form>
						{elseif $f.value === 'BOOL'}
						<a href="{$self}&amp;setFilter={$target}&amp;filterValue=1">{$msgs.browsing_filters_option_yes}</a> {if $show_counts}<span class="browsing_filter_count">({$f.yes})</span>{/if}<br />
						<a href="{$self}&amp;setFilter={$target}&amp;filterValue=0">{$msgs.browsing_filters_option_no}</a> {if $show_counts}<span class="browsing_filter_count">({$f.no})</span>{/if}
						{else}
							{$hasHidden = false}
							{foreach $f.value as $value => $count}
								{if $count@index == $expandable_threshold}
									<div id="{$target}_more" style="display: none;">
									{$hasHidden = true}
								{/if}
								{if $f.leveled.$value}
									{$level=$f.leveled.$value}
										<a href="{$self}&amp;setFilter={$target}&amp;filterValue={$level.id}">{$level.name}</a> {if $show_counts}<span class="browsing_filter_count">({$count})</span>{/if}
								{elseif $value}
										<a href="{$self}&amp;setFilter={$target}&amp;filterValue={$value}">{$value|fromDB}</a> {if $show_counts}<span class="browsing_filter_count">({$count})</span>{/if}
								{/if}
								{if $value && !$value@last}<br />{/if}
							{/foreach}
							{if $hasHidden}
								</div> {* closes div opened in "if @iteration" above *}
								<div class="center">
										<a href="#" id="{$target}_more_link" class="button-compact" onclick="jQuery('#{$target}_more_link').hide(); jQuery('#{$target}_more').show(300); jQuery('#{$target}_less_link').show(); return false;">{$msgs.browsing_filters_more_btn}</a>
										<a href="#" style="display: none;" id="{$target}_less_link" class="button-compact" onclick="jQuery('#{$target}_less_link').hide(); jQuery('#{$target}_more').hide(300); jQuery('#{$target}_more_link').show(); return false;">{$msgs.browsing_filters_less_btn}</a>
								</div>
							{/if}
						{/if}
					</div>
				</div>
			{/foreach}
			
			{if $numFilters > 0}
				<div class="center">
					<a href="{$self}&amp;resetAllFilters=1" class="button">{$msgs.browsing_filters_reset_all}</a>
				</div>
			{/if}
		</div>
	</div>
</div>
