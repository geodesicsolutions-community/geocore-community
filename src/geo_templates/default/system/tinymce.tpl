{* 17.10.0-22-g1a8f4fd *}

<script type="text/javascript">
	//<![CDATA[
	
	gjWysiwyg.loadTiny = function () {
		
		
		tinyMCE.init({
			selector : '.editor',
			extended_valid_elements : "iframe[src|width|height|name|align|style|scrolling|frameborder|allowtransparency]",
			//make it NOT automatically add the <p> around everything...  Comment the line out if it is needed.
			forced_root_block : '',
			
			content_css: '{if $inAdmin}../{/if}{external file="css/wysiwyg.css"}',
			plugins: 'lists,link,paste,table,insertdatetime,code,preview,media,searchreplace,print,directionality,fullscreen,noneditable,visualchars,nonbreaking,emoticons,anchor,textcolor,colorpicker,charmap,searchreplace,hr',
			toolbar: [
			'undo redo | cut copy paste | formatselect fontsizeselect',
			'bold italic underline strikethrough subscript superscript removeformat | alignleft aligncenter alignright alignjustify | outdent indent blockquote',
			'bullist numlist | link charmap hr emoticons | forecolor backcolor'
			],
			menubar: false,
			statusbar: false,
			branding: false
		});
			
		localStorage.tinyMCE = 'on';	
		return true;
	};
	
	{if !$inAdmin}
		//when page is loaded, init the editor
		jQuery(function () {
			
			if (typeof localStorage.tinyMCE == 'undefined' || localStorage.tinyMCE == 'on') {
				gjWysiwyg.loadTiny();
			}
			
		});
	{/if}
	//]]>
</script>
