{* 17.12.0-10-g8226874 *}
{include file='control_panel/header.tpl'}
	{* header.tpl starts a div for main column *}
	<form method="post" action="{$classifieds_file_name}?a=ap&amp;addon=storefront&amp;page=control_panel&amp;action=update&amp;action_type=pages">

		<div class="content_box">
			<h3 class="subtitle">{$msgs.usercp_pages_settings_header}</h3>
			<div class="{cycle values='row_odd,row_even'}">
				<label class="field_label" for="data[home_cat]">
					{$msgs.usercp_pages_settings_homecatlabel}
				</label>
				<input type="text" name="data[home_cat]" id="data[home_cat]" value="{$home_cat}" class="field" />
			</div>
			<div class="{cycle values='row_odd,row_even'}">
				{if !$no_pages}
					{* pages exist -- show dropdown to select default *}
					<label class="field_label" for="data[default_page]">
						{$msgs.usercp_pages_settings_defaultlabel}
					</label>
					
					<select name="data[default_page]" id="data[default_page]" class="field">
						<option value="">{$msgs.usercp_pages_settings_defaultpagenull}</option>
						{foreach from=$pages item=page}
							<option value="{$page.page_id}" {if $page.selected}selected="selected"{/if}>{$page.name}</option>
						{/foreach}
					</select>
				{/if}
			</div>
			
			<div class="{cycle values='row_odd,row_even'}">
				<a href="{$classifieds_file_name}?a=ap&amp;addon=storefront&amp;page=control_panel&amp;action=update&amp;action_type=pages&amp;create_pages=yes" class="button" onclick="if(!confirm('{$msgs.usercp_pages_settings_restoredefaults_confirm}')) return false;">{$msgs.usercp_pages_settings_restoredefaults}</a>
			</div>
			
			<h3 class="subtitle">{$msgs.usercp_pages_settings_addnewheader}</h3>
			<div class="{cycle values='row_odd,row_even'}">
				<label class="field_label" for="data[new_cat]">
					{$msgs.usercp_pages_settings_addnewcategory}
				</label>
				<input type="text" name="data[new_cat]" id="data[new_cat]"  class="field" />
			</div>
				
			<div class="{cycle values='row_odd,row_even'}">
				<label class="field_label" for="data[new_page]">
					{$msgs.usercp_pages_settings_addnewpage}
				</label>
				<input type="text" name="data[new_page]" id="data[new_page]" value="{$new_page}" class="field" />
			</div>
			<div class="center"><input type="submit" value="{$msgs.usercp_pages_btn_save}" class="button" /></div>
		</div>
	</form>

	{if $category_count > 0}
		<div class="content_box">
			<h3 class="subtitle">{$msgs.usercp_pages_cats_header}</h3>

			<div id="category_sort_result"></div>

			<ul class="sortable_list" id="category_list">
				{foreach from=$categories item=cat}
					<li id="cat_sort_{$cat.category_id}" class="storefront_sort_item clearfix">
						<div class="sortable_item_controls" id="cat_controls_{$cat.category_id}">
							<a href="javascript:void(0);" onclick="edit_cat({$cat.category_id}); return false;" class="edit">{$msgs.usercp_pages_btn_edit}</a>
							<a href="{$classifieds_file_name}?a=ap&amp;addon=storefront&amp;page=control_panel&amp;action=update&amp;action_type=pages&amp;del_cat={$cat.category_id}" class="delete">{$msgs.usercp_pages_btn_delete}</a>
						</div>
						
						<div id="cat_name_{$cat.category_id}" class="item_name" style="display: inline-block;">
							<span class="glyphicon glyphicon-move"></span>&nbsp;{$cat.category_name}
						</div>
						<div style="clear:both; height:20px; position:relative;">
						<a href="javascript:void(0);" onclick="jQuery('#reveal_add_subcat_{$cat.category_id}').toggle('fast');" class="edit" style="display: inline-block;" />{$msgs.usercp_pages_btn_addsub}</a>
						</div>
			<div id="subcategories_for_{$cat.category_id}"  style="clear:both; margin-top: 5px;">
							{foreach $cat.subcategories as $sub_id => $sub_name}
								<div id="subcategory_{$sub_id}" class="subcat_input_container">
									<span id="subcat_main_{$sub_id}">
										<span id="subcat_name_{$sub_id}">{$sub_name}</span> 
										<a href="javascript:void(0);" onclick="jQuery('#subcat_main_{$sub_id}').hide(); jQuery('#subcat_edit_{$sub_id}').show();" class="edit" />{$msgs.usercp_pages_btn_edit}</a>
										<a href="{$classifieds_file_name}?a=ap&amp;addon=storefront&amp;page=control_panel&amp;action=update&amp;action_type=pages&amp;del_cat={$sub_id}" class="delete">{$msgs.usercp_pages_btn_delete}</a>
									</span>
									<span id="subcat_edit_{$sub_id}" style="display: none;">
										<input type="text" name="edit_subcat_txt_{$sub_id}" id="edit_subcat_txt_{$sub_id}" value="{$sub_name}" placeholder="{$msgs.usercp_pages_plh_newsub}" class="field" />
										<a href="javascript:void(0);" onclick="editSubcategory({$sub_id});" class="edit" />{$msgs.usercp_pages_btn_save}</a>
										<a href="javascript:void(0);" onclick="jQuery('#subcat_main_{$sub_id}').show(); jQuery('#subcat_edit_{$sub_id}').hide();" class="delete" />{$msgs.usercp_pages_btn_cancel}</a>
									</span>
								</div>
							{/foreach}
						</div>
						
						<div id="reveal_add_subcat_{$cat.category_id}" style="display:none;">
							<div class="subcat_input_container">
								<input type="text" name="new_subcat_for_{$cat.category_id}" id="new_subcat_for_{$cat.category_id}" placeholder="{$msgs.usercp_pages_plh_newsub}" class="field" />
								<a href="javascript:void(0);" onclick="addSubcategory({$cat.category_id});" class="edit" />{$msgs.usercp_pages_btn_save}</a>
								<a href="javascript:void(0);" onclick="jQuery('#reveal_add_subcat_{$cat.category_id}').hide('fast');" class="delete" />{$msgs.usercp_pages_btn_cancel}</a>
							</div>
						</div>
						
						<input type="hidden" id="cat_oldname_{$cat.category_id}" value="{$cat.category_name}" /> {*hidden input field, so the ajax form can grab the name *}
					</li>
				{/foreach}
			</ul>
		</div>

		{add_footer_html}
			<script type="text/javascript">
			//<![CDATA[
			
				var addSubcategory = function(id) {
					//add subcat to db
					jQuery.ajax('{$classifieds_file_name}?a=ap&addon=storefront&page=control_panel_ajax', {
						data: {
							action: 'add_subcategory',
							parent: id,
							name: jQuery('#new_subcat_for_'+id).val()
						},
						type: 'POST'
					}).done(function(msg) {
						//easy way: reload page so that new subcat is shown without having to duplicate creation code here
						window.location.href = "{$classifieds_file_name}?a=ap&addon=storefront&page=control_panel&action=display&action_type=pages"
					});
				}
				
				var editSubcategory = function(id) {
					//update db with ajax
					jQuery.ajax('{$classifieds_file_name}?a=ap&addon=storefront&page=control_panel_ajax', {
						data: {
							action: 'edit_subcategory',
							edit: id,
							name: jQuery('#edit_subcat_txt_'+id).val()
						},
						type: 'POST'
					}).done(function(msg) {
						//ajax should return new name (even if it hasn't changed) -- update fields with new name
						jQuery("#subcat_name_"+id).html(msg);
						jQuery("#edit_subcat_txt_"+id).val(msg);
						jQuery('#subcat_main_'+id).show();
						jQuery('#subcat_edit_'+id).hide();
					});
					
					
				}
				
				var edit_cat = function (id) {
					oldValue = jQuery('#cat_oldname_'+id).val();
					
					//hide edit/delete button while editing
					jQuery('#cat_controls_'+id).hide();
					
					formHTML = '';
					formHTML += '<input type="text" name="update_cat_name_'+id+'" id="send_cat_'+id+'" value="'+oldValue+'" class="field" /> ';
					formHTML += '<input type="button" value="{$msgs.usercp_pages_btn_save}" onclick="SendCatName('+id+', jQuery(\'#send_cat_'+id+'\').val());" class="button" />';
					jQuery('#cat_name_'+id).html(formHTML);
					
					//don't use hand cursor in expanded mode
					jQuery('#cat_name_'+id).css('cursor','auto');
					
					//so that the "form" still submits when Enter is pressed
					jQuery('send_cat_'+id).keypress(function(e) {
						if(event.which == 13) {
							event.preventDefault();
							SendCatName(id, jQuery('#send_cat_'+id).val());
						}
					});
				}
				
				var SendCatName = function (id, val) {
					jQuery.post('{$classifieds_file_name}?a=ap&addon=storefront&page=control_panel_ajax', { cat_id: id, new_name: val }, function(changedName) {
						jQuery('#cat_oldname_'+id).val(changedName); //update hidden form field
						jQuery('#cat_name_'+id).html('<span class="glyphicon glyphicon-move"></span>&nbsp;'+changedName); //remove form and replace with new name
						
						jQuery('#cat_controls_'+id).show(); //show edit/delete controls again
						jQuery('#cat_name_'+id).css('cursor','pointer'); //back to using pointer to drag
					}, 'text');
				}
	
				//load category sorter
				jQuery(document).ready(function() {
					jQuery('#category_list').sortable({
						handle: ".glyphicon-move",
						update: function(event, ui) {						
							jQuery.post('{$classifieds_file_name}?a=ap&addon=storefront&page=control_panel_ajax', { category_order: jQuery('#category_list').sortable('serialize') }, function(ret) {
								jQuery('#category_sort_result').hide();
								jQuery('#category_sort_result').html('<div class="success_box">{$msgs.usercp_pages_cats_saved}</div>');
								jQuery('#category_sort_result').show('slow');
								setTimeout( function() { jQuery('#category_sort_result').hide('slow'); } , 1500); 
							}, 'text');
							
						}
					});
				});
				
			//]]>
			</script>
		{/add_footer_html}
	{/if}
		
	{if $page_count > 0}

		<div class="content_box">
			<h3 class="subtitle">{$msgs.usercp_pages_page_header}</h3>

			<div id="page_sort_result"></div>

			<ul class="sortable_list" id="page_list">
				{foreach from=$pages item=page}
					<li id="page_sort_{$page.page_id}" class="storefront_sort_item clearfix">
					
						<div class="sortable_item_controls" id="page_controls_{$page.page_id}">
							<a href="javascript:void(0);" onclick="edit_page({$page.page_id}); return false;" class="edit">{$msgs.usercp_pages_btn_edit}</a>
							<a href="{$classifieds_file_name}?a=ap&amp;addon=storefront&amp;page=control_panel&amp;action=update&amp;action_type=pages&amp;del_page={$page.page_id}" class="delete">{$msgs.usercp_pages_btn_delete}</a>
						</div>
						
						<div class="item_name" id="page_name_{$page.page_id}"><span class="glyphicon glyphicon-move"></span>&nbsp;{$page.name}</div>
						
						<div id="page_edit_form_{$page.page_id}" style="display: none;">
							<div>
								<label class="field_label">{$msgs.usercp_pages_page_name}</label> 
								<input type="text" name="update_page_name_{$page.page_id}" id="send_page_{$page.page_id}" value="{$page.name}" class="field" />
							</div>
							<div>
								<label class="field_label">{$msgs.usercp_pages_page_link}</label> 
								<input type="text" name="update_page_link_{$page.page_id}" id="page_link_{$page.page_id}" value="{$page.link_text}" class="field" />
							</div>
							<div>
								<label class="field_label">{$msgs.usercp_pages_page_body}</label>
							</div>
							<div id="editor_wrapper_{$page.page_id}">
								<a href="#" onclick="gjWysiwyg.toggleTinyEditors(); return false;">{$messages.add_remove_wysiwyg|escape_js}</a><br />
								<textarea class="editor field" id="page_body_{$page.page_id}" name="update_page_body_{$page.page_id}" style="width: 98%; height: 200px;">{$page.body|escape}</textarea>
							</div>
							<div class="center clearfix">
								<input type="button" value="{$msgs.usercp_pages_btn_save}" class="button" onclick="SendPageData({$page.page_id});" />
								<input type="button" value="{$msgs.usercp_pages_btn_cancel}" onclick="PageCancel({$page.page_id})" class="cancel" />
							</div>
						</div>
					</li>
				{/foreach}
			</ul>
		</div>
		
		{add_footer_html}
			<script type="text/javascript">
			//<![CDATA[
			
				var edit_page = function (id) {
					
					//show edit form
					jQuery('#page_edit_form_'+id).show('slow');
					//hide edit/delete and static title button while editing
					jQuery('#page_controls_'+id).hide();
					jQuery('#page_name_'+id).hide();
					
					//during edit, kill the hand cursor
					jQuery('#page_sort_'+id).css('cursor','auto');
																	
				}
				
				var PageCancel = function (id)
				{					
					jQuery('#page_edit_form_'+id).hide('slow'); //hide edit form
					jQuery('#page_name_'+id).show(); //restore static title label
					jQuery('#page_controls_'+id).show(); //show edit/delete controls again
					jQuery('#page_sort_'+id).css('cursor','pointer'); //use pointer cursor for sorting
				}
				
				var SendPageData = function (id)
				{
					var name = jQuery('#send_page_'+id).val();
					var link = jQuery('#page_link_'+id).val();
	
					var body = (localStorage.tinyMCE === 'off') ? jQuery('#page_body_'+id).val() : tinyMCE.get('page_body_'+id).getContent();
	
					jQuery.post('{$classifieds_file_name}?a=ap&addon=storefront&page=control_panel_ajax', {
							page_id: id,
							new_name: name,
							new_link: link,
							new_body: body
						},
						function(returned) {
							newData = returned.split("~~!~~");
							/* newData is the following (updated) data:
								[0] => page name
								[1] => page link
								[2] => page body
							*/
							
							jQuery('#page_name_'+id).html('<span class="glyphicon glyphicon-move"></span>&nbsp;'+newData[0]); //update static page name label
							
							//let PageCancel do the rest of the heavy lifting for closing up the edit box, so we don't duplicate code
							PageCancel(id);
							
							//show confirmation message
							jQuery('#page_sort_result').hide();
							jQuery('#page_sort_result').html('<div class="success_box">Pages Updated</div>');
							jQuery('#page_sort_result').show('fast');
							setTimeout( function() { jQuery('#page_sort_result').hide('fast'); } , 1500);
							
					}, 'text');
							
						
						
				}
	
				//load page sorter
				jQuery(document).ready(function() {
					jQuery('#page_list').sortable({
						handle: ".glyphicon-move",
						update: function(event, ui) {						
							jQuery.post('{$classifieds_file_name}?a=ap&addon=storefront&page=control_panel_ajax', { page_order: jQuery('#page_list').sortable('serialize') }, function(ret) {
								jQuery('#page_sort_result').hide();
								jQuery('#page_sort_result').html('<div class="success_box">{$msgs.usercp_pages_page_saved}</div>');
								jQuery('#page_sort_result').show('slow');
								setTimeout( function() { jQuery('#page_sort_result').hide('slow'); } , 1500); 
							}, 'text');
							
						}
					});
				});
				
			//]]>
			</script>
		{/add_footer_html}
	{/if}
	<div class="center">
		<a class="button" href="{$classifieds_file_name}?a=4">{$msgs.usercp_back_to_my_account}</a>
	</div>
</div>
{* end of div started in header.tpl *}
