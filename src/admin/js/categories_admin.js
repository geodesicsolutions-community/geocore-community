// 7.5.3-36-gea36ae7

var catFieldAdmin = {
	init : function () {
		
		jQuery('#checkAllValues').click(catFieldAdmin.checkAllValuesClick);
		
		jQuery('.massEditButton').click(catFieldAdmin.massEditButtonClick);
		jQuery('.massDeleteButton').click(catFieldAdmin.massDeleteButtonClick);
		jQuery('.moveButton').click(catFieldAdmin.moveButtonClick);
		jQuery('.copyButton').click(catFieldAdmin.copyButtonClick);
		jQuery('input.use_label_checkbox').click(catFieldAdmin.isLabeledCheckboxClick)
			.each(function () {catFieldAdmin.toggleLanguageLabels(jQuery(this));});
		
		jQuery('.editCatLink').unbind('.gjLightbox').on('click.gjLightbox', function () {
			jQuery.get(jQuery(this).attr('href'), function (contents) {
				jQuery(document).gjLightbox('open', contents);
				//this is the reason we don't just use lightUpLink... so can run this after loaded
				catFieldAdmin.initAjax();
			}, 'html');
			//make sure the ones that are links or what not don't continue
			return false;
		});;
		
		jQuery('.enabledButton').click(catFieldAdmin.enabledClick)
			.removeClass('enabledButton')
			.css({cursor: 'pointer'});
		gjUtil.lightbox.onComplete(catFieldAdmin.initAjax);
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
			
			jQuery('#moveToBrowseBox').load(myhref, formparams, catFieldAdmin.initAjax);
			
			return false;
		});
		
		thisBox.find('.which_head_html').change(function () {
			var currentVal = jQuery(this).val();
			if (currentVal=='cat' || currentVal=='cat+default') {
				thisBox.find('.head_html').show('fast');
			} else {
				thisBox.find('.head_html').hide('fast');
			}
		});
		thisBox.find('.which_head_html').change();
	},
	
	enabledClick : function () {
		var value_id = jQuery(this).prev().val();
		var formparams = {'value' : value_id, 'auto_save':1};
		jQuery(this).load('index.php?page=category_enabled',formparams);
	},
	
	checkAllValuesClick : function () {
		jQuery('input.valueCheckbox').prop('checked',jQuery(this).is(':checked'));
	},
	
	massEditButtonClick : function () {
		var formparams = jQuery('#massForm').serialize();
		var postUrl = jQuery('#massForm').attr('action') + '&page=category_edit_bulk';
		
		jQuery(document).gjLightbox('post', postUrl, formparams);
		
		return false;
	},
	
	massDeleteButtonClick : function () {
		var formparams = jQuery('#massForm').serialize();
		var postUrl = jQuery('#massForm').attr('action') + '&page=category_delete';
		
		jQuery(document).gjLightbox('post', postUrl, formparams);
		
		return false;
	},
	
	moveButtonClick : function () {
		var formparams = jQuery('#massForm').serialize();
		var postUrl = jQuery('#massForm').attr('action') + '&page=category_move';
		jQuery(document).gjLightbox('post', postUrl, formparams);
		
		return false;
	},
	
	copyButtonClick : function () {
		var formparams = jQuery('#massForm').serialize();
		var postUrl = jQuery('#massForm').attr('action') + '&page=category_copy';
		jQuery(document).gjLightbox('post', postUrl, formparams);
		
		return false;
	},
	
	isLabeledCheckboxClick : function () {
		catFieldAdmin.toggleLanguageLabels(jQuery(this));
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

jQuery(catFieldAdmin.init);