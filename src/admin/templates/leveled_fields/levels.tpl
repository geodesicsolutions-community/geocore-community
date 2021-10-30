{* 16.09.0-87-g69e04de *}

{$admin_msgs}

<div class='page-title1'>
Multi-Level Field Group: <span class="color-primary-two">{$leveled_field_label}</span>
</div>

<fieldset>
	<legend>{$leveled_field_label} Levels</legend>
	<div>
		<form action="index.php?page=leveled_field_levels&amp;leveled_field={$leveled_field}" method="post" class="form-horizontal">
			<div class="table-responsive">
				<table class="table table-hover table-bordered table-striped">
					<thead>
						<tr class="col_hdr_top">
							<th>Level {$tooltips.level}</th>
							<th>Sample Value {$tooltips.sample}</th>
							<th style="width: 170px;">Always Show {$tooltips.always_show}</th>
							{foreach $languages as $lang}
								<th>{$lang.language} Label</th>
							{/foreach}
						</tr>
					</thead>
					<tbody>
						{foreach $levels as $level}
							<tr>
								<td>Level <strong class="text_blue">{$level.level}</strong></td>
								<td>
									{if $level.sample}
										{$level.sample}
									{else}
										N/A (Add one in <a href="index.php?page=leveled_field_values&amp;leveled_field={$leveled_field}">values</a>)
									{/if}
								</td>
								<td class="center">
									{if $level@first}
										-
									{else}
										<input type="checkbox" name="always_show[{$level.level}]" value="yes" class="always_show_checkbox"{if $level.always_show=='yes'} checked="checked"{/if} />
									{/if}
								</td>
								
								{foreach $languages as $lang}
									<td class="center">
										<input type="text" name="label[{$level.level}][{$lang.language_id}]" value="{$level.labels[$lang.language_id]}" />
									</td>
								{/foreach}
							</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
			<p class="page_note"><strong>Need another level?</strong>  If 
			you want to add another level, start by <a href="index.php?page=leveled_field_values&amp;leveled_field={$leveled_field}">adding
			a value</a> for that level by editing the values.  When there is at least
			one value in a level, it will show the level on this page and you will
			be able to set a label.</p>
			<div class="center">
				<input type="submit" name="auto_save" value="Save" />
			</div>
		</form>
	</div>
</fieldset>
<div class="center">
	<br /><br />
	<a href="index.php?page=leveled_field_values&amp;leveled_field={$leveled_field}" class="mini_button">View / Edit Values for {$leveled_field_label}</a>
	<br /><br />
	<a href="index.php?page=leveled_fields" class="mini_button">View All Multi-Level Field Groups</a>
</div>