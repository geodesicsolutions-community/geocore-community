{* 16.09.0-79-gb63e5d8 *}

{$admin_msgs}

<fieldset>
	<legend>Form Messages</legend>
	<div class="table-responsive">
		<table class="table table-hover table-striped table-bordered">
			<thead>
			<tr class="col_hdr_top">
				<td class="col_hdr_left">Message Name</td>
				<td class="col_hdr_left">Content Type</td>
				<td style="width: 10%;"></td>
			</tr>
			</thead>
			{foreach $messages_list as $message}
				<tr class="{cycle values='row_color1,row_color2'}">
					<td>{$message.message_name}</td>
					<td>{$message.content_type}</td>
					<td style="white-space: nowrap;" class="center">
						<a href="index.php?page=admin_messaging_form_edit&amp;message_id={$message.message_id}" class="btn btn-info btn-xs" style="margin:0;"><i class="fa fa-pencil"></i> Edit</a>
						<a href="index.php?page=admin_messaging_form_delete&amp;message_id={$message.message_id}&amp;auto_save=1" class="btn btn-danger btn-xs lightUpLink" style="margin:0;"><i class="fa fa-trash-o"></i> Delete</a>
					</td>
				</tr>
			{foreachelse}
				<tr>
					<td colspan="3">
						<div class="page_note_error">There are currently no form messages to display.</div>
					</td>
				</tr>
			{/foreach}
		</table>
	</div>
</fieldset>
<form action="index.php?page=admin_messaging_form_new" method="post" class='form-horizontal form-label-left'>
	<fieldset>
		<legend>Create a New Form Message</legend>
		<div class='x_content'>
			{include file="messaging/forms/edit_form.tpl"}
		</div>
	</fieldset>
</form>