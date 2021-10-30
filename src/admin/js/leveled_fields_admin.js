// 7.1beta2-159-g657f89c

var levFieldAdmin = {
	init : function () {
		
		jQuery('#checkAllValues').click(levFieldAdmin.checkAllValuesClick);
		
		jQuery('.massEditButton').click(levFieldAdmin.massEditButtonClick);
		jQuery('.massDeleteButton').click(levFieldAdmin.massDeleteButtonClick);
		jQuery('.moveButton').click(levFieldAdmin.moveButtonClick);
		jQuery('.copyButton').click(levFieldAdmin.copyButtonClick);
		jQuery('input.use_label_checkbox').click(levFieldAdmin.isLabeledCheckboxClick)
			.each(function () {levFieldAdmin.toggleLanguageLabels(jQuery(this));});
		
		jQuery('.enabledButton').click(levFieldAdmin.enabledClick)
			.removeClass('enabledButton')
			.css({cursor: 'pointer'});
		gjUtil.lightbox.onComplete(levFieldAdmin.initAjax);
	},
	//This one initializes stuff after ajax stuff happens
	initAjax : function () {
		var thisBox = gjUtil.lightbox.contents();
		if (!thisBox) {
			//could not get contents, nothing to do
			return;
		}
		
		thisBox.find('.unique_use_bulk_edit').click(function () {
			jQuery('#unique_name_box').toggle(jQuery('#unique_name_box').is('.showNameBox'));
		});
		
		thisBox.find('.move_to_type').click(function () {
			jQuery('#moveToIdBox').toggle(jQuery(this).val()=='id');
			jQuery('#moveToBrowseBox').toggle(jQuery(this).val()=='browse');
		});
		
		thisBox.find('.moveBrowseLink').click(function() {
			var formparams = jQuery('#massForm').serializeArray();
			var myhref=jQuery(this).attr('href');
			
			jQuery('#moveToBrowseBox').load(myhref, formparams, levFieldAdmin.initAjax);
			
			return false;
		});
	},
	
	enabledClick : function () {
		var value_id = jQuery(this).prev().val();
		var formparams = {'value' : value_id, 'auto_save':1};
		jQuery(this).load('index.php?page=leveled_field_value_enabled',formparams);
	},
	
	checkAllValuesClick : function () {
		jQuery('input.valueCheckbox').prop('checked',jQuery(this).is(':checked'));
	},
	
	massEditButtonClick : function () {
		var formparams = jQuery('#massForm').serialize();
		var postUrl = jQuery('#massForm').attr('action') + '&page=leveled_field_value_edit_bulk';
		
		jQuery(document).gjLightbox('post', postUrl, formparams);
		
		return false;
	},
	
	massDeleteButtonClick : function () {
		var formparams = jQuery('#massForm').serialize();
		var postUrl = jQuery('#massForm').attr('action') + '&page=leveled_field_value_delete';
		
		jQuery(document).gjLightbox('post', postUrl, formparams);
		
		return false;
	},
	
	moveButtonClick : function () {
		var formparams = jQuery('#massForm').serialize();
		var postUrl = jQuery('#massForm').attr('action') + '&page=leveled_field_value_move';
		jQuery(document).gjLightbox('post', postUrl, formparams);
		
		return false;
	},
	
	copyButtonClick : function () {
		var formparams = jQuery('#massForm').serialize();
		var postUrl = jQuery('#massForm').attr('action') + '&page=leveled_field_value_copy';
		jQuery(document).gjLightbox('post', postUrl, formparams);
		
		return false;
	},
	
	isLabeledCheckboxClick : function () {
		levFieldAdmin.toggleLanguageLabels(jQuery(this));
	},
	
	toggleLanguageLabels : function (checkbox) {
		var checked = checkbox.prop('checked');
		
		var thisTd = checkbox.closest('td').next();
		//walk the table, enable/disable each text box according to if labeled is checked
		
		while (thisTd && thisTd.find('input').length) {
			thisTd.find('input:first').prop('disabled', !checked);
			thisTd = thisTd.next();
		}
	}
};

jQuery(document).ready(levFieldAdmin.init);