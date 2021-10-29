{* 16.09.0-79-gb63e5d8 *}

{$admin_msgs}

<fieldset>
	<legend>Region Levels</legend>
	<div>
		<form action="index.php?page=region_levels" method="post">
			<div class="table-responsive">
			<table class="table table-hover table-striped table-bordered">
				<thead>
					<tr class="col_hdr_top">
						<th>Level {$tooltips.level}</th>
						<th>Sample {$tooltips.sample}</th>
						<th>Region Type {$tooltips.type}</th>
						<th style="width: 170px;">Always Show {$tooltips.always_show}</th>
						<th>Labeled? {$tooltips.labeled}</th>
						{foreach $languages as $lang}
							<th>{$lang.language} Label</th>
						{/foreach}
					</tr>
				</thead>
				<tbody>
					{foreach $levels as $level}
						<tr class="{cycle values='row_color1,row_color2'}">
							<td><strong class="color-primary-one">Level {$level.level}</strong></td>
							<td>{$level.sample}</td>
							<td>
								<select name="region_type[{$level.level}]">
									<option value="other"{if $level.region_type=='other'} selected="selected"{/if}>Other</option>
									<option value="country"{if $level.region_type=='country'} selected="selected"{/if}>Country</option>
									<option value="state/province"{if $level.region_type=='state/province'} selected="selected"{/if}>State or Province</option>
									<option value="city"{if $level.region_type=='city'} selected="selected"{/if}>City</option>
								</select>
							</td>
							<td class="center">
								{if $level@first}
									-
								{else}
									<input type="checkbox" name="always_show[{$level.level}]" value="yes" class="always_show_checkbox"{if $level.always_show=='yes'} checked="checked"{/if} />
								{/if}
							</td>
							<td class="center">
								<input type="checkbox" name="use_label[{$level.level}]" value="yes" class="use_label_checkbox"{if $level.use_label=='yes'} checked="checked"{/if} />
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
			<br />
			<div class="center medium_font" style="margin: 10px;">
				<input type="checkbox" name="region_select_build_down" value="1" {if $build_down}checked="checked" {/if}/> Build region selectors down instead of across
			</div>
			<div class="center">
				<input type="submit" name="auto_save" value="Save" />
			</div>
		</form>
	</div>
</fieldset>
<div class="clearColumn"></div>