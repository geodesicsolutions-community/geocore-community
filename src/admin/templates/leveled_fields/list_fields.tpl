{* 16.09.0-87-g69e04de *}

{$adminMsgs}

<fieldset>
	<legend>Multi-Level Field Groups</legend>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-bordered">
			<thead>
				<tr class="col_hdr_top">
					<th>Group Admin Label (ID#)</th>
					<th style="white-space: nowrap;"># of Levels</th>
					<th style="white-space: nowrap;">Total # Values</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				{foreach $fields as $field}
					<tr>
						<td style='text-align: center;'>
							{$field.label} ({$field.id})
							<a href="index.php?page=leveled_field_edit&amp;leveled_field={$field.id}" class="btn btn-info btn-xs lightUpLink"><i class='fa fa-pencil'></i> Edit</a>
						</td>
						<td style='text-align: center;'>
							{$field.max_level}
							<a href="index.php?page=leveled_field_levels&amp;leveled_field={$field.id}" class="btn btn-xs btn-primary">Show Levels</a>
						</td>
						<td style='text-align: center;'>
							{$field.value_count}
							<a href="index.php?page=leveled_field_values&amp;leveled_field={$field.id}" class="btn btn-xs btn-primary">Show Values</a>
						</td>
						<td style='text-align: center;'>
							<a href="index.php?page=leveled_fields_delete&amp;leveled_field={$field.id}" class="btn btn-danger btn-xs lightUpLink"><i class='fa fa-trash-o'></i> Delete</a>
						</td>
					</tr>
				{foreachelse}
					<tr><td colspan="4">
						<p class="page_note_error">There were no leveled fields found!  You can add one below.</p>
					</td></tr>
				{/foreach}
			</tbody>
		</table>
	</div>
</fieldset>

<fieldset>
	<legend>Add New Multi-Level Field Group</legend>
	<div>
		<form method="post" action="index.php?page=leveled_fields_add" class="form-horizontal">
			<div class="form-group">
				<label class="control-label col-xs-12 col-sm-5">
					Group Label (Only Used in Admin Panel)
				</label>
				<div class="col-xs-12 col-sm-6">
					<input type="text" name="new_label" placeholder="e.g. Vehicle Type" class="form-control" />
				</div>
			</div>
			<div class="center">
				<input type="submit" name="auto_save" value="Create New Group" />
			</div>
		</form>
	</div>
</fieldset>