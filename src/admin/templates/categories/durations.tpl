{* 16.09.0-79-gb63e5d8 *}

{$admin_msgs}

<br />

<div class="breadcrumbBorder">
	<ul id="breadcrumb">
		<li><a href="index.php?mc=categories&page=category_config">Main</a></li>
		{foreach $category_tree as $cat}
			<li{if $cat@last} class="current2"{/if}>
				<a href="index.php?mc=categories&page=category_config&parent={$cat.category_id}">{$cat.category_name}</a>
			</li>
		{/foreach}
	</ul>
</div>


<form action="index.php?page=category_durations&amp;c={$category_id}" method="post">
	<fieldset>
		<legend>Category Listing Durations</legend>
		<div class="table-responsive">
			<table class="table table-hover table-striped table-bordered">
				<thead>
					<tr class="col_hdr_top">
						<th>Listing Duration (Displayed)</th>
						<th>Listing Duration (# Days)</th>
						<th></th>
					</tr>
				</thead>
				{foreach $lengths as $length}
					<tr class="{cycle values='row_color1,row_color2'}">
						<td>{$length.display_length_of_ad}</td>
						<td>{$length.length_of_ad}</td>
						<td>
							<a href="index.php?page=category_durations_delete&amp;c={$category_id}&amp;length_id={$length.length_id}&amp;auto_save=1" class="mini_cancel lightUpLink">Delete</a>
						</td>
					</tr>
				{/foreach}
				<tr class="col_ftr">
					<th>Displayed: <input type="text" name="display_length_of_ad" /></th>
					<th>Days: <input type="text" name="length_of_ad" /></th>
					<th><input type="submit" name="auto_save" value="Add Duration" /></th>
				</tr>
			</table>
		</div>
	</fieldset>
</form>

<div style='padding: 5px;'><a href="index.php?page=category_config&amp;parent={$info.parent_id}" class="back_to">
<i class='fa fa-backward'> </i> Back to Categories</a></div>