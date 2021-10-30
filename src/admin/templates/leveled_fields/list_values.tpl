{* 16.09.0-87-g69e04de *}

{$adminMsgs}

<div class='page-title1'>
Multi-Level Field Group: <span class="color-primary-two">{$leveled_field_label}</span>
</div>

<fieldset>
	<legend>Multi-Level Field Values</legend>
	<div>
		{include file='leveled_fields/levelInfo.tpl'}
		
		<form action="index.php?leveled_field={$leveled_field}&amp;parent={$parent}&amp;p={$page}" method="post" id="massForm">
			<table class='table table-hover table-striped table-bordered'>
				<thead>
					<tr class="col_hdr_top">
						<th style="width: 21px;"></th>
						<th>Value (ID#)</th>
						<th style="width: 60px;">Listings</th>
						<th style="width: 60px;">Enabled?</th>
						<th>Display Order</th>
						<th style="width: 140px;"></th>
					</tr>
					<tr class="col_ftr">
						<td><input type="checkbox" id="checkAllValues" /></td>
						<td colspan="5" style="padding-left: 20px;">With Selected:
							<a href="#" class="btn btn-dark btn-xs massEditButton"><i class="fa fa-edit"></i> Mass Edit</a>
							<a href="#" class="btn btn-dark btn-xs copyButton"><i class="fa fa-copy"></i> Copy</a>
							<a href="#" class="btn btn-dark btn-xs moveButton"><i class="fa fa-arrows"></i> Move</a>
							<a href="#" class="btn btn-danger btn-xs massDeleteButton"><i class="fa fa-trash-o"></i> Delete</a>
							{$pagination}
						</td>
					</tr>
				</thead>
				<tfoot>
					<tr class="col_ftr">
						<td colspan="6" style="padding-left: 20px;">
							<i class="fa fa-sign-out" style="font-size: 1.4em;"></i>
							With Selected:
							<a href="#" class="btn btn-dark btn-xs massEditButton"><i class="fa fa-edit"></i> Mass Edit</a>
							<a href="#" class="btn btn-dark btn-xs copyButton"><i class="fa fa-copy"></i> Copy</a>
							<a href="#" class="btn btn-dark btn-xs moveButton"><i class="fa fa-arrows"></i> Move</a>
							<a href="#" class="btn btn-danger btn-xs massDeleteButton"><i class="fa fa-trash-o"></i> Delete</a>
							{$pagination}
						</td>
					</tr>
				</tfoot>
				<tbody>
					{if $parent}
						{foreach $parents as $value}
							{if $value.enabled=='no'}
								<tr id="row_{$value.id}">
									<td class="center"></td>
									<td colspan="5">
										<div class="disabledSection">
											<strong style="color: red;">Warning:</strong> Parent Value <strong class="text_blue">{$value.name}</strong>  is <strong>Disabled</strong>!  Sub-Values below are not currently usable on the site.
										</div>
									</td>
								</tr>
							{/if}
						{/foreach}
					{/if}
					{if !$values}
						<tr><td colspan="10"><p class="page_note_error">No values were found at this level!  You can create some new values at this level using the "Add Value" or "Bulk Add" buttons at the bottom...</p></td></tr>
					{else}
						{foreach $values as $value}
							<tr class="{cycle values='row_color1,row_color2'}" id="row_{$value.id}">
								<td class="center"><input type="checkbox" name="values[]" class="valueCheckbox" value="{$value.id}" /></td>
								<td{if $value.enabled=='no'} class="disabled"{/if}>
									<a href="index.php?page=leveled_field_values&amp;leveled_field={$leveled_field}&amp;parent={$value.id}">{$value.name|fromDB} ({$value.id})</a>
								</td>
								<td class="center">{$value.listing_count}</td>
								<td class="center">
									{include file='leveled_fields/enabled.tpl'}
								</td>
								<td class="center">{$value.display_order}</td>
								<td class="center">
									<a href="index.php?page=leveled_field_values&amp;leveled_field={$leveled_field}&amp;parent={$value.id}" class="btn btn-success btn-xs"><i class="fa fa-folder-open"></i> Enter</a>
									<a href="index.php?page=leveled_field_value_edit&amp;leveled_field={$leveled_field}&amp;value={$value.id}&amp;p={$page}" class="btn btn-info btn-xs lightUpLink"><i class="fa fa-pencil"></i> Edit</a>
								</td>
							</tr>
						{/foreach}
					{/if}
				</tbody>
			</table>
		</form>
		<br />
		<div class="center">
			<a href="index.php?page=leveled_field_value_create&amp;leveled_field={$leveled_field}&amp;parent={$parent}" class="btn btn-xs btn-success lightUpLink"><i class="fa fa-plus-circle"></i> Add Value</a>
			<a href="index.php?page=leveled_field_value_create_bulk&amp;leveled_field={$leveled_field}&amp;parent={$parent}" class="btn btn-xs btn-success lightUpLink"><i class="fa fa-truck"></i> Bulk Add</a>
		</div>
	</div>
</fieldset>

<div class="center">
	<br /><br />
	<a href="index.php?page=leveled_fields" class="mini_button">View All Multi-Level Field Groups</a>
</div>