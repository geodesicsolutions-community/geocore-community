{* 16.09.0-79-gb63e5d8 *}

{if $adminMsgs}
	<script type="text/javascript">
		//<![CDATA[
			Event.observe(window,'load',function(event) {ldelim}
				//alert them about it!
				geoUtil.addMessage('{$adminMsgs|escape_js}');
			});
		//]]>
	</script>
{/if}

{if $codeMirrorMode}
	<script type="text/javascript">
		//<![CDATA[
			var geoDesignManage = geoDesignManage || { };
			geoDesignManage.codeMirrorMode = '{$codeMirrorMode|escape_js}';
			geoDesignManage.useCodeMirror = true;
			geoDesignManage.codeMirrorSmartIndent = {if $codemirrorAutotab}true{else}false{/if};
			{if $codemirrorTheme}
				geoDesignManage.codeMirrorTheme = '{$codemirrorTheme}';
			{/if}
		//]]>
	</script>
{/if}

{include file="design/parts/workingOn.tpl"}

{include file="design/parts/editTools.tpl"}


<input type="hidden" id="contentsUntouched" value="{$contents|escape}" name="contentsUntouched" />

{if $canEdit}<form action="" method="post" id="fileEditForm">{/if}
	<input type="hidden" name="file" value="{$file|escape}" />
	<input type="hidden" name="auto_save" value="1" />
	<div class="breadcrumbBorder">
		<ul id="breadcrumb">
			{foreach from=$locationParts item=part}
				<li title="{$part.title}"{if $part.endPath} class="current2"{/if}>
					{if $part.showLink&&!$part.endPath}<a href="index.php?page=design_manage&amp;location={$part.fullPath|escape}">{/if}
					{$part.location}
					{if $part.showLink&&!$part.endPath}</a>{/if}
				</li>
			{/foreach}
		</ul>
	</div>

	<div class="page_note" style="margin: 10px 25px;">
		<span><strong>Access:</strong> {$access}</span>
	</div>
	{if $fileType!='main_page'&&$fileType!='external'&&$canEdit}
		{include file="design/parts/editSystemWarning.tpl"}
	{/if}
	{if $trial_msg}
		<p class="page_note">{$trial_msg}</p>
	{/if}
	<br />
	<ul class="tabList">
		{if $showWysiwyg}
			<li id="designTab" title="WYSIWYG (What You See Is What You Get) Editor">
				<i class="fa fa-picture-o"></i><span class="visible-lg-inline"> WYSIWYG {if $canEdit}Editor{else}Viewer{/if}</span>
			</li>
		{/if}
		<li id="codeTab"><i class="fa fa-code"></i><span class="visible-lg-inline"> Source Code {if $canEdit}Editor{else}Viewer{/if}</span></li>
		{if $fileType=='main_page'}
			<li id="attachmentsTab"><i class="fa fa-link"></i><span class="visible-lg-inline"> Template Attachments</span></li>
			<li id="attachedToTab"><i class="fa fa-file"></i><span class="visible-lg-inline"> Template Attached To..</span></li>
		{/if}
	</ul>

	<div class="tabContents" id="editorContents">
		<div id="editTemplateButtons" style="display: none;">{strip}
			<img src="admin_images/icons/download.png" id="downloadTemplate" alt="Download Template" title="Download Template" class="autoTemplateButton" />
			<img src="admin_images/icons/upload.png" id="uploadTemplate" alt="Upload Changes" title="Upload Changes" class="autoTemplateButton" />
			{if $canEdit}<input type="image" src="admin_images/icons/save.png" alt="Save Changes" title="Save Changes" />{/if}
			{if $restoreDefault}
				<img src="admin_images/icons/restore.png" id="restoreDefault" alt="Restore Default Contents" title="Restore Default Contents" class="autoTemplateButton" />
			{/if}
			{if $fileType=='main_page'}
				<div class="buttonSeperator"></div>
				<img src="admin_images/icons/insert-tag.png" id="insertTag" alt="Insert a Tag" title="Insert a Tag" class="autoTemplateButton" />
			{/if}
		{/strip}</div>
		<div id="popupButtonHook"></div>
		<input type="hidden" name="contentsPre" id="tplContentsPre" />
		<input type="hidden" name="contentsPost" id="tplContentsPost" />

		{if $default_contents}
			<div class="leftCss">
				<strong>{$css_filename} file (Edit This)</strong>
				<textarea name="contents" id="tplContents"{if !$canEdit} readonly="readonly"{/if} rows="50" cols="300">{$contents|escape}</textarea>
			</div>
			<div class="rightCss">
				<strong>default.css file (Read-Only for Reference)</strong>
				<textarea id="default_css" name="ignore">{$default_contents}</textarea>
			</div>
			<br />
		{else}
			{* normal display *}
			<textarea name="contents" id="tplContents"{if !$canEdit} readonly="readonly"{/if} rows="50" cols="300">{$contents|escape}</textarea>
		{/if}

		<div id="editNotes">
			{if $codemirrorSearch}
				<p class="page_note" style="float: right;">
					<strong class="text_blue">Search / Replace Shortcut Keys</strong><br /><br />

					<strong>Start Searching:</strong> Ctrl-F / Cmd-F<br />
					<strong>Find next:</strong> Ctrl-G / Cmd-G<br />
					<strong>Find previous:</strong> Shift-Ctrl-G / Shift-Cmd-G<br />
					<strong>Replace:</strong> Shift-Ctrl-F / Cmd-Option-F<br />
					<strong>Replace All:</strong> Shift-Ctrl-R / Shift-Cmd-Option-F
				</p>
			{/if}
		</div>

		<br /><br />
		{if $canEdit}
			<div class="center">
				<input type="submit" value="Save Changes" class="mini_button" />
			</div>
			<br />
		{/if}
		<div class="clear"></div>
	</div>

	{if $fileType=='main_page'}
		<div id="attachmentsTabContents" class="tabContents">
		    <div style="background-color: #F7F7F7; padding: 3px;">
			<p class="page_note">
				<strong>Attachments:</strong> List updated after last save or when the <em>Re-Scan Attachments</em> tool was last used.
			</p>
			{if $advMode}
				<div class="{cycle values="row_color1,row_color2"}">
					<div class="leftColumn">Attachment File Used:</div>
					<div class="rightColumn">
						{$modules_to_template_filename}
					</div>
					<div class="clearColumn"></div>
				</div>
			{/if}

			<p class="large_font" style="text-align:center;">Attachments in template: <strong class="text_green">{$file}</strong></p>
			<div class="col_hdr_top">Attached Modules</div>
			{foreach from=$attachments.modules item=module key=moduleId}
				<div class="{cycle values="row_color1,row_color2"}">
					<div class="leftColumn">
						<a href="index.php?page=modules_page&b={$moduleId}">{$moduleId} - {$pageNames.$moduleId}</a>
					</div>
					<div class="rightColumn">
						{ldelim}module tag="{$module}"}
					</div>
					<div class="clearColumn"></div>
				</div>
			{foreachelse}
				<div style="text-align: center;" class="medium_font">N/A</div>
			{/foreach}
			<div class="col_hdr_top">Addon Tags</div>
			{foreach from=$attachments.addons key="author" item="author_info"}
				{foreach from=$author_info key="addon" item="addon_data"}
					{foreach from=$addon_data item="tag"}
						<div class="{cycle values="row_color1,row_color2"}">
							<div class="leftColumn">{$addon} - {$tag}</div>
							<div class="rightColumn">
								{ldelim}addon author="{$author}" addon="{$addon}" tag="{$tag}"}
							</div>
							<div class="clearColumn"></div>
						</div>
					{/foreach}
				{/foreach}
			{foreachelse}
				<div style="text-align: center;" class="medium_font">N/A</div>
			{/foreach}

			<div class="col_hdr_top">Sub-Templates</div>
			{foreach from=$attachments.sub_pages item=sub_page}
				<div class="{cycle values="row_color1,row_color2"}">
					<div class="leftColumn">
						{$sub_page.name}
						[{foreach from=$sub_page.tsets item=tset name=subpageTSets}
							<a href="index.php?page=design_edit_file&amp;file={$tset|escape}/main_page/{$sub_page.name|escape}">{$tset}</a>{if !$smarty.foreach.subpageTSets.last},{/if}
						{foreachelse}
							<strong style="color: red;">Not found! (check working with template sets)</strong>
						{/foreach}]
					</div>
					<div class="rightColumn">{ldelim}include file="{$sub_page.name}"}</div>
					<div class="clearColumn"></div>
				</div>
			{foreachelse}
				<div style="text-align: center;" class="medium_font">N/A</div>
			{/foreach}
		    </div>
		</div>
		<div class="tabContents" id="attachedToTabContents">
		    <div style="background-color: #F7F7F7; padding: 10px;"
			<p class="large_font" style="text-align:center;"><strong>Attachments for:</strong> <strong class="text_green">{$file}</strong> within <strong class="text_green">{$t_set}</strong>:</p>
			<div class="col_hdr_top">Attached to pages</div>
			{foreach from=$attachedToPage item=page}
				<div class="{cycle values="row_color1,row_color2"}">
					<div class="leftColumn">
						{$page}
						{if $pageNames.$page}
							- {$pageNames.$page}
						{/if}
					</div>
					<div class="rightColumn">
						<a href="index.php?page=page_attachments_edit&pageId={$page}">View/Edit Page Attachments</a>
					</div>
					<div class="clearColumn"></div>
				</div>
			{foreachelse}
				<div style="text-align: center;" class="medium_font">N/A</div>
			{/foreach}
			<div class="col_hdr_top">Attached to templates as Sub-Template</div>
			{foreach from=$attachedToTpl item=tpl}
				<div class="{cycle values="row_color1,row_color2"}">
					<div class="leftColumn">{$tpl}</div>
					<div class="rightColumn"><a href="index.php?page=design_edit_file&amp;file={$t_set}/main_page/{$tpl|escape}">Edit Template in {$t_set}</a></div>
					<div class="clearColumn"></div>
				</div>
			{foreachelse}
				<div style="text-align: center;" class="medium_font">N/A</div>
			{/foreach}
		    </div>
		</div>
	{/if}
	{if $showWysiwyg}
		<!-- Dummy div, design and edit share same div -->
		<div id="designTabContents"></div>
	{/if}
	<!--  Dummy div, design and edit share same div -->
	<div id="codeTabContents"></div>

{if $canEdit}</form>{/if}
