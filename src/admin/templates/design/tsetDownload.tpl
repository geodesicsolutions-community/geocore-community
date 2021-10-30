{* 6.0.7-3-gce41f93 *}

<div class="closeBoxX"></div>
<div class="lightUpTitle">
	Download Template Set
</div>

<div class="templateToolContents" style="width: 450px;">
	<div class="page_note">This allows you to download and save your template set, for easy <strong>backup/restore</strong>
	purposes.  This also allows you to easily <strong>transfer the site's design to another site</strong>.</div>
	
	{if $canZip}
		<form action='index.php?page=design_sets_download' method='post'>
			<div class="{cycle values="row_color1,row_color2"}">
				<div class="leftColumn">
					Template Set to Download
				</div>
				<div class="rightColumn">
					{$t_set}
					<input type="hidden" name="t_set" value="{$t_set|escape}" />
				</div>
				<div class="clearColumn"></div>
			</div>
			<div class="{cycle values="row_color1,row_color2"}">
				<div class="leftColumn">
					Include what template types:
				</div>
				<div class="rightColumn">
					{foreach from=$t_types item=t_type}
						<label><input name="types[{$t_type}]" type="checkbox" value="1"{if $t_type=='main_page'||$t_type=='external'} checked="checked"{/if} /> {$t_type}/</label><br />
					{/foreach}
				</div>
				<div class="clearColumn"></div>
			</div>
			<br />
			<div style="text-align: right;">
				<input type='submit' name='auto_save' class="mini_button" value='Download' />
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