{* 16.09.0-96-gf3bd8a1 *}
{$admin_msgs}
<form action="" method="post" class="form-horizontal form-label-left">
	<fieldset>
		<legend>Profile Picture Dimensions</legend>
		<div class='x_content'>

			 <div class="form-group">
			  <label class="control-label col-md-5 col-sm-5 col-xs-12">Uploader Viewport: 
			  <br><span class="small_font">(profile pics will be cropped to this size)</span></label>
				<div class="col-md-6 col-sm-6 col-xs-12">
				  <div class="input-group">
					<div class="input-group-addon">Width:</div>
					<input type="number" name="viewport_width" class="form-control col-md-7 col-xs-12" value="{$viewport_width}" step="1" />
					<div class="input-group-addon">pixels</div>
				  </div>
				  <div class="input-group">
					<div class="input-group-addon">Height:</div>
					<input type="number" name="viewport_height" class="form-control col-md-7 col-xs-12" value="{$viewport_height}" step="1" />
					<div class="input-group-addon">pixels</div>
				  </div>
				</div>
			  </div> 

			 <div class="form-group">
			  <label class="control-label col-md-5 col-sm-5 col-xs-12">Uploader Boundaries: 
			  <br><span class="small_font">(recommended: make these bigger than the above set)</span></label>
				<div class="col-md-6 col-sm-6 col-xs-12">
				  <div class="input-group">
					<div class="input-group-addon">Width:</div>
					<input type="number" name="boundary_width" class="form-control col-md-7 col-xs-12" value="{$boundary_width}" step="1" />
					<div class="input-group-addon">pixels</div>
				  </div>
				  <div class="input-group">
					<div class="input-group-addon">Height:</div>
					<input type="number" name="boundary_height" class="form-control col-md-7 col-xs-12" value="{$boundary_height}" step="1" />
					<div class="input-group-addon">pixels</div>
				  </div>
				</div>
			  </div>			

			<div class="center"><input type="submit" value="Save" name="auto_save" /></div>
		</div>
	</fieldset>
</form>

<fieldset>
	<legend>Display Options</legend>
	<div>
		<p>Display profile pics with these template tags:</p>
		<p>{literal}
			<strong>{addon author='geo_addons' addon='profile_pics' tag='show_pic'}</strong> -- Show the profile pic of the current user<br />
			<strong>{addon author='geo_addons' addon='profile_pics' tag='show_pic' user='rob'}</strong> -- Show the profile pic of username, i.e. "rob" (also accepts User ID#)<br />
			<strong>{listing addon='profile_pics' tag='show_pic'}</strong> -- Show the profile pic of a listing's seller
		{/literal}</p>
		<p>Any of the above will accept additional parameters of <strong>width</strong> and/or <strong>height</strong>, given in pixel values
	</div>
</fieldset>

<div class='clearColumn'></div>