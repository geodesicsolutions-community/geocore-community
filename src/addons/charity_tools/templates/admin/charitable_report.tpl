{* 16.09.0-105-ga458f5f *}
{$adminMsgs}
<fieldset>
	<legend>Report Timeframe</legend>
	<div>
		<form action="" method="post" class='form-horizontal'>
			<div class='form-group'>
				<label class='control-label col-xs-12 col-sm-5'>Show Charitable Badge purchases in date range</label>
				<div class="col-xs-12 col-sm-6">
					<div class='input-group'>
						<div class='input-group-addon'>Starting <i class='fa fa-calendar'></i></div>
						<input type="text" name="d[start_date]" id="startDate" class="dateInput form-control" />
					</div>
					<div class='input-group'>
						<div class='input-group-addon'>Ending <i class='fa fa-calendar'></i></div>
						<input type="text" name="d[end_date]" id="endDate" class="dateInput form-control" />
					</div>
				</div>
			</div>
			<div class="center"><input type="submit" value="Run Report" /></div>	
		</form>
	</div>
</fieldset>

{foreach $badgeData as $id => $badge}
	<fieldset>
		<legend>{$badge.name} - {if $badge.region}{$badge.region}{else}All Regions{/if}</legend>
		<div class='table-responsive'>
			<table class='table table-hover table-striped table-bordered'>
				<thead>
					<tr>
						<th>Listing ID</th>
						<th>Purchase Time</th>
						<th>Price</th>
					</tr>
				</thead>
				<tbody>
					{foreach $purchases.$id as $p}
						<tr>
							<td style="text-align: center;">{$p.listing}</td>
							<td style="text-align: center;">{$p.time}</td>
							<td style="text-align: center;">{$p.price}</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
			<div class="center" style="font-weight: bold;">Total Collected: {$badge.total|displayPrice}</div>
		</div>
	</fieldset>
{/foreach}