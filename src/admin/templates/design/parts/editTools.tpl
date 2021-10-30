{* 7.3beta2-104-g95aa210 *}


<div id="downloadTemplate_box" style="display: none;" class="templateTool">
	<div class="closeBoxButton">X</div>
	<div class="col_hdr templateToolTitlebar">
		<img src="admin_images/icons/download.png" alt="Download Template" title="Download Template" />
		Download File
	</div>
	
	<div class="templateToolContents">
		<form style="display: inline;" action="" method="post" id="downloadTplForm">
			<input type="hidden" name="file" value="{$file|escape}" />
			<input type="hidden" name="download" value="1" />
			<input type="hidden" name="auto_save" value="1" />
			<input type="hidden" name="contents" id="downloadContentsInput" value="" />
			<p>If there are un-saved changes, save them before downloading the file?</p>
			<label><input type="checkbox" name="saveChanges" id="downloadSaveChanges" value="1" checked="checked" /> Save current changes?</label>
			<br />
			<div class="templateToolButtons">
				<input type="submit" class="mini_button" value="Download File" />
				<input type="button" class="cancelButton mini_cancel" value="Cancel" />
			</div>
			<div class="clearColumn"></div>
		</form>
	</div>
</div>

<div id="uploadTemplate_box" style="display: none;" class="templateTool">
	<div class="closeBoxButton">X</div>
	<div class="col_hdr templateToolTitlebar">
		<img src="admin_images/icons/upload.png" alt="Upload File" title="Upload File" />
		Upload File
	</div>
	
	<div class="templateToolContents">
		<form enctype="multipart/form-data" action="" method="post">
			<input type="hidden" name="file" value="{$file|escape}" />
			<input type="hidden" name="auto_save" value="1" />
			<input type="hidden" name="upload" value="1" />
			
			<input type="file" name="contents" />
			<br /><br />
			<strong style="color: red;">Warning:</strong> This will overwrite the current template contents!
			
			<br />
			<div class="templateToolButtons">
				<input type="submit" class="mini_button" value="Upload" />
				<input type="button" class="cancelButton mini_cancel" value="Cancel" />
			</div>
			<div class="clearColumn"></div>
		</form>
	</div>
</div>

<div id="restoreDefault_box" style="display: none;" class="templateTool">
	<div class="closeBoxButton">X</div>
	<div class="col_hdr templateToolTitlebar">
		<img src="admin_images/icons/restore.png" alt="Restore Default Contents" title="Restore Default Contents" />
		Restore Default Contents
	</div>
	
	<div class="templateToolContents">
		<form enctype="multipart/form-data" action="" method="post">
			<input type="hidden" name="file" value="{$file|escape}" />
			<input type="hidden" name="auto_save" value="1" />
			<input type="hidden" name="restore" value="1" />
			
			<strong style="color: red;">Warning:</strong> This will overwrite the current template contents with the default contents!
			<br />
			<div class="templateToolButtons">
				<input type="submit" class="mini_button" value="Restore Default Contents" />
				<input type="button" class="cancelButton mini_cancel" value="Cancel" />
			</div>
			<div class="clearColumn"></div>
		</form>
	</div>
</div>


