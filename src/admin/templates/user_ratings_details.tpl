{* 7.4beta2-29-g1df2207 *}
{$adminMsgs}

<fieldset>
	<legend>Average User Rating</legend>
	<div>
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">Raw Average:</div>
			<div class="rightColumn">{$average_rating_raw}</div>
			<div class="clearColumn"></div>
		</div>
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">Displays as:</div>
			<div class="rightColumn">{$average_rating_rendered}</div>
			<div class="clearColumn"></div>
		</div>
	</div>
</fieldset>

<fieldset>
	<legend>Individual User Ratings</legend>
	<div>
		{foreach $rendered as $from => $render}
			<div class="{cycle values='row_color1,row_color2'}">
				<div class="leftColumn">Rating from User #{$from}:</div>
				<div class="rightColumn">{$render}</div>
				<div class="clearColumn"></div>
			</div>
		{/foreach}
		{if $pagination}{$pagination}{/if}
	</div>
</fieldset>