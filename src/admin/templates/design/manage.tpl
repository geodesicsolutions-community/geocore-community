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

{include file="design/parts/workingOn.tpl"}

<form action="index.php?page=design_download_file" method="post" id="downloadForm">
	<input type="hidden" name="auto_save" value="1" />
	<input type="hidden" name="file" value="" id="downloadFileInput" />
</form>


<div{if $needsDefaultCopy} style="display: none;"{/if} id="manage-TemplateSet">
	<div id="designTopInfo" class="breadcrumbBorder">
		<div id="designTopInfoCreateNew">
			<a href="index.php?page=design_new_folder&amp;location={$location|escape}" class="lightUpLink" id="newFolderLinky"><img src="admin_images/icons/folder-add.png" alt="Create new Folder" title="Create new Folder" /></a>
			<a href="index.php?page=design_new_file&amp;location={$location|escape}" class="lightUpLink" id="newFileLinky"><img src="admin_images/icons/file-add.png" alt="Create new File" title="Create new File" /></a>
			<a href="index.php?page=design_upload_file&amp;location={$location|escape}" class="lightUpLink" id="uploadFileLinky"><img src="admin_images/icons/upload.png" alt="Upload" title="Upload" /></a>
			<a class="fileListLink" href="index.php?page=design_manage" id="refreshListLink"><img src="admin_images/icons/refresh.png" alt="Refresh List" title="Refresh List" /></a>
		</div>
		<ul id="breadcrumb">
			<li class="current">Loading...</li>
			{foreach from=$locationParts item=part}
				<li title="{$part.title}">
					{if $part.showLink}<a href="index.php?page=design_manage&amp;location={$part.fullPath|escape}" class="fileListLink">{/if}
					{$part.location}
					{if $part.showLink}</a>{/if}
				</li>
			{/foreach}
		</ul>
	</div>
	<div class="clearColumn"></div>
	<div id="designLeftMenu" class='col-xs-12 col-lg-3'>
		<div class="infoBox">
			<h2 class="title">Template Folders</h2>
			<div id="tsetJumpInside">
				{if $advMode}
					<div>Jump To:</div>
					<ul>
						<li>
							<a href="index.php?page=design_manage" id="navLink_t_sets" class="fileListLink folderLink">
								<img src="admin_images/icons/folder-closed.png" alt="folder" />
								Base Folder
							</a>
						</li>
					</ul>
				{/if}
				<div id="insideJumpTitle"></div>
				<ul>
					{* HREF updated by js on the fly for each location *}
					<li>
						<a href="#" title="Overall Page Templates" id="navLink_main_page" class="fileListLink folderLink">
							<img src="admin_images/icons/folder-closed.png" alt="folder" />
							<span class="text_blue">main_page</span> Templates
						</a>
					</li>
					<li>
						<a href="#" title="Images, CSS, JS, and other non-template files" id="navLink_external" class="fileListLink folderLink">
							<img src="admin_images/icons/folder-closed.png" alt="folder" />
							<span class="text_blue">external</span> Files
						</a>
					</li>
					<li>
						<a href="#" title="System Templates (Version Specific)" id="navLink_system" class="fileListLink folderLink">
							<img src="admin_images/icons/folder-closed.png" alt="folder" />
							<span class="text_blue">system</span> Templates
						</a>
					</li>
					<li>
						<a href="#" title="Module Templates (Version Specific)" id="navLink_module" class="fileListLink folderLink">
							<img src="admin_images/icons/folder-closed.png" alt="folder" />
							<span class="text_blue">module</span> Templates
						</a>
					</li>
					<li>
						<a href="#" title="Addon Templates (Version Specific)" id="navLink_addon" class="fileListLink folderLink">
							<img src="admin_images/icons/folder-open.png" alt="folder" />
							<span class="text_blue">addon</span> Templates
						</a>
					</li>
				</ul>
			</div>
			<div id="tset_jump_box" style="display: none;"></div>
		</div>
		<div class="infoBox">
			<h2 class="title">File Info</h2>
			<div id="designPreviewBox">
				<div id="designPreviewLoading" style="display: none;">
					<img src="admin_images/loading.gif" alt="Loading..." /> &nbsp; Loading...
				</div>
				<div id="designEmptyPreview">
					<div class="fileInfoName" id="designEmptyPreviewLabel">None Selected</div>
					<div class="fileInfoPreviewType">File Preview</div>
					<div class="designPreviewWindow empty"></div>
				</div>
				<div id="designPreviewMain" style="display: none;"></div>
			</div>
		</div>
	</div>
	<div id="designMainWindow" class='col-xs-12 col-lg-9'>
		<div id="refreshFilelistBox" style="display: none;">
			<img src="admin_images/loading.gif" alt="Loading..." /> &nbsp; Loading...
		</div>
		
		<div id="designFileList">
			{include file="design/parts/fileList.tpl"}
		</div>
		<div id="designBottomTools">
			<div id="designBottomTools_selected" style="{if $advMode}padding-bottom: 5px;{else}border-bottom: none;{/if}">
				<input type="checkbox" id="fileListCheckAllToggle" />
				&nbsp;
				<strong>With Selected:</strong>
				<ul>
					<li id="designSelected_edit">View/Edit</li>
					<li id="designSelected_cut">Cut</li>
					<li id="designSelected_copy">Copy</li>
					<li id="designSelected_paste">Paste</li>
					<li id="designSelected_rename">Rename</li>
					<li id="designSelected_make_copy">Make Copy</li>
					<li id="designSelected_download">Download</li>
					<li id="designSelected_del">Delete</li>
				</ul>
			</div>
			{if $advMode}
				<span id="folderCountSpan">-</span> Folders | <span id="fileCountSpan">-</span> Files | <span id="totalSizeSpan">-</span> Total Size<br />
				<strong>Selected:</strong> <span id="selectedFolderCountSpan">-</span> Folders | <span id="selectedFileCountSpan">-</span> Files
			{/if}
		</div>
		<div id="designClipboard" style="display: none;">
			<div id="clearclipboard">
				<a href="javascript:void(0)" class="mini_button" id="clearClipboardButton">Clear Clipboard</a>
			</div>
			<strong>File Clipboard</strong> (<span id="clipTypeSpan"></span>)
			<br />
			<div id="systemCopyWarningBox" class="page_note" style="display: none;">
				<strong>C<span class="opyReplace">opy</span>ing System, Module, or Addon Templates</strong> - When you c<span class="opyReplace">opy</span> these types of templates, simply browse
				to the template set you wish to copy the files to and paste
				anywhere within the template set, and they will be copied to
				the correct sub-folder within that template set, regardless of
				where in the template set you are currently viewing.
			</div>
			<br />
			<div id="designClipboard_files"></div>
			<div class="clearColumn" style="clear: right;"></div>
		</div>
	</div>
	<div class="clearColumn"></div>
</div>
