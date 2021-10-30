//16.09.0-79-gb63e5d8

//JS for the fields to use page

var FieldsManage = {
	init : function () {
		if ($('fieldsToUseForm')) {
			$('fieldsToUseForm').observe('submit', FieldsManage.fieldsToUseFormSubmit);
		}
		
		//watch the select boxes for changes at the top
		$$('select.whatFields_select').each(function (element) {
			element.observe('change', FieldsManage.whatFieldsSelect);
		});
		FieldsManage.whatFieldsSelect();
		
		//watch the "enabled" checkboxes
		$$('input.enableCheckbox').each (function (element) {
			element.observe('click', FieldsManage.enableCheckboxClick);
			//do the initial page load stuff
			FieldsManage.enableCheckboxClick(element);
		});
		
		$$('input.resetButton').each (function (element) {
			element.observe ('click', function (action) {
				//wait for a split second to do this, so that the reset has taken place already
				setTimeout(function () {
					$$('input.enableCheckbox').each (function (elem) {
						FieldsManage.enableCheckboxClick(elem);
					});
					FieldsManage.whatFieldsSelect();
				}, 100);
			});
		});
		
		//observe all the "check all" checkboxes.
		$$('input.checkAll').each(function (element) {
			var watchAction = (element.type=='checkbox') ? 'click' : 'change';
			element.observe(watchAction, FieldsManage.checkAllClick);
			//un-check them starting out
			element.checked = false;
		});
		
		//watch the field type selectors
		$$('select.typeSelector').each(function (element) {
			element.observe('change', FieldsManage.typeChanged);
			
			//watch the other box too
			var otherLabel = $(element.identify()+'_otherLabel');
			
			if (otherLabel) {
				otherLabel.down('input').observe('click',function () {
					FieldsManage.typeChanged(element);
				});
			}
			
			FieldsManage.typeChanged(element);
		});
	},
	
	whatFieldsSelect : function (event) {
		
		var cat=$('what_fields_to_use_category');
		var group=$('what_fields_to_use_group');
		
		if (!cat && !group) {
			//this is site-wide settings, nothing to show/hide
			
			return;
		}
		
		var catOwn = true;
		var groupOwn = true;
		
		if (cat && cat.getValue() !== 'own') {
			catOwn = false;
		}
		if (group && group.getValue() !== 'own') {
			groupOwn = false;
		}
		
		var allOwn = (catOwn && groupOwn)? true : false;
		
		if (!allOwn) {
			var explain = '';
			if (!groupOwn) {
				explain += 'User Group using: <span class="color-primary-one">Site-Wide settings</span>';
				if (!catOwn) {
					explain += '<br />';
				}
			}
			if (!catOwn) {
				explain += 'Category using: <span class="color-primary-one">';
				if (cat.getValue() == 'parent') {
					explain += 'Parent Category\'s settings';
				} else {
					explain += 'Site-Wide settings';
				}
				explain += '</span>';
			}
			$$('span.explainSpan').each (function (explainSpan) {
				explainSpan.update(explain);
			});
		}
		
		$('fieldsToUse_settings')[allOwn? 'show' : 'hide']();
		
		$('fieldsToUse_off')[allOwn? 'hide' : 'show']();
	},
	
	fieldsToUseFormSubmit : function (event) {
		//we will be manually submitting!
		event.stop();
		if ($('auto_save').getValue()==2) {
			//form already submitted, do nothing
			
			return;
		}
		
		//remember that the form submitted to prevent clicking on save multiple times
		$('auto_save').setValue('2');
		this.select('input.saveFieldsButton').each (function (element) {
			//change the text on them
			element.setValue('Saving, please wait...')
				.disable();
		});
		
		//un-disable all input elements - this way any disabled
		//for elements get re-enabled!
		this.select('input:disabled','select:disabled').each(function (element) {
			if (!element.hasClassName('saveFieldsButton')) {
				element.enable();
			}
		});
		
		this.submit();
	},
	
	typeChanged : function (event) {
		//figure out if this is from an action or the initial page load
		var element = (typeof event.stop == 'function')? this : event;
		
		var typeId = element.identify();
		var thisVal = element.getValue();
		
		var showLength = (thisVal=='text' || thisVal=='textarea' 
			|| thisVal=='number' ||thisVal=='cost');
		var showOther = (!showLength && thisVal != 'date');
		
		var otherLabel = $(typeId+'_otherLabel');
		
		var usingOther = false;
		
		if (otherLabel) {
			var checkOther = otherLabel.down('input');
			if (showOther) {
				otherLabel.removeClassName('disabled');
				
				if (checkOther) {
					checkOther.disabled = false;
					usingOther = checkOther.checked;
				}
			} else {
				otherLabel.addClassName('disabled');
				if (checkOther) {
					checkOther.disabled = true;
					checkOther.checked = false;
				}
			}
		}
		
		//show/hide field length based on if it should be or not
		var fieldLength = $(typeId+'_fieldLength');
		if (fieldLength) {
			var beforeVis = fieldLength.visible();
			
			fieldLength[(showLength||usingOther)?'show':'hide']();
			$(typeId+'_fieldLengthBlank')[(showLength||usingOther)? 'hide':'show']();
			if (!beforeVis && fieldLength.visible() && (fieldLength.value=='0'||fieldLength.value=='')) {
				//if switching from invisible to visible, and value is 0, change
				//value to default of 256.
				fieldLength.value=256;
			}
		}
		
	},
	
	checkAllClick : function (event) {
		//The checkboxes to check/uncheck will have a class name the same as the
		//ID of the checkall input clicked, plus _input
		var className = this.identify()+'_input:enabled';
		
		var isChecked = this.type=='checkbox'? this.checked: this.getValue();
		
		$$('input.'+className, 'select.'+className).each(function (element){
			element.checked=isChecked;
			if (element.hasClassName('enableCheckbox')) {
				FieldsManage.enableCheckboxClick(element);
			} else if (element.hasClassName('typeSelector')) {
				FieldsManage.typeChanged(element);
			}
		});
	},
	
	enableCheckboxClick : function (event) {
		//figure out if this is from an action or the initial page load
		var element = (typeof event.stop == 'function')? this : event;
		FieldsManage.enableCheckboxSub(element);
		
		//Also go on over to the display tab and make those disabled
		$$('input.'+element.identify()+'_displayLocations').each(function(dispElement) {
			dispElement.checked=element.checked;
			FieldsManage.enableCheckboxSub(dispElement);
		});
	},
	
	enableCheckboxSub : function (element) {
		var currentRow = element.up().up();
		
		var isChecked = element.checked;
		currentRow.select('td:not(.enabledCheckboxColumn)').each(function(tdElement){
			//if checked:  remove class 'disabled', if not checked, add class 'disabled'
			tdElement[isChecked? 'removeClassName' : 'addClassName']('disabled')
				.select('input','select').each(function (inputElement){
					//if checked, enable element, if not checked, disable element
					inputElement[isChecked? 'enable':'disable']();
				});
		});
		if (isChecked) {
			//we should re-hide stuff if needed, like "other" boxes
			currentRow.select('.typeSelector').each(function(typeElement) {
				FieldsManage.typeChanged(typeElement);
			});
		}
	}
};

Event.observe(window,'load',FieldsManage.init);
