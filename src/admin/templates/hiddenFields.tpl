{* 7.0.3-318-gd7dfd36 *}
{$adminMsgs}
<fieldset>
	<legend>Hide Fields</legend>
	<div>
		<form action="" method="post">
			<p class="page_note">Check the boxes for the fields you wish to hide on the <strong>listing details pages</strong> from any users that are <strong>not logged in</strong>.</p>
			<div class="col_hdr_top">
				<div class="leftColumn">Hide Tag Contents? <input type="checkbox" id="hiddenCheckAll" /></div>
				<div class="rightColumn">Tag Used in Listing Details Page</div>
				<div class="clearColumn"></div>
			</div>
			{foreach $fields as $fieldType => $f2}
				<div class="col_hdr">{$fieldType|replace:'_':' '|capitalize}</div>
				{foreach $f2 as $field => $data}
					<div class="{cycle values="row_color1,row_color2"}">
						<div class="leftColumn"><input type="checkbox" id="{$field}" class="hiddenFields" name="hiddenFields[{$field}]" value="1" {if $hiddenFields && $hiddenFields.$field}checked="checked"{/if} /></div>
						<div class="rightColumn">
							<label for="{$field}">
								{if $data.type=='tag'}
									{ldelim}listing tag='{$field}'{rdelim}
								{elseif $data.type=='field'}
									{ldelim}listing field='{$field}'{rdelim}
								{else}
									{ldelim}${$field}{rdelim}
								{/if}
								<br /><span class="small_font">
									{if $data.desc}
										{$data.desc}
									{else}
										{$field|replace:'_':' '|capitalize}
									{/if}
								</span>
							</label>
						</div>
						
						<div class="clearColumn"></div>
					</div>
				{/foreach}
			{/foreach}
			<div style="text-align: center;"><input type="submit" name="auto_save" value="Save Changes" /></div>
		</form>
	</div>
</fieldset>