<div id="insertTag_box" style="display: none; width: 500px;" class="templateTool">
	<div class="closeBoxButton">X</div>
	<div class="col_hdr templateToolTitlebar">
		<img src="admin_images/icons/insert-tag.png" alt="Insert Tag" title="Insert Tag" />
		Insert Tag
	</div>
	
	<div class="templateToolContents">
		<ul class="tabList">
			<li class="activeTab" id="moduleTagTab">Module</li>
			{if $addonTags}<li id="addonTagTab">Addon Tags</li>{/if}
			<li id="subTagTab">Sub-Template</li>
			<li id="externalTagTab">External</li>
			<li id="listingTagTab">Listing</li>
			<li id="otherTagTab">Other</li>
		</ul>
		<div class="tabContents" id="moduleTagTabContents">
			<p class="page_note">
				A module tag will be replaced by the contents of a specific
				module.  When you place a new module tag into a template, save
				the changes and the "attached modules" will be updated automatically.
			</p>
			<br />
			<strong>Module Tag Syntax:</strong>
			<div class="page_note">
				{ldelim}module tag='<strong class="text_blue">module_tag_name</strong>'}
			</div>
			<br />
			<strong>Insert Module Tag in Template:</strong><br />
			<select id="moduleTagSelect">
				<option value="none">Select Module</option>
				{foreach from=$modules item=module key=moduleId}
					<option value="{$module}">{$pageNames.$moduleId}</option>
				{/foreach}
			</select> <input type="button" value="Insert Module Tag" class="mini_button" id="moduleInsertButton" />
		</div>
		{if $addonTags}
			<div class="tabContents" style="display: none;" id="addonTagTabContents">
				<p class="page_note">
					Each addon can have its own tags, which can be used to display
					that tag's contents.  When you place an addon tag into a template, save
					the template changes and the "attached addon tags" will be updated automatically.
					Consult the documentation for each addon to see what each of that addon's tags
					can be used for.
				</p>
				<br />
				<strong>Addon Tag Syntax:</strong>
				<div class="page_note">
					{ldelim}addon author='<span class="text_blue">addon_auth_tag</span>' addon='<span class="text_blue">addon_folder_name</span>' tag='<span class="text_blue">addon_tag</span>'}
				</div>
				<br />
				<strong>Insert Addon Tag in Template:</strong><br />
				<select id="addonTagSelect">
					<option value="none">Select Addon Tag</option>
					{foreach from=$addonTags item=info}
						{foreach from=$info.tags item=tag}
							<option value="{$info.name}.{$info.auth_tag}.{$tag}">{$info.title} - {$tag}</option>
						{/foreach}
					{/foreach}
				</select> <input type="button" value="Insert Addon Tag" class="mini_button" id="addonInsertButton" />
			</div>
		{/if}
		<div class="tabContents" style="display: none;" id="subTagTabContents">
			<p class="page_note">
				You can use a sub-template to display a sub-templates contents
				within other templates.  This tag is actually just the normal
				<a class="text_blue"href="http://www.smarty.net/manual/en/language.function.include.php" onclick="window.open(this.href); return false;">Smarty include
				function</a>, which is something built into Smarty template system itself.
				You can read more info within the
				<a href="http://www.smarty.net/manual/en/language.function.include.php" onclick="window.open(this.href); return false;">Smarty documentation</a>, almost
				everything works as it documents on the Smarty website.
				<br /><br />
				<strong>Additional Functionality</strong>:  The Geo software adds to
				the normal abilities of the built-in Smarty {ldelim}include}.
				When including
				a sub-template, it will "look" for that template across all currently
				active template sets.  To include a template in a different template set,
				you can use the filename relative to the other template set's main_body
				folder, and the system will include it just like it was in the same template set.
			</p>
			<br />
			<strong>Tag Syntax:</strong>
			<div class="page_note">
				{ldelim}include file='<span class="text_blue">sub_template_filename.tpl</span>'}
			</div>
			<br />
			<strong>Insert Tag in Template:</strong><br />
			{include file="design/parts/templateDropdown.tpl" selectName="sub-tpl" selectId="subTplSelect" templateSelected=0 excludeTemplate=0}
			<input id="subTplInsertButton" type="button" class="mini_button" value="Insert sub-template tag" />
		</div>
		<div class="tabContents" style="display: none;" id="externalTagTabContents">
			<p class="page_note">
				You can reference any file in <strong>{$geo_templatesDir}[Any Active Template Set]/external/</strong>
				inside of a template, using a special {ldelim}external ...} tag which would get the full URL
				for the file.
				<br /><br />
				<strong>Can be used in Page Text:</strong>  This is the only tag
				that can be used in page text, useful if you want to reference
				an image inside a text field.
			</p>
			<br />
			<strong>Usage Example:</strong>
			<p style="white-space: normal;" class="medium_font">
				You could use an external tag for the image URL in your template like so:
			</p>
			<div class="page_note">&lt;img src="{ldelim}external file='images/my_image.jpg'}" alt="My Image" /></div>
			
			<br />
			<strong>Tag Syntax:</strong>
			<div class="page_note">
				{ldelim}external file='<span class="text_blue">images/my_image.jpg</span>'}
			</div>
			<br />
			<strong>Insert Tag in Template:</strong><br />
			<label>external/
			<select id="externalTagSelect">
				<option value="none">Select File</option>
				{foreach from=$externalFiles item=filename}
					<option>{$filename|escape}</option>
				{/foreach}
			</select></label> <input type="button" class="mini_button" value="Insert External Tag" id="externalInsertButton" />
		</div>
		<div class="tabContents" style="display: none;" id="listingTagTabContents">
			<div class="col_hdr_top">Listing Details Page Tags</div>
			<p class="page_note">
				These tags display information about a listing on the listing details page.  They are only used
				on the template assigned to the classified or auction {ldelim}body_html} sub-template.  See 
				<a href="index.php?mc=pages_sections&page=sections_browsing_page&b=1" onclick="window.open(this.href); return false">Tag Verification Tool</a>
				for more info on what each tag displays.
			</p>
			<br />
			<select id="specialTagSelect2">
				<option value="none">Select Tag</option>
				{strip}
					{foreach $listingTags as $sectionTags}
						{foreach $sectionTags as $tag => $data}
							{if $data.type!='tag'}
								<option>{ldelim}${$tag}{rdelim}</option>
							{/if}
						{/foreach}
					{/foreach}
				{/strip}
			</select> <input type="button" value="Insert Tag" class="mini_button specialInsertButton" />
			<br /><br />
			<div class="col_hdr_top">Listing Details Page / Browsing Page Tags</div>
			<p class="page_note">
				These tags display information about a listing on the listing details page
				or on the browsing pages.  These can be used in any browsing page sub-template,
				or listing details page template.  See 
				<a href="index.php?mc=pages_sections&page=sections_browsing_page&b=1" onclick="window.open(this.href); return false">Tag Verification Tool</a>
				for more info on what each tag displays.
				{if $listingAddonTags}See the documentation for the associated addon for what
					each of the addon listing tags display.{/if}
			</p>
			<br />
			{if $listingAddonTags}<strong>Standard Listing Tags:</strong><br />{/if}
			<select id="specialTagSelect3">
				<option value="none">Select Tag</option>
				{strip}
					{foreach $listingTags as $sectionTags}
						{foreach $sectionTags as $tag => $data}
							{if $data.type=='tag'}
								<option>{ldelim}listing tag='{$tag}'{rdelim}</option>
							{elseif $data.type=='field'}
								<option>{ldelim}listing field='{$tag}'{rdelim}</option>
							{/if}
						{/foreach}
					{/foreach}
				{/strip}
			</select> <input type="button" value="Insert Tag" class="mini_button specialInsertButton" />
			{if $listingAddonTags}
				<br /><br />
				<strong>Addon Listing Tags:</strong><br />
				<select id="specialTagSelect4">
					<option value="none">Select Tag</option>
					{strip}
						{foreach $listingAddonTags as $info}
							{foreach $info.tags as $tag}
								{capture assign='fullTag'}{ldelim}listing addon='{$info.name}' tag='{$tag}'{rdelim}{/capture}
								<option value="{$fullTag|escape}">{$info.title} - {$tag}</option>
							{/foreach}
						{/foreach}
					{/strip}
				</select> <input type="button" value="Insert Tag" class="mini_button specialInsertButton" />
			{/if}
		</div>
		<div class="tabContents" style="display: none;" id="otherTagTabContents">
			<div class="col_hdr_top">Main Page Tags</div>
			
			<p class="page_note">
				These three tags are used on almost every page, to display the
				dynamically generated contents for the page.
			</p>
			<br />
			
			<select id="specialTagSelect">
				<option value="none">Select Tag</option>
				<option value="{ldelim}head_html}">{ldelim}head_html} - use in &lt;head&gt;...&lt;/head&gt; section</option>
				<option value="{ldelim}body_html}">{ldelim}body_html} - Display page's main contents</option>
				<option value="{ldelim}footer_html}">{ldelim}footer_html} - use right before &lt;/body&gt;</option>
			</select> <input type="button" value="Insert Tag" class="specialInsertButton mini_button" />
			<br /><br />
			
			{if $signs_flyersTags}
				<div class="col_hdr_top">Sign/Flyer Page Tags</div>
				<p class="page_note">
					These tags display information about a listing on a sign or flyer.  They are only used
					on the template assigned to the sign or flyer template.  See 
					<a href="index.php?mc=pages_sections&page=sections_browsing_page&b=73" onclick="window.open(this.href); return false">Tag Verification Tool</a>
					for more info on what each tag displays.
				</p>
				<br />
				<select id="specialTagSelect5">
					<option value="none">Select Tag</option>
					{foreach from=$signs_flyersTags item=tag}
						<option>{ldelim}${$tag}{rdelim}</option>
					{/foreach}
				</select> <input type="button" value="Insert Tag" class="mini_button specialInsertButton" />
				<br /><br />
			{/if}
		</div>
		<div class="templateToolButtons">
			<input type="button" class="cancelButton mini_cancel" value="Close" />
		</div>
		<div class="clearColumn"></div>
	</div>
</div>