{* 7.4beta1-61-gf4fc972 *}

<script>
jQuery(function () {
	//set defaults
	geoVidProcess.currentSlot = {$currentSlot};
	
	geoVidProcess.ajaxUrl = "{if $in_admin}../{/if}AJAX.php";

	geoVidProcess.adminId = '{$adminId}';

	geoVidProcess.userId = '{$userId}';

	geoVidProcess.text = {ldelim}
		addButton : '{$messages.500925|escape_js}',
		editButton : '{$messages.500926|escape_js}'
	};

	//initialize external video js
	geoVidProcess.init();
});
</script>