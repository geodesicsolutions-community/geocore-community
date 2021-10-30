// 7.1beta5-16-g7c7ded4

var regionAdmin = {
	init : function () {
		
		jQuery('#checkAllRegions').click(regionAdmin.checkAllRegionsClick);
		
		jQuery('.massEditButton').click(regionAdmin.massEditButtonClick);
		jQuery('.massDeleteButton').click(regionAdmin.massDeleteButtonClick);
		jQuery('.moveButton').click(regionAdmin.moveButtonClick);
		jQuery('input.use_label_checkbox').click(regionAdmin.isLabeledCheckboxClick)
			.each(function () {regionAdmin.toggleLanguageLabels(jQuery(this));});
		
		jQuery('.enabledButton').click(regionAdmin.enabledClick)
			.removeClass('enabledButton')
			.css({cursor: 'pointer'});
		gjUtil.lightbox.onComplete(regionAdmin.initAjax);
	},
	//This one initializes stuff after ajax stuff happens
	initAjax : function () {
		var thisBox = gjUtil.lightbox.contents();
		
		thisBox.find('.unique_use_bulk_edit').click(function () {
			jQuery('#unique_name_box').toggle(jQuery('.unique_use_bulk_edit:checked').is('.showNameBox'));
		});
		
		thisBox.find('.move_to_type').click(function () {
			jQuery('#moveToIdBox').toggle(jQuery(this).val()=='id');
			jQuery('#moveToBrowseBox').toggle(jQuery(this).val()=='browse');
		});
		
		thisBox.find('.moveBrowseLink').click(function() {
			var formparams = jQuery('#massForm').serializeArray();
			var myhref=jQuery(this).attr('href');
			
			jQuery('#moveToBrowseBox').load(myhref, formparams, regionAdmin.initAjax);
			
			return false;
		});
	},
	
	enabledClick : function () {
		var region_id = jQuery(this).prev().val();
		var formparams = {'region' : region_id, 'auto_save':1};
		jQuery(this).load('index.php?page=region_enabled',formparams);
	},
	
	checkAllRegionsClick : function () {
		jQuery('input.regionCheckbox').prop('checked',jQuery(this).is(':checked'));
	},
	
	massEditButtonClick : function () {
		var formparams = jQuery('#massForm').serialize();
		var postUrl = jQuery('#massForm').attr('action') + '&page=region_edit_bulk';
		
		jQuery(document).gjLightbox('post',postUrl,formparams);
		
		return false;
	},
	
	massDeleteButtonClick : function () {
		var formparams = jQuery('#massForm').serialize();
		var postUrl = jQuery('#massForm').attr('action') + '&page=region_delete';
		
		jQuery(document).gjLightbox('post',postUrl,formparams);
		
		return false;
	},
	
	moveButtonClick : function () {
		var formparams = jQuery('#massForm').serialize();
		var postUrl = jQuery('#massForm').attr('action') + '&page=region_move';
		jQuery(document).gjLightbox('post',postUrl,formparams);
		
		return false;
	},
	
	isLabeledCheckboxClick : function () {
		regionAdmin.toggleLanguageLabels(jQuery(this));
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

jQuery(document).ready(regionAdmin.init);