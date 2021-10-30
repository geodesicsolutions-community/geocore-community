{* 16.09.0-106-ge989d1f *}
<div class="table-responsive">
{if $special}
	<table class="table table-bordered table-hover table-striped">
		<thead>
			<tr>
				<th colspan="4" style="white-space:nowrap;">Special Permissions</th>
			</tr>
			<tr>
				<th style="padding-right:15px;">&nbsp;</th>
				<th style="width:20px;">View</th>
				<th style="width:20px;">Edit</th>
			</tr>
		</thead>
		<tbody>
			{if $no_pages}
				<tr class="medium_font_light row_color_red">
					<td colspan="4" style="text-align:left; padding-left: 5px; white-space:nowrap;">Permissions for Pages Not Found</td>
				</tr>
				{foreach from=$no_pages item="index"}
				<tr class="small_font">
					<td style="padding-left: 20px;">{$index}</td><td  style="text-align: center;"><input type="checkbox" name="display[{$index}]" onclick="javascript: updateChecks(this)" class="displayBox" value="1"{if $display_permissions.$index} checked="checked"{/if} /></td><td  style="text-align: center;"><input type="checkbox" class="updateBox" name="update[{$index}]"{if $update_permissions.$index} checked="checked"{/if} /></td>
				</tr>
				{/foreach}
			{/if}
			{foreach from=$special_pages item="title" key="index"}
				<tr class="small_font">
					<td style="padding-left: 20px;">{$title}</td>
					<td colspan="2" style="text-align: center;">
						<input type="checkbox" name="display[{$index}]" value="1"{if $display_permissions.$index} checked="checked"{/if} />
					</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
{/if}
<br />
<table class="table table-bordered table-hover table-striped">
	<thead>
		<tr>
			<th colspan="4">Page Access Permissions</th>
		</tr>
		<tr>
			<th style="padding-right:15px; white-space:nowrap;">
				Section / Page Name
			</th>
			<th style="white-space:nowrap;">
				View
			</th>
			<th style="white-space:nowrap;">
				Edit
			</th>
		</tr>
		<tr>
			<th>&nbsp;</th>
			<th style="text-align: center;" class="medium_font_light row_color_red">
				<input type="checkbox" name="dummyDisplayAll" class="displayBox" onclick="javascript:checkAll(this)" />
			</th>
			<th style="text-align: center;" class="medium_font_light row_color_red">
				<input type="checkbox" name="dummyUpdateAll" class="updateBox" onclick="javascript:checkAll(this)" />
			</th>
		</tr>
	</thead>
	<tbody>
		{foreach from=$top_categories item="top_category"}

			

			{foreach from=$top_category.children_categories item="category"}
				{include file="category"}
			{/foreach}
		{/foreach}
	</tbody>
</table>
<div class="center"><input type="submit" name="auto_save" value="Save Changes" /></div>
<script type="text/javascript" src="../addons/multi_admin/multi_admin.js"></script>