{* 7.2.1-6-g63ad625 *}

{$admin_msgs}

<style>

ul.combineSteps {
	margin: 0;
	padding: 0;
}
ul.combineSteps li {
	list-style: none;
	border: 1px solid black;
	border-radius: 10px;
	margin: 3px;
	display: inline-block;
	font-size: 14px;
	vertical-align: middle;
}

ul.combineSteps li:hover {
	background-color: #eee;
}

ul.combineSteps li label {
	display: block;
	padding: 5px 10px 5px 5px;
}

</style>

<script>
jQuery(function () {
	//watch check-boxes and show/hide the "force preview" only when one of the options
	//that would let the preview show is checked...
	jQuery('.previewOption').click(function () {
		var previewOptions = jQuery(this).closest('fieldset').find('.previewOption');
		var showForce = false;
		jQuery.each(previewOptions, function () {
			if (jQuery(this).prop('checked')) {
				showForce = true;
			}
		});
		
		if (showForce) {
			jQuery(this).closest('fieldset').find('.force_preview_box').show('fast');
		} else {
			jQuery(this).closest('fieldset').find('.force_preview_box').hide('fast');
		}
	});
	
	//also show/hide the forcepreview box...
	jQuery('.force_preview_box').each(function () {
		if (!jQuery(this).closest('fieldset').find('.previewOption:checked').length) {
			//hide me!
			jQuery(this).hide('fast');
		}
	});
});
</script>

<h2>Combine Listing Placement Steps</h2>

<form action="" method="post">
	{foreach $types as $type => $type_info}
		<fieldset>
			<legend>Settings for {$type_info.title}</legend>
			<div>
				<div class="{cycle values='row_color1,row_color2'}">
					<div class="leftColumn"><input type="checkbox" name="skip_cart[{$type}]" class="previewOption" value="1"{if $type_info.skip_cart} checked="checked"{/if} /></div>
					<div class="rightColumn">
						Skip Cart/Checkout if Free?
					</div>
					<div class="clearColumn"></div>
				</div>
				<div class="{cycle values='row_color1,row_color2'}">
					<div class="leftColumn"><input type="checkbox" name="always_preview[{$type}]" class="previewOption" value="1"{if $type_info.always_preview} checked="checked"{/if} /></div>
					<div class="rightColumn">
						Always Show Preview on "last step"?
					</div>
					<div class="clearColumn"></div>
				</div>
				
				<div class="{cycle values='row_color1,row_color2'} force_preview_box">
					<div class="leftColumn"><input type="checkbox" name="force_preview[{$type}]" value="1"{if $type_info.force_preview} checked="checked"{/if} /></div>
					<div class="rightColumn">
						Force Preview to continue?<br />
						<span class="small_font">(When preview button is displayed)</span>
					</div>
					<div class="clearColumn"></div>
				</div>
				<div class="{cycle values='row_color1,row_color2'}">
					<div class="leftColumn">Should it Combine Steps for <strong class="text_blue">{$type_info.title}</strong>?</div>
					<div class="rightColumn">
						<label>
							<input type="radio" name="combine[{$type}]" class="combineSetting" value="none"{if $type_info.combine=='none'} checked="checked"{/if} />
							Do not combine
						</label>
						<br />
						<label>
							<input type="radio" name="combine[{$type}]" class="combineSetting" value="all"{if $type_info.combine=='all'} checked="checked"{/if} />
							Combine ALL Steps (Single Page Listing Process)
						</label>
						<br />
						<label>
							<input type="radio" name="combine[{$type}]" class="combineSetting" value="selected"{if $type_info.combine=='selected'} checked="checked"{/if} />
							Combine selected steps
						</label>
						<div class="selectedSteps" style="display: none;">
							<p class="page_note" style="display: inline-block;">
								<strong>Note:</strong> Selected steps must be <em>sequential</em>.
							</p>
							
							<ul class="combineSteps">
								{foreach $type_info.steps as $step => $step_label}
									<li>
										<label>
											<input type="checkbox" name="combined[{$type}][{$step}]" value="{$step}" class="combined_checkbox"
												{if $type_info.combined.$step}checked="checked"{/if} /> {$step_label}
										</label>
									</li>	
								{/foreach}
							</ul>
						</div>
					</div>
					<div class="clearColumn"></div>
				</div>
			</div>
		</fieldset>
	{/foreach}
	<div class="center">
		<br /><br />
		<input type="submit" name="auto_save" value="Save Changes" />
	</div>
</form>