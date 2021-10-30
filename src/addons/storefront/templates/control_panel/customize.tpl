{* 7.6.3-149-g881827a *}

{include file='control_panel/header.tpl'}
	{* header.tpl starts a div for main column *}
	<form method="post" enctype="multipart/form-data" action="{$classifieds_file_name}?a=ap&amp;addon=storefront&amp;page=control_panel&amp;action=update&amp;action_type=customize">
		<div class="content_box">
			<h3 class="subtitle">{$msgs.usercp_custom_logo_header}</h3>
			<div class="{cycle values='row_odd,row_even'}">
				<label for="storefrontLogo" class="field_label">
					{$msgs.usercp_custom_logo_upload}
				</label>
				<input class='file' type='file' name='logo' id='storefrontLogo' size='40' class="field" />
			</div>
			
			<h1 class="subtitle">{$msgs.usercp_custom_logo_currentheader}</h1>
			<div style="clear: both;">
				<img class="store-logo" style="top:0; margin:5px; max-width: 85%;" src="addons/storefront/images/{if $current_logo}{$current_logo}{else}addon_storefront_logo.gif{/if}" alt="Your Logo" {if $logo_width and $logo_height} style="width: {$logo_width}px; height: {$logo_height}px; max-width:100%;"{/if} />
			</div>
			<h1 class="subtitle">{$msgs.usercp_custom_logo_size_header}</h1>
			<div class="{cycle values='row_odd,row_even'}">
				<label class="field_label">
					{$msgs.usercp_custom_logo_size_mainlabel}
				</label>
				<div style="white-space: nowrap; display: inline;">
					<strong class="text_highlight">{$msgs.usercp_custom_logo_size_width}</strong> <input type="text" size="4" maxlength="4" name="data[logo_width]" value="{$logo_width}" class="field" /> {$msgs.usercp_custom_logo_size_px} &nbsp;&nbsp;
					<strong class="text_highlight">{$msgs.usercp_custom_logo_size_height}</strong> <input type="text" size="4" maxlength="4" name="data[logo_height]" value="{$logo_height}" class="field" /> {$msgs.usercp_custom_logo_size_px}
				</div>
			</div>
			
			<div class="{cycle values='row_odd,row_even'}">
				<label class="field_label">
					{$msgs.usercp_custom_logo_size_listlabel}
				</label>
				<div style="white-space: nowrap; display: inline;">
					<strong class="text_highlight">{$msgs.usercp_custom_logo_size_width}</strong> <input type="text" size="4" maxlength="4" name="data[logo_list_width]" value="{$logo_list_width}" class="field" /> {$msgs.usercp_custom_logo_size_px} &nbsp;&nbsp;
					<strong class="text_highlight">{$msgs.usercp_custom_logo_size_height}</strong> <input type="text" size="4" maxlength="4" name="data[logo_list_height]" value="{$logo_list_height}" class="field" /> {$msgs.usercp_custom_logo_size_px}  
				</div>
			</div>
		</div>

		<div class="content_box">
			<h1 class="title">{$msgs.usercp_custom_settings_header}</h1>
			
			<div id="name_result" class="center main_text"></div>
			<div class="{cycle values='row_odd,row_even'}">
				<label class="field_label">
					{$msgs.usercp_custom_settings_name_label}
				</label>
				<input onchange="CheckStoreName(this.value)" type="text" id="storefront_name" name="data[storefront_name]" value="{$storefront_name|escape}" size="30" maxlength="50" class="field" />
				<input type="button" id="btn_check" value="{$msgs.usercp_custom_settings_name_check}" onclick="CheckStoreName(jQuery('#storefront_name').val())" class="button" />
			</div>
			<script type="text/javascript">
				var CheckStoreName = function (name) {
							
					jQuery('#name_result').html("{$msgs.usercp_custom_settings_name_pending}");
					jQuery('#btn_submit').prop('disabled',true);
					jQuery('#btn_check').prop('disabled',true);
				
					jQuery.post('{$classifieds_file_name}?a=ap&addon=storefront&page=check_name_ajax', { name_to_check: name }, function( returned ) {
						if(returned == 'INVALID') {
							resultText = "<div class='field_error_box'>{$msgs.usercp_custom_settings_name_invalid}</div>";
						} else if (returned == 'IN_USE') {
							resultText = "<div class='field_error_box'>{$msgs.usercp_custom_settings_name_taken}</div>";
						} else if (returned == 'OK') {
							resultText = "<div class='success_box'>{$msgs.usercp_custom_settings_name_good}</div>";
							jQuery('#btn_submit').prop('disabled',false);
						} else {
							//probably blank...
							resultText = "";
							jQuery('#btn_submit').prop('disabled',false);
						}
						jQuery('#name_result').html(resultText);
						jQuery('#btn_check').prop('disabled',false);
					}, 'html');
				
				}
			</script>

			<div class="{cycle values='row_odd,row_even'}">
				<label class="field_label">{$msgs.usercp_custom_settings_welcomenoteheader}</label>	<a href="#" onclick="gjWysiwyg.toggleTinyEditors(); return false;">{$messages.add_remove_wysiwyg}</a>
			</div>
			<div class="{cycle values='row_odd,row_even'}">
				<textarea class='editor field' name='data[welcome_note]' id='storefrontNote' cols='' rows='' style="width: 95%; height: 200px;">{$welcome_message|escape}</textarea>
			</div>
		</div>

		{if count($template_choices) > 1}
			<div class="content_box">
				<h2 class="title">{$msgs.usercp_custom_settings_tpl_header}</h2>
				<div class="{cycle values='row_odd,row_even'}">
					<label class="field_label" for="data[storefrontTemplate]">{$msgs.usercp_custom_settings_tpl_label}</label>
					<select name="data[storefrontTemplate]" id="data[storefrontTemplate]" class="field">
						{foreach from=$template_choices item=tpl}
							<option value="{$tpl.template_id}"{if $tpl.template_id==$current_template} selected="selected"{/if}>{$tpl.name|fromDB}</option>
						{/foreach}
					</select>  
				</div>
			</div>
		{else}
			{*only one template choice -- assign it automatically*}
			<div><input type="hidden" name="data[storefrontTemplate]" value="{$single_template.template_id}" /></div>
		{/if}
		
		<div class="center">
			<input type="submit" value="{$msgs.usercp_custom_settings_save}" id="btn_submit" class="button" />
		</div>
		<div class="center">
			<a class="button" href="{$classifieds_file_name}?a=4">{$msgs.usercp_back_to_my_account}</a>
		</div>
	</form>
</div>
{* end of div started in header.tpl *}