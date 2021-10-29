{* 16.09.0-105-ga458f5f *}
{$admin_msgs}
<fieldset>
	<legend>Current Discount Codes</legend>
	<div class="table-responsive">
		<table class="table table-hover table-striped table-bordered">
			<thead>
				<tr class="col_hdr_top">
					<th>Name</th>
					<th>Code</th>
					<th>Normal Orders Using</th>
					{if $isEnt}<th>Recurring Orders Using</th>{/if}
					<th>Start Date</th>
					<th>End Date</th>
					<th>Status</th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$codes item=code}
					<tr class="{cycle values='row_color1,row_color2'}">
						<td>
							<strong>{$code.name|fromDB}</strong><br />
							<span class="small_font">{$code.description|fromDB}</span>
						</td>
						<td>{$code.discount_code|fromDB}</td>
						<td class="center">{$code.normal_count}</td>
						{if $isEnt}<td class="center">{$code.recurring_count}</td>{/if}
						<td class="center">
							{$code.starts|format_date:'Y-m-d'}
						</td>
						<td class="center">
							{if $code.ends=='0'}
								Never
							{else}
								{$code.ends|format_date:'Y-m-d'}
							{/if}
						</td>
						<td class="center">{if $code.active}Active{else}Disabled{/if}</td>
						<td class="center">
							<a href="index.php?mc=discounts&amp;page=discounts_edit&amp;discount_id={$code.discount_id}" class="btn btn-info btn-xs" style="margin:0;"><i class="fa fa-pencil"></i> Edit</a>
							<a href="index.php?mc=discounts&amp;page=discounts_delete&amp;c={$code.discount_id}&amp;auto_save=1" class="lightUpLink btn btn-danger btn-xs" style="margin:0;"><i class="fa fa-trash"></i> Delete</a>
							<a href="index.php?mc=discounts&amp;page=discounts_stats&amp;discount_id={$code.discount_id}" class="lightUpLink btn btn-primary btn-xs" style="margin:0;"><i class="fa fa-exchange"></i> Usage Stats</a>
						</td>
					</tr>
				{foreachelse}
					<tr>
						<td>No codes!</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
</fieldset>
<div class="center">
	<br />
	<a href="index.php?mc=discounts&amp;page=discounts_new" class="mini_button">Add New Code</a>
</div>