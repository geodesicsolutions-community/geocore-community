{* 7.5.1-8-gacdaa11 *}
<style type="text/css">
	{literal}
	table.quickFind {
		/*border: 8px solid rgb(221, 221, 221);*/
		font-weight: bold;
		margin: 10px;
	}
	table.quickFind td {
		vertical-align: middle;
		padding: 15px 3px 15px 3px;
		white-space: nowrap;

		font-size:8pt;
		font-weight:bold;
		height:100%;
	}

	table.quickFind .img {
		text-align: right;
		/*width: 30%;*/
	}
	table.quickFind .txt {
		text-align: right;
		/*width: 10%;*/
	}
	table.quickFind .search {
		text-align: left;
	}
	table.quickFind .search input {
		font-size: 8pt;
	}
	input.field {
		width: 100px;
	}

	form {
		display: inline;
	}

	{/literal}
</style>

<div class="closeBoxX"></div>
<div class="lightUpTitle">Quick Find Tools</div>

<table class="quickFind" cellpadding="0" cellspacing="0" align="center">
	<tr class="{cycle values='row_color1,row_color2'}">
		<td class="img"><img src="admin_images/design/icon_user.png" alt="find users" /></td>
		<td class="txt"><a href="index.php?page=users_search">User Search</a></td>
		<td class="search">
			<form action="index.php?mc=users&page=users_search" method="post" style="padding: 15px;">
				<input type="text" name="b[text_to_search]" class="field" />
				<input type="hidden" name="b[search_type]" value="1" />
				<select name="b[field_type]" style="font-size: 8pt;" class="field">
					<option value="1">Username</option>
					<option value="2">Last Name</option>
					<option value="3">First Name</option>
					<option value="4">Email</option>
					<option value="5">Company</option>
					<option value="6">URL</option>
					<option value="7">City</option>
					<option value="8">Phone</option>
				</select>
				<input type="hidden" name="b[search_group]" value="0" />
				<input type="submit" name="auto_save" value="Go" />
			</form>
		</td>
	</tr>
	<tr class="{cycle values='row_color1,row_color2'}">
		<td class="img"><img src="admin_images/design/icon_registration.gif" alt="Search for Listing" /></td>
		<td class="txt">Listing ID #</td>
		<td class="search">
			<form action="index.php" method="get" style="padding: 15px;">
				<input type="hidden" name="page" value="users_view_ad" />
				<input type="text" name="b" size="8" class="field" />
				<input type="submit" value="View Listing" />
			</form>
		</td>
	</tr>
	<tr class="{cycle values='row_color1,row_color2'}">
		<td class="img"><img src="admin_images/design/icon_cost.gif" alt="Search for Order" /></td>
		<td class="txt">Order ID #</td>
		<td class="search">
			<form action="index.php?page=orders_list_order_details" method="post" style="padding: 15px;">
				<input type="hidden" name="page" value="orders_list_order_details" />
				<input type='text' name='orderId' class="field" />
				<input type="submit" value="Get Order" />
			</form>
		</td>
	</tr>
	<tr class="{cycle values='row_color1,row_color2'}">
		<td class="img"><img src="admin_images/design/icon_cost.gif" alt="Search for Order" /></td>
		<td class="txt">Invoice ID #</td>
		<td class="search">
			<form action="index.php?page=orders_list_order_details" method="post" style="padding: 15px;">
				<input type="hidden" name="page" value="orders_list_order_details" />

				<input type='text' name='invoiceId' class="field" />
				<input type="submit" value="Get Order" />
			</form>
		</td>
	</tr>
	<tr class="{cycle values='row_color1,row_color2'}">
		<td class="img"><img src="admin_images/design/icon_cost.gif" alt="Invoice Transaction ID" /></td>
		<td class="txt">Invoice<br />Transaction ID</td>
		<td class="search">
			<form action="index.php?page=orders_list_order_details" method="post" style="padding: 15px;">
				<input type="hidden" name="page" value="orders_list_order_details" />

				<input type='text' name='transactionId' class="field" />
				<input type="submit" value="Get Order" />
			</form>
		</td>
	</tr>
	<tr class="{cycle values='row_color1,row_color2'}">
		<td class="img"><img src="admin_images/design/icon_template.png" alt="Search Templates and Text" /></td>
		<td class="txt">Template/Text</td>
		<td class="search">
			<form action="index.php" method="get" style="padding: 15px;">
				<input type="hidden" name="page" value="text_search" />
				<input type="hidden" name="show_first" value="1" />
				<input type="text" name="text" class="field" />
				<input type="submit" value="Find Text" style="font-size: 8pt;" />
			</form>
		</td>
	</tr>
	{if !$white_label}
		<tr>
			<td colspan="3" class="page_note" style="text-align: center;">
				<a href="https://geodesicsolutions.org">Geodesic Solutions</a> Searches
			</td>
		</tr>
		<tr class="{cycle values='row_color1,row_color2'}">
			<td class="img"><img src="admin_images/design/icon_manual.png" alt="Search the User Manual" /></td>
			<td class="txt"><a href="https://geodesicsolutions.org/wiki/">User Manual</a></td>
			<td class="search">
				<form action="https://geodesicsolutions.org/wiki/" method="get" id="userManualSearch" style="padding: 15px;">
					<input type="hidden" name="do" value="search" />
					<input type="text" name="id" class="field" />
					<input type="submit" value="Find Help &gt;&gt;" style="font-size: 8pt;" />
				</form>
			</td>
		</tr>
	{/if}
</table>

