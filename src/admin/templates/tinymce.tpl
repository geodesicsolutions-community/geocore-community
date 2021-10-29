{* 17.10.0-22-g1a8f4fd *}

<!-- tinyMCE -->


<script type="text/javascript">

gjWysiwyg.loadTiny = function () {
	//NOTE: loadTiny() is first run in fieldset_toggle.js as it
	//needs to first be called AFTER fieldsets are manipulated.
	
	tinyMCE.init({
		selector: "{if $type=='textManager'}.textManager{else}#tplContents{/if}",
		document_base_url : '{$doc_base_url}',
	
		plugins: 'lists,link,paste,table,insertdatetime,code,preview,media,searchreplace,print,directionality,fullscreen,noneditable,visualchars,nonbreaking,emoticons,anchor,textcolor,colorpicker,charmap,searchreplace,hr{if $fullpage},fullpage{/if}',
		toolbar: [
		'{if $fileBased}geoUpload geoDownload geoSave {if $restoreDefault}geoRestore{/if} | geoTags | {/if}styleselect | fontselect fontsizeselect',
		'undo redo | cut copy paste | bold italic underline strikethrough subscript superscript | removeformat | alignleft aligncenter alignright alignjustify | outdent indent blockquote',
		'searchreplace | bullist numlist | link charmap nonbreaking media hr emoticons | insertdate inserttime| forecolor backcolor | ltr rtl | fullscreen{if $fullpage} fullpage{/if}'
		],
		menubar: false,
		
		forced_root_block : '',
		extended_valid_elements : "iframe[src|width|height|name|align|style|scrolling|frameborder|allowtransparency],script[type|language|src]",
		apply_source_formatting : true,
		cleanup_on_startup : true,
		content_css : '{$content_css}',
		branding: false,
		
		{if $fileBased}
			setup: function(editor) {
				geoDesignManage.initEditorButtons(editor, true, {if $restoreDefault}true{else}false{/if});
			}
		{/if}
		
	});
	localStorage.tinyMCE = 'on';
	return true;
};
</script>
<!-- tinyMCE -->