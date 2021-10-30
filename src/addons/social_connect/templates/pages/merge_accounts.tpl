{* 67d0e9c *}

<div class="content_box">
	<h1 class="title">{$msgs.fb_merge_page_title}</h1>
	<h1 class="subtitle">{$msgs.fb_merge_page_subtitle}</h1>
	<p class="page_instructions">{$msgs.fb_merge_page_instructions}</p>
	<br /><br />
	
	<h1 class="subtitle">{$msgs.fb_merge_page_section_title}</h1>
	<br />
	<img src="https://graph.facebook.com/{$user}/picture" alt="" style="float: left; margin: 0px 5px 5px 5px; border:2px solid black;" />
	
	<strong>{$msgs.fb_merge_page_profile_label}</strong> <a href="{$user_profile.link}" onclick="window.open(this.href); return false;">{$user_profile.name}</a>
	<div class="clear"></div>
	<br />
	<h1 class="subtitle">{$msgs.fb_merge_page_merge_section_title}</h1>
	<div class="{cycle values='row_even,row_odd'}">
		<label class="field_label">{$msgs.fb_merge_page_linked_username_label}</label>
		{$other_user.username}
	</div>
	<div class="{cycle values='row_even,row_odd'}">
		<label class="required">{$msgs.fb_merge_page_linked_password_label}</label>
		{$msgs.fb_merge_page_linked_password_value}
	</div>
	<br /><br />
	<div class="{cycle values='row_even,row_odd'}">
		<label class="field_label">{$msgs.fb_merge_page_unlinked_username_label}</label>
		{$this_user.username}
	</div>
	<form action="" method="post">
		<input type="hidden" name="merge" value="yes" />
		<div class="{if $errors.verify}field_error_row{else}{cycle values='row_even,row_odd'}{/if}">
			<label class="required">{$msgs.fb_merge_page_unlinked_password_label} *</label> 
			<input type="password" name="verify" class="field" />
			{if $errors.verify}<br /><span class="error_message">{$errors.verify}</span>{/if}
		</div>
		<br /><br />
		<div class="center">
			<input type="submit" value="{$msgs.fb_merge_page_submit_text}" class="button" />
			<br /><br />
			<a href="{$classifieds_file_name}?cancel_fb_link=yes" class="cancel">{$msgs.fb_merge_page_cancel_link_text}</a>
		</div>
	</form>
	<div class="clear"></div>
</div>