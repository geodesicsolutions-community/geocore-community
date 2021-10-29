{* 7.0.2-66-g28e6e7b *}
<form action="" method="post">
	<table style="border-style: none; width: 100%;">
		 <tr>
			 {foreach from=$parents item=p}
				<td class="filter_dropdown_{if $two}2{else}1{/if}">
					<select name="{$p.selectName}" class="filter_dropdown_{if $two}2{else}1{/if}" onchange="{literal}if(this.options[this.selectedIndex].value != '') this.form.submit();{/literal}">
						<option value="0">{$p.zeroLabel}</option>
						{foreach from=$p.options item=opt}
							<option value="{$opt.value}" {if $opt.selected}selected="selected"{/if}>{$opt.label}</option>
						{/foreach}
					</select>
				</td>
				
				{if !$module.module_display_filter_in_row}
					</tr><tr>
				{/if}
			{/foreach}

		
			{if !$module.module_display_filter_in_row}
				</tr><tr>
			{/if}
			
			{if count($children) > 0}
				<td class="filter_dropdown_{if $two}2{else}1{/if}">
					{foreach from=$children item=child name=childloop}
					
						<select name="{$child.selectName}" class="filter_dropdown_{if $two}2{else}1{/if}" onchange="{literal}if(this.options[this.selectedIndex].value != '') this.form.submit();{/literal}">
							{foreach from=$child.options item=o}
								<option value="{$o.value}" {if $o.selected}selected="selected"{/if}>{$o.label}</option>
							{/foreach}
						</select>
						
						
						{if !$smarty.foreach.childloop.last}
							{if $module.module_display_filter_in_row}
								</td>
								<td>
							{else}
								</td>
								</tr>
								<tr>
								<td class="filter_dropdown_{if $two}2{else}1{/if}">
							{/if}
						{/if}
						
					{/foreach}
				</td>
			{/if}	
		</tr> 
	</table>
</form>
			
					