{* 17.04.0-12-g03921c2 *}

	<div class="row tile_count">
		<div class="col-lg-2 col-md-4 col-sm-4 col-xs-6 tile_stats_count">
			<span class="count_top"><i class="fa fa-users"></i> Users</span>
			<div class="count">{$stats.users.total}</div>
			<span class="count_bottom">Total Registered</span>
		</div>
		<div class="col-lg-2 col-md-4 col-sm-4 col-xs-6 tile_stats_count">
			<span class="count_top"><i class="fa fa-clipboard"></i> Registrations</span>
			<div class="count">
				{if $stats.users.registrations > 0}<a href="index.php?page=register_unapproved&amp;mc=registration_setup"><b class="red">{/if}
				{$stats.users.registrations}
				{if $stats.users.registrations > 0}</b></a>{/if}
			</div>
			<span class="count_bottom">
				{if $stats.users.registrations > 0}<a href="index.php?page=register_unapproved&amp;mc=registration_setup"><b class="red">{/if}
				Pending Approval
				{if $stats.users.registrations > 0}</b></a>{/if}
			</span>
		</div>
		<div class="col-lg-2 col-md-4 col-sm-4 col-xs-6 tile_stats_count">
			<span class="count_top"><span class="glyphicon glyphicon-sunglasses"></span> Visitors</span>
			<div class="count">{$stats.users.current}</div>
			<span class="count_bottom">Last 30 Minutes</span>
		</div>
		<div class="col-lg-2 col-md-4 col-sm-4 col-xs-6 tile_stats_count">
			<span class="count_top"><i class="fa fa-newspaper-o"></i> Listings</span>
			<div class="count">{$stats.auctions.count + $stats.classifieds.count}</div>
			<span class="count_bottom">Total Live</span>
		</div>
		<div class="col-lg-2 col-md-4 col-sm-4 col-xs-6 tile_stats_count">
			<span class="count_top"><i class="fa fa-newspaper-o"></i> Listings</span>
			<div class="count">
				{if ($stats.auctions.unapproved + $stats.classifieds.unapproved) > 0}<a href="index.php?page=orders_list_items&amp;narrow_item_status=pending"><b class="red">{/if}
				{($stats.auctions.unapproved + $stats.classifieds.unapproved)}
				{if ($stats.auctions.unapproved + $stats.classifieds.unapproved) > 0}</b></a>{/if}
			</div>
			<span class="count_bottom">
				{if ($stats.auctions.unapproved + $stats.classifieds.unapproved) > 0}<a href="index.php?page=orders_list_items&amp;narrow_item_status=pending"><b class="red">{/if}
				Pending Approval
				{if ($stats.auctions.unapproved + $stats.classifieds.unapproved) > 0}</b></a>{/if}
			</span>
		</div>
		<div class="col-lg-2 col-md-4 col-sm-4 col-xs-6 tile_stats_count">
			<span class="count_top"><i class="fa fa-database"></i> Software Version</span>
			<div id="licenseVersionInfo">
				<div class="count"><i class="fa fa-spinner fa-spin"></i></div>
				<span class="count_bottom"> </span>
			</div>
		</div>
	</div>

	{if $adminMsgs}<div class="row">{$adminMsgs}</div>{/if}
	
	<div class="row">
		<div class="col-xs-12 col-lg-6">
			<fieldset>
				<legend><i class="fa fa-clipboard"></i> Recent Registrations</legend>
				<div class="table-responsive">
					<table class="table table-hover table-striped">
						<thead>
							<th>Username (ID)<br />Email</th>
							<th>Status</th>
							<th>Date</th>
						</thead>
						<tbody>
							{foreach $stats.users.recent as $u}
								<tr>
									<td><a href="index.php?mc=users&page=users_view&b={$u.id}">{$u.username} ({$u.id})</a><br />{$u.email}</td>
									<td>{$u.status}</td>
									<td>{$u.joined}</td>
								</tr>
							{foreachelse}
								<tr>
									<td colspan="4">No Registrations Found</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
				<div class="right"><a href="index.php?page=users_list&mc=users">View All</a></div>
				<div id="new-users-chart"></div>
			</fieldset>
			<fieldset>
				<legend><i class="fa fa-edit"></i> Recent Orders</legend>
				<div class="table-responsive">
					<table class="table table-hover table-striped">
						<thead>
							<th>Order ID</th>
							<th>Username (ID)</th>
							<th>Amount</th>
							<th>Date</th>
						</thead>
						<tbody>
							{foreach $stats.orders.recent as $o}
								<tr>
									<td><a href="index.php?page=orders_list_order_details&order_id={$o.id}">{$o.id}</a> {$o.contents}</td>
									<td><a href="index.php?mc=users&page=users_view&b={$o.user_id}">{$o.username} ({$o.user_id})</a></td>
									<td>{$o.amount}</td>
									<td>{$o.date}</td>
								</tr>
							{foreachelse}
								<tr>
									<td colspan="4">No Orders Found</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
				<div class="right"><a href="index.php?page=orders_list&narrow_order_status=all&narrow_gateway_type=all&sortBy=created&sortOrder=down">View All</a></div>
				<div id="revenue-chart"></div>
			</fieldset>
			<div>
				{include file='home/landingPage.tpl'}
			</div>
		</div>
		<div class="col-xs-12 col-lg-6">
			{if !$hide_getting_started and $getting_started_completion lt 100}
				<div>
					{include file='home/getting_started.tpl'}
				</div>
			{/if}
			
			
			<div>
				<fieldset>
					<legend><span class="glyphicon glyphicon-sunglasses"></span> Current Visitor Stats</legend>
					<div>
						<div id="device-chart"></div>
						<div id="browser-chart"></div>
					</div>
				</fieldset>
			</div>
			
			
			{if !$white_label and !$geoturbo_status}
				{if $is_trial_demo}
					<div>
						{include file='home/demo.tpl'}
					</div>
				{else}
					<div>
						{if $product.leased}
							{include file='home/leased.tpl'}
						{else}
							{include file='home/downloads.tpl'}
						{/if}
					</div>
					<div>
						{include file='home/support.tpl'}
					</div>
				{/if}
			{/if}
		</div>
	</div>
		
	<div class="row">
		<div class="table-responsive col-xs-12">
			<fieldset>
				<legend><i class="fa fa-bar-chart"></i> Detailed Stats</legend>
				<div>
					<div class="col-xs-12 col-lg-4">
						<table class="table table-hover table-striped table-bordered">
							<tr>
								<td>Total Orders</td>
								<td class="right">
									{if $stats.orders.total > 0}<a href="index.php?page=orders_list&amp;narrow_order_status=all&amp;narrow_gateway_type=all">{/if}
										{$stats.orders.total}
									{if $stats.orders.total > 0}</a>{/if}
								</td>
							</tr>
							<tr>
								<td>Total Items</td>
								<td class="right">
									{if $stats.orders.total_items > 0}<a href="index.php?page=orders_list_items&amp;narrow_item_status=all&amp;narrow_item_type=all">{/if}				
										{$stats.orders.total_items}
									{if $stats.orders.total_items > 0}</a>{/if}
								</td>
							</tr>
							<tr>
								<td>Active Orders</td>
								<td class="right">
									{if $stats.orders.active > 0}<a href="index.php?page=orders_list&amp;narrow_order_status=active&amp;narrow_gateway_type=all">{/if}
										{$stats.orders.active}
									{if $stats.orders.active > 0}</a>{/if}
								</td>
							</tr>
							<tr>
								<td>Orders Awaiting Payment</td>
								<td class="right">
									{if $stats.orders.pending > 0}<a href="index.php?page=orders_list&amp;narrow_order_status=pending&amp;narrow_gateway_type=all">{/if}
										{$stats.orders.pending}
									{if $stats.orders.pending > 0}</a>{/if}
								</td>
							</tr>
							<tr>
								<td>Orders Awaiting Approval</td>
								<td class="right">
									{if $stats.orders.pending_admin > 0}<a href="index.php?page=orders_list&amp;narrow_order_status=pending_admin&amp;narrow_gateway_type=all">{/if}
										{$stats.orders.pending_admin}
									{if $stats.orders.pending_admin > 0}</a>{/if}
								</td>
							</tr>
							
							<tr>
								<td>Items Awaiting Approval</td>
								<td class="right">
									{if $stats.orders.waiting_items > 0}<a href="index.php?page=orders_list_items&amp;narrow_item_status=pending&amp;narrow_item_type=all">{/if}				
									{$stats.orders.waiting_items}
								{if $stats.orders.waiting_items > 0}</a>{/if}
								</td>
							</tr>
							<tr>
								<td>Incomplete Orders</td>
								<td class="right">
									{if $stats.orders.incomplete > 0}<a href="index.php?page=orders_list&amp;narrow_order_status=incomplete&amp;narrow_gateway_type=all">{/if}
										{$stats.orders.incomplete}
									{if $stats.orders.incomplete > 0}</a>{/if}	
								</td>
							</tr>
							<tr>
								<td>Suspended Orders</td>
								<td class="right">
									{if $stats.orders.suspended > 0}<a href="index.php?page=orders_list&amp;narrow_order_status=suspended&amp;narrow_gateway_type=all">{/if}				
									{$stats.orders.suspended}
								{if $stats.orders.suspended > 0}</a>{/if}
								</td>
							</tr>
							<tr>
								<td>Canceled Orders</td>
								<td class="right">
									{if $stats.orders.canceled > 0}<a href="index.php?page=orders_list&amp;narrow_order_status=canceled&amp;narrow_gateway_type=all">{/if}
										{$stats.orders.canceled}
									{if $stats.orders.canceled > 0}</a>{/if}
								</td>
							</tr>
							<tr>
								<td>Fraud Orders</td>
								<td class="right">
									{if $stats.orders.fraud > 0}<a href="index.php?page=orders_list&amp;narrow_order_status=fraud&amp;narrow_gateway_type=all">{/if}
										{$stats.orders.fraud}
									{if $stats.orders.fraud > 0}</a>{/if}
								</td>
							</tr>
						
						</table>
					</div>
					<div class="col-xs-12 col-lg-4">
						<table class="table table-hover table-striped table-bordered">
							<tr>
								<td>Registrations Awaiting Approval</td>
								<td class="right">
									{if $stats.users.registrations > 0}<a href="index.php?page=register_unapproved&amp;mc=registration_setup">{/if}
										{$stats.users.registrations}
									{if $stats.users.registrations > 0}</a>{/if}
								</td>
							</tr>
							<tr>
								<td>Total Registered Users</td>
								<td class="right">
									<a href="index.php?page=users_list&amp;mc=users">{$stats.users.total}</a>
								</td>
							</tr>
							<tr>
								<td>New Registrations (Last 24 hours)</td>
								<td class="right">
									{$stats.users.last1}
								</td>
							</tr>
							<tr>
								<td>New Registrations (Last 7 days)</td>
								<td class="right">
									{$stats.users.last7}
								</td>
							</tr>
							<tr>
								<td>New Registrations (Last 30 days)</td>
								<td class="right">
									{$stats.users.last30}
								</td>
							</tr>
							<tr>
								<td>Total Live Classifieds</td>
								<td class="right">
									{$stats.classifieds.count}
								</td>
							</tr>
							<tr>
								<td>Total Users with Live Classifieds</td>
								<td class="right">
									{$stats.classifieds.users}
								</td>
							</tr>
							<tr>
								<td>Total Views for Live Classifieds</td>
								<td class="right">
									{$stats.classifieds.viewed}
								</td>
							</tr>
							<tr>
								<td>Classifieds Awaiting Approval</td>
								<td class="right">
									{if $stats.classifieds.unapproved > 0}<a href="index.php?page=orders_list_items&amp;narrow_item_status=pending&amp;narrow_item_type=classified">{/if}
									{$stats.classifieds.unapproved}
									{if $stats.classifieds.unapproved > 0}</a>{/if}
								</td>
							</tr>
							<tr>
								<td>Total Live Auctions</td>
								<td class="right">
									{$stats.auctions.count}
								</td>
							</tr>
							<tr>
								<td>Total Users with Live Auctions</td>
								<td class="right">
									{$stats.auctions.users}
								</td>
							</tr>
							<tr>
								<td>Total Views for Live Auctions</td>
								<td class="right">
									{$stats.auctions.viewed}
								</td>
							</tr>
							<tr>
								<td>Auctions Awaiting Approval</td>
								<td class="right">
									{if $stats.auctions.unapproved > 0}<a href="index.php?page=orders_list_items&amp;narrow_item_status=pending&amp;narrow_item_type=auction">{/if}
									{$stats.auctions.unapproved}
									{if $stats.auctions.unapproved > 0}</a>{/if}
								</td>
							</tr>
						</table>
					</div>
					<div class="col-xs-12 col-lg-4">
						<table class="table table-hover table-striped table-bordered">
							<tr>
								<td>Total Addons Installed</td>
								<td class="right">
									<a href="index.php?page=addon_tools&amp;mc=addon_management">{$stats.other.addonsInstalled}</a>
								</td>
							</tr>
							<tr>
								<td>Total Addons Enabled</td>
								<td class="right">
									<a href="index.php?page=addon_tools&amp;mc=addon_management">{$stats.other.addonsEnabled}</a>
								</td>
							</tr>
							<tr>
								<td>Total Languages Installed</td>
								<td class="right">
									<a href="index.php?page=languages_home&amp;mc=languages">{$stats.other.languagesInstalled}</a>
								</td>
							</tr>
							<tr>
								<td>Total Languages Enabled</td>
								<td class="right">
									<a href="index.php?page=languages_home&amp;mc=languages">{$stats.other.languagesEnabled}</a>
								</td>
							</tr>
							<tr>
								<td>Live listings with Bolding</td>
								<td class="right">
									{$stats.extras.bolding}
								</td>
							</tr>
							<tr>
								<td>Live listings with Better Placement</td>
								<td class="right">
									{$stats.extras.better_placement}
								</td>
							</tr>
							{foreach $stats.extras.featured as $fLevel => $fCount}
									<tr>
										<td>Live listings with Featured Level {$fLevel}</td>
										<td class="right">
											{$fCount}
										</td>
									</tr>
							{/foreach}
							{if $stats.extras.attention_getter !== false}
								<tr>
									<td>Live listings with an Attention Getter</td>
									<td class="right">
										{$stats.extras.attention_getter}
									</td>
								</tr>
							{/if}
							{if $stats.extras.charitable !== false && $stats.extras.charitable > 0}
								<tr>
									<td>Live listings with a Charitable Badge</td>
									<td class="right">
										{$stats.extras.charitable}
									</td>
								</tr>
							{/if}
						</table>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
	<script type="text/javascript">
		google.charts.load("current", { packages:["corechart"] });
		google.charts.setOnLoadCallback(drawGoogleCharts);
		function drawGoogleCharts() {
		
			//pie chart for device types
			var deviceData = google.visualization.arrayToDataTable([
				['Device Type', 'Current Users'],
				['Desktop', {if $devices.desktop}{$devices.desktop}{else}0{/if}],
				['Tablet', {if $devices.tablet}{$devices.tablet}{else}0{/if}],
				['Phone', {if $devices.phone}{$devices.phone}{else}0{/if}],
			]);
			var deviceOptions = {
				title: 'Device Types',
				titleTextStyle: { color: "#ff4500", fontSize: 14, bold: false },
				is3D: true,
				slices: [{ color: "#3498db" },{ color: "#9b59b6" },{ color: "#e74c3c" }],
				legend: { position: 'bottom' }
			};
			var devices = new google.visualization.PieChart(document.getElementById('device-chart'));
			devices.draw(deviceData, deviceOptions);
			
			//pie chart for browser types
			var browserData = google.visualization.arrayToDataTable([
				['Browser Type', 'Current Users'],
				['Chrome', {if $browsers.Chrome}{$browsers.Chrome}{else}0{/if}],
				['Firefox', {if $browsers.Firefox}{$browsers.Firefox}{else}0{/if}],
				['IE / Edge', {if $browsers.IE}{$browsers.IE}{else}0{/if}],
				['iPhone', {if $browsers.iPhone}{$browsers.iPhone}{else}0{/if}],
				['Android', {if $browsers.Android}{$browsers.Android}{else}0{/if}]
			]);
			var browserOptions = {
				title: 'Browser Types',
				titleTextStyle: { color: "#ff4500", fontSize: 14, bold: false },
				is3D: true,
				slices: [{ color: "#3498db" },{ color: "#9b59b6" },{ color: "#e74c3c" },{ color: "#f7ad02" },{ color: "#1abb9c" },{ color: "#73879c" }],
				legend: { position: 'bottom' }
			};
			var browsers = new google.visualization.PieChart(document.getElementById('browser-chart'));
			browsers.draw(browserData, browserOptions);
			
			//line chart for new users
			var newUsersData = google.visualization.arrayToDataTable([
				['Date', 'New Registrations'],
				{foreach $newUsers as $date => $number}
					['{$date}', {$number}],
				{/foreach}
			]);
			var newUsersOptions = {
				title: 'New Registrations in the Last Week',
				titleTextStyle: { color: "#ff4500", fontSize: 14, bold: false },
				legend: { position: 'none' },
				vAxis: { minValue: 0, viewWindow: { min: 0 }, textPosition: 'none' },
				series: { 0: { color: "#ff4500" } }
			};
			var newUsers = new google.visualization.LineChart(document.getElementById('new-users-chart'));
			newUsers.draw(newUsersData, newUsersOptions);
			
			{if $dailyTransactions}
				//line chart for transactions
				var revenueData = google.visualization.arrayToDataTable([
					['Date', 'Revenue'],
					{foreach $dailyTransactions as $date => $amount}
						['{$date}', {$amount}],
					{/foreach}
				]);
				var revenueOptions = {
					title: 'Revenue in the Last Week',
					titleTextStyle: { color: "#ff4500", fontSize: 14, bold: false },
					legend: { position: 'none' },
					vAxis: { minValue: 0, viewWindow: { min: 0 }, format: 'currency' },
					series: { 0: { color: "#ff4500" } }
				};
				var revenue = new google.visualization.LineChart(document.getElementById('revenue-chart'));
				revenue.draw(revenueData, revenueOptions);
			{/if}
			
			jQuery(window).resize(function() {
				devices.draw(deviceData, deviceOptions);
				browsers.draw(browserData, browserOptions);
				newUsers.draw(newUsersData, newUsersOptions);
				{if $dailyTransactions}
					revenue.draw(revenueData, revenueOptions);
				{/if}
			});
		}
	</script>