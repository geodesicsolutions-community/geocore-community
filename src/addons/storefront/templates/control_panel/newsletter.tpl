{* @git-info@ *}
{include file='control_panel/header.tpl'}
	{* header.tpl starts a div for main column *}
	{if $show_newsletter}
		<form method="post" action="{$classifieds_file_name}?a=ap&amp;addon=storefront&amp;page=control_panel&amp;action=update&amp;action_type=newsletter">
			<div class="content_box">
				<h3 class="subtitle">{$msgs.usercp_news_settings_header}</h3>
				
				<div class="{cycle values='row_odd,row_even'}">
					<label class="field_label">
						{$msgs.usercp_news_settings_allownewsubs}
					</label>
					<select name="data[display_newsletter]" class="field">
						<option value="1" {if $display_newsletter == 1}selected="selected"{/if}>{$msgs.usercp_news_settings_allownewsubs_yes}</option>
						<option value="0" {if $display_newsletter == 0}selected="selected"{/if}>{$msgs.usercp_news_settings_allownewsubs_no}</option>
					</select>
				</div>
				
				<div class="{cycle values='row_odd,row_even'}">
					<label class="field_label">
						{$msgs.usercp_news_settings_currentsubs}
					</label>
					<strong class="text_highlight">{$current_sub_count}</strong>
				</div>
				
				{if $current_sub_count > 0}
					<div class="{cycle values='row_odd,row_even'}">
						<label class="field_label">	
							{$msgs.usercp_news_settings_remove}
						</label>
						Check to confirm: <input type="checkbox" name="data[do_remove]" value="1" />
					</div>
					<div class="{cycle values='row_odd,row_even'}">
						<label class="field_label">	
							{$msgs.usercp_news_settings_remselect}
						</label>
						<select style="vertical-align: top; height:100px;" name='data[removeThese][]' id='subscriberEmail' multiple='multiple' size='5' class="field">
							{foreach from=$emails item='email'}
								<option value="{$email}">{$email}</option>
							{/foreach}
						</select>
					</div>
					<input type="reset" value="Reset Form" class="button" /> <input type="submit" value="{$msgs.usercp_news_settings_saveremove}" class="button" />
				{else}
					{* no subscriber list, so just show a normal "save" button *}
					<div class="center"><input type="submit" value="{$msgs.usercp_news_settings_save}" class="button" /></div>
				{/if}
			</div>
		</form>

		<form method="post" action="{$classifieds_file_name}?a=ap&amp;addon=storefront&amp;page=control_panel&amp;action=update&amp;action_type=newsletter">
			<div class="content_box">
				<h3 class="subtitle">{$msgs.usercp_news_send_header}</h3>
				<div class="{cycle values='row_odd,row_even'}">
					<label class="field_label">
						{$msgs.usercp_news_send_subject}
					</label>
					<input type="text" name="data[newsletter_subject]" size="40" maxlength="255" class="field" />
				</div>
				<div class="{cycle values='row_odd,row_even'}">
					<label class="field_label">{$msgs.usercp_news_send_bodyheader}</label>&nbsp;<a href="#" onclick="gjWysiwyg.toggleTinyEditors(); return false;">{$messages.add_remove_wysiwyg}</a>
				</div>
				<div class="{cycle values='row_odd,row_even'}">
					<textarea class="editor field" name="data[newsletter_body]" id="newsletter" style="width: 95%; height: 200px;"></textarea>		
				</div>
				<div class="center">
					<input type="submit" value="{$msgs.usercp_news_send_button}" class="button" />
				</div>
			</div>
		</form>
	{/if}
	<div class="center">
		<a class="button" href="{$classifieds_file_name}?a=4">{$msgs.usercp_back_to_my_account}</a>
	</div>
</div>
{* end of div started in header.tpl *}
