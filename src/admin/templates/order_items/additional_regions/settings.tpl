{* 7.1beta1-1015-g8a4d8d8 *}

<div>
	<p class="page_note">
		<strong>Note:</strong>  All settings below apply to <strong>additional</strong>
		regions, which are in addition to the main "primary" region.
	</p>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">
			Max # Additional Regions
		</div>
		<div class="rightColumn">
			<input type="text" name="additional_regions[max]" value="{$max}" size="4" />
		</div>
		<div class="clearColumn"></div>
	</div>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn"># Free Additional Regions</div>
		<div class="rightColumn">
			<input type="text" name="additional_regions[free]" value="{$free}" size="4" />
		</div>
		<div class="clearColumn"></div>
	</div>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Cost per Additional Region</div>
		<div class="rightColumn">
			{$precurrency}<input type="text" name="additional_regions[cost]" value="{$cost|displayPrice:'':''}" size="11" />{$postcurrency}
		</div>
		<div class="clearColumn"></div>
	</div>
</div>