{* 16.09.0-79-gb63e5d8 *}
{$adminMsgs}

<script>
	//<![CDATA[
	jQuery(document).ready(function () {
		jQuery('a.resetText').click(function () {
			var defaultText = jQuery(this).find('input').val();
			jQuery(this).closest('div').find('.langText').val(defaultText);
			return false;
		});
	});      
	//]]>
</script>

<form action="" method="post" class='form-horizontal form-label-left'>

<div class="page-title1">Addon: <span class='color-primary-two'>{$addon_title}</span> </div>

	<fieldset>
		<legend>Edit Text</legend>
		<div class='x_content'>
			<input type="hidden" name="auth_tag" value="{$addon_auth_tag}" />
			{foreach $text_info as $index => $info}
				{if $info.section && $current_section!=$info.section}
					{if $current_section}
						<div class="center"><input type="submit" name="auto_save" value="Save" /></div>
						{* Close the <fieldset><div> from previous iteration *}
						</div></fieldset>
					{/if}
					{$current_section=$info.section}
					<fieldset><legend>{$info.section}</legend><div>
				{/if}
				<div class="header-color-primary-mute">{$info.name}</div>
				{if $info.desc}
					<p class="page_note">{$info.desc}</p>
				{/if}
				{foreach $info.lang as $lang_id => $lang_val}
					
						<div class='form-group'>
						<label class='control-label col-md-5 col-sm-5 col-xs-12'>{$languages.$lang_id} Language: </label>
						  <div class='col-md-6 col-sm-6 col-xs-12'>
							{if $info.type=='input'}
								<input type="text" class="langText form-control col-md-7 col-xs-12" size="40" name="tag[{$lang_id}][{$index}]" value="{$lang_val|escape}" />
							{else}
								<textarea class="langText" class="form-control" name="tag[{$lang_id}][{$index}]" cols="50" rows="5">{$lang_val|escape}</textarea>
							{/if}
							<br />
							<a href="#" class="resetText">
								Reset to Default
								<input type="hidden" class="defaultText" value="{$info.default|escape}" />
							</a>
						  </div>
						</div>



				{/foreach}
			{/foreach}
			<div class="center">
				<input type="submit" name="auto_save" value="Save" />
			</div>
			{if $current_section}
				</div></fieldset>
			{/if}
		</div>
	</fieldset>
</form>