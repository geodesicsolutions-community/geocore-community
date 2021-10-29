{* 7.4.6-5-g3153bd5 *}

<div class="closeBoxX"></div>
<div class="lightUpTitle">
	Copy Template Set
</div>
<div class="templateToolContents" style="width: 450px;">
	<form action='index.php?page=design_sets_copy' method='post'>
		<div class="page_note">This will make a copy of the files found in the 
			{if $t_set==merged}
				template sets you select below,
			{else}
				{$t_set} template set,
			{/if}
		to a new template set named what you enter in the field below.</div>
		{if $t_set==merged}
			<div class="page_note">If a file is found in more than one template set, the file from the template set "higher" in the list will be used.</div>
		{/if}
		
		<div class="{cycle values="row_color1,row_color2"}">
			<div class="leftColumn">
				Copy From
			</div>
			<div class="rightColumn">
				{if $t_set==merged}
					Following template sets merged:<br />
					{foreach from=$t_sets item=tset}
						<label><input type="checkbox" name="t_sets[]" value="{$tset}" /> {$tset}</label><br />
					{/foreach}
				{else}
					{$t_set}
				{/if}
				<input type="hidden" name="t_set" value="{$t_set}" />
			</div>
			<div class="clearColumn"></div>
		</div>
		<div class="{cycle values="row_color1,row_color2"}">
			<div class="leftColumn">
				<label for="new_t_set">New Template Set Name</label>
			</div>
			<div class="rightColumn">
				<input name="new_t_set" id="new_t_set" type="text" value="" />
			</div>
			<div class="clearColumn"></div>
		</div>
		<div class="{cycle values="row_color1,row_color2"}">
			<div class="leftColumn">
				What template types:
			</div>
			<div class="rightColumn">
				{foreach from=$t_types item=t_type}
					<label><input name="types[{$t_type}]" type="checkbox" value="1"{if $t_type=='main_page'||$t_type=='external'||$t_type=='smarty'} checked="checked"{/if} /> {$t_type}/</label><br />
				{/foreach}
			</div>
			<div class="clearColumn"></div>
		</div>
		<br />
		<div style="text-align: right;">
			<input type='submit' class="mini_button" name='auto_save' value='Copy Now' />
			<input type="button" value="Cancel" class="closeLightUpBox mini_cancel" />
		</div>
	</form>
</div>