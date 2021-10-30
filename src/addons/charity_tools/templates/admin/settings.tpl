{* 16.09.0-105-ga458f5f *}

{$adminMessages}

<form action="" method="post" class='form-horizontal'>

	<fieldset>
		<legend>Good Neighbor Badge</legend>
		<div class='x_content'>
		
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>Enable Good Neighbor Badge</label>
				<div class="col-xs-12 col-sm-6"><input type="checkbox" value="1" name="settings[use_neighborly]" {if $use_neighborly}checked="checked"{/if} /></div>
			</div>
			
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>Good Neighbor Badge Image: </label>
				<div class="col-xs-12 col-sm-6">
					geo_templates/[Template Set]/external/images/addon/charity_tools/<input type="text" name="settings[neighborly_image]" value="{$neighborly_image}" class='form-control' /> 
					{if $neighborly_image}<img src="../{$neighborly_preview}" alt="" />{/if}
				</div>
			</div>
			
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>Good Neighbor Badge expires after </label>
				<div class="col-xs-12 col-sm-6"><input type="text" value="{$neighborly_duration}" name="settings[neighborly_duration]" size="2" /> months</div>
			</div>
		
		</div>
	</fieldset>
	
	<fieldset>
		<legend>Charitable Badge Global Override Image</legend>
		<div class='x_content'>
			<div class="form-group">
				<label class='control-label col-xs-12 col-sm-5'>Image Path:</label>
				<div class="col-xs-12 col-sm-6">geo_templates/[Template Set]/external/images/addon/charity_tools/<input type="text" name="settings[charitable_override]" value="{$charitable_override}" class='form-control' /> </div>
			</div>
			{if $override_image}
				<div class="form-group">
					<label class='control-label col-xs-12 col-sm-5'>Preview:</label>
					<div class="col-xs-12 col-sm-6 vertical-form-fix">
						<img src="../{$override_image}" alt="" /><br />
						The above image will be shown instead of a specific charitable badge image on ALL listings that otherwise have a charitable badge showing.
					</div>
				</div>
			{/if}
		</div>
	</fieldset>
	
	<div class="center"><input type="submit" value="Save" class="button" name="auto_save" /></div>
	
	{if $charitables}
		<fieldset>
			<legend>Charitable Badges</legend>
			<div class='table-responsive x_content'>
			
				<table class='table table-hover table-striped table-bordered'>
					<thead>
						<tr>
							<th>Charity Name</th>
							<th>Badge Image</th>
							<th>Region <strong>-or-</strong> Zipcode</th>
							<th>Description</th>
							<th>Delete</th>
						</tr>
					</thead>
					<tbody>
						{foreach $charitables as $id => $c}
							<tr>
								<td style="text-align: center;">{$c.name}</td>
								<td style="text-align: center;"><img src="../{$c.image}" alt="{$c.name}" /><br />{$c.image}</td>
								<td style="text-align: center;">{if $c.region}{$c.region}{elseif $c.zipcode}{$c.zipcode}{else}none{/if}</td>
								<td style="text-align: center;">{$c.description}</td>
								<td style="text-align: center;"><a href="index.php?page=addon_charity_tools_settings&deleteCharitable={$id}&auto_save=1" class="btn btn-xs btn-danger lightUpLink"><i class="fa fa-trash-o"></i> Delete</a></td>
							</tr>
						{/foreach}
					</tbody>		
				</table>
			</div>
		</fieldset>
	{/if}
	
	<fieldset>
		<legend>Add New Charitable Badge</legend>
		<div class='table-responsive x_content'>
			<table class='table table-hover table-striped table-bordered'>
				<thead>
					<tr>
						<th>Charity Name</th>
						<th>Badge Image</th>
						<th>Region <strong>-or-</strong> Zipcode</th>
						<th>Description</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td style="text-align: center;"><input type="text" name="nc[name]" class="form-control" /></td>
						<td style="text-align: center;">geo_templates/[Template Set]/external/images/addon/charity_tools/<input class="form-control" type="text" name="nc[image]" value="charitable.png" /></td>
						<td>
							{$newRegion}<br /><br />
							<strong>or Zipcode: </strong><input type="text" class="form-control" size="5" name="nc[zipcode]" />
						</td>
						<td style="text-align: center;"><textarea name="nc[description]" rows="4" cols="25" class="form-control"></textarea></td>
					</tr>
				</tbody>
			</table>
		</div>
	</fieldset>
	
	
<div class="center"><input type="submit" value="Add New" class="btn btn-info" name="auto_save" /></div>
</form>