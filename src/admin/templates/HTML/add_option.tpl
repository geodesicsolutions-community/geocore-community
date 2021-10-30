{* 6.0.7-3-gce41f93 *}
{if !isset($html_value)}
	<div class='{$color}'{if $id} id='{$id}' name='{$id}'{/if}{if $color_assist} style='background-color:{$color_assist}'{/if}>
		<div class='centerColumn'>
			{$option}
		</div>
		<div class='clearColumn'></div>
	</div>
{else}
	<div class='{$color} table_line'{if $id} id='{$id}'{/if}{if $color_assist} style='background-color:{$color_assist}'{/if}>
		{if isset($left_html)}
			<span style='position:relative;top:10px;font-weight: bold'>
				{$left_html}
			</span>	
		{/if}
		<div class='leftColumn'>
			{$option}
			{if $eg}
				<br /><div class='small_font'>{$eg}</div>
			{/if}
		</div>
		<div class='rightColumn'>
			{$html_value}
			{if $right_html}
				<div style='position:relative;float:right; margin-right: 10px; top: -16px'>
					<div style='position:relative; width: 140px; text-align: left'>{$right_html}</div>
				</div>	
			{/if}
		</div>
		<div class='clearColumn'></div>
	</div>
{/if}