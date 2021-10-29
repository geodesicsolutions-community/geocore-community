{* 6.0.7-3-gce41f93 *}

<div class="closeBoxX"></div>
<div class="lightUpTitle">
	Upload Template Set
</div>

<div class="templateToolContents" style="width: 450px;">
	{if $canZip}
		<form enctype="multipart/form-data" action='index.php?page=design_sets_upload' method='post'>
			<div class="{cycle values="row_color1,row_color2"}">
				<div class="leftColumn">
					<label for="ot_set">Template Set zip file</label>
				</div>
				<div class="rightColumn">
					<input type="file" name="zipfile" />
				</div>
				<div class="clearColumn"></div>
			</div>
			<div class="{cycle values="row_color1,row_color2"}">
				<div class="leftColumn">
					If template set exists?
				</div>
				<div class="rightColumn">
					<label><input name="overwrite" type="radio" value="rename" checked="checked" /> Rename uploaded set</label><br />
					<label><input name="overwrite" type="radio" value="overwrite" /> Overwrite existing set</label>
				</div>
				<div class="clearColumn"></div>
			</div>
			<div class="{cycle values="row_color1,row_color2"}">
				<div class="leftColumn">
					Use set
				</div>
				<div class="rightColumn">
					<input name="useIt" type="checkbox" value="1" />
				</div>
				<div class="clearColumn"></div>
			</div>
			<br />
			<div style="text-align: right;">
				<input type='submit' name='auto_save' class="mini_button" value='Upload' />
				<input type="button" value="Cancel" class="closeLightUpBox mini_cancel" />
			</div>
		</form>
	{else}
		<p>
			<strong>Not capable:</strong> This server does not have zip support, which is required to download a template set.
			<br /><br />
			To enable <em>zip support</em>, see the notes on <a href="http://php.net/manual/en/zip.installation.php">php.net Zip installation</a>, under the <strong>PHP 5.2.0 and later</strong> section,
			specific to your server environment.
		</p>
	{/if}
</div>