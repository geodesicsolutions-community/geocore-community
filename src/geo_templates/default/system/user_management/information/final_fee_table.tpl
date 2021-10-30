{* 7.0.2-66-g28e6e7b *}
{if count($ffRows) > 0}
	<h1 class="subtitle">{$messages.200123}</h1>
	
	<table style="width: 100%;" id="FF{$price_plan_id}">
		<tr class="results_column_header">
			<td>{$messages.200119}</td>
			<td></td>
			<td>{$messages.200120}</td>
			<td>{$messages.200121}</td>
			<td>{$messages.500111}</td>
		</tr>
		{foreach from=$ffRows item=row}
			<tr class="{cycle values='row_odd,row_even'}">
				<td>{$row.low}</td>
				<td>to</td>
				<td>{$row.high}</td>
				<td>{$row.charge} %</td>
				<td>{$row.fixed}</td>
			</tr>
		{/foreach}
	</table>
{/if}