
Object.extend(Hash.prototype, {
	implode: function( glue ) {
		return this.toQueryString();
		//return (this.constructor == Hash) ? this.values().implode( glue ) : this;
	}
});

var geoExport = {
	init : function () {
		$$('.exportButton').each(function (elem) {
			elem.observe('click', function (action) {
				//export button just submits the form normally.
				$('exportForm').submit();
				action.stop();
			});
		});
		
		$('saveButton').observe('click', function (action) {
			action.stop();
			//save button uses ajax to submit save form
			var url = 'AJAX.php?controller=addon_exporter&action=saveSettings';
			
			var params = jQuery('#exportForm').serialize();
			
			new Ajax.Request(url, {
				method: 'post',
				parameters : params,
				onComplete: geoExport.saveComplete
			});
			
		});
		
		
		//make load check-all work
		$('checkAllLoad').observe('click', function (action) {
			var is_checked = this.checked;
			$$('input.deleteLoadCheckbox').each(function (elem) {
				elem.checked = is_checked;
			});
		});
		
		geoExport.reInit();
		//initialize calendars for date ranges and such
		geoExport.initCalendars();
	},
	
	reInit : function () {
		//do stuff that needs to be done when contents may have been changed
		//watch the delete button
		if ($('submitDelete')) {
			$('submitDelete').observe('click', geoExport.deleteSettingsClicked);
		}
		//watch the load buttons for clicks
		$$('.loadButtons').each(function (elem) {
			//in case it is already being watched once
			elem.stopObserving();
			//watch it for clicks
			elem.observe('click', geoExport.loadSettingsClicked);
		});
		
		$$('.exportTypeRadio').each (function (elem) {
			elem.stopObserving();
			elem.observe('click', function () {
				$('filenameExtension').update('.'+this.getValue());
			});
			if (elem.checked) {
				$('filenameExtension').update('.'+elem.getValue());
			}
		});
	},
	
	initCalendars : function () {
		$$('input.dateInput').each(function (elem) {
			geoExport.initCal(elem.identify());
		});
		
	},
	initCal : function (inputField) {
		Calendar.setup({
			dateField : inputField,
			triggerElement : inputField+'CalButton'
		});
		$(inputField+'CalButton').setStyle({cursor: 'pointer'});
	},
	
	saveComplete : function (transport) {
		data = transport.responseJSON;
		
		geoExport.handleCommonResponse(data);
		
		if (data.name_exists) {
			//name exists html, show the box in lightbox
			jQuery(document).gjLightbox('open',data.name_exists);
			$('saveButton_force').observe('click', function (action) {
				action.stop();
				//save button uses ajax to submit save form
				var url = 'AJAX.php?controller=addon_exporter&action=saveSettings';
				
				var params = $('exportForm').serialize(true);
				params.force_save = 1;
				new Ajax.Request(url, {
					method: 'post',
					parameters : params,
					onComplete: geoExport.saveComplete
				});
				//close the confirmation box
				jQuery(document).gjLightbox('close');
			});
		}
		
		return;
	},
	
	loadSettingsClicked : function (action) {
		action.stop();
		var loadname = this.previous().getValue();
		
		var url = 'AJAX.php?controller=addon_exporter&action=loadSettings';
		
		new Ajax.Request(url, {
			method: 'post',
			parameters : {
				name : loadname
			},
			onComplete: geoExport.loadSettingsComplete
		});
	},
	
	deleteSettingsClicked : function (action) {
		
	},
	
	loadSettingsComplete : function (transport) {
		var data = transport.responseJSON;
		
		if (!geoExport.handleCommonResponse(data)) {
			//don't attempt to load settings if problems
			return;
		}
		
		if (!data.settings) {
			gjUtil.addError('Problem retrieving data to load settings!');
			return;
		}
		
		var settings = data.settings;
		
		//loop through all elements in the form and set each one
		$('exportForm').getElements().each (function (elem) {
			if (!elem.name) {
				//element is without name, probably submit or button or something
				return;
			}
			var elemName = elem.name;
			if (elemName == 'auto_save' || elem.hasClassName('deleteLoadCheckbox')) {
				//not to be changed
				return;
			}
			
			if (elem.type=='text') {
				//text field
				elem.value = settings[elemName];
			} else if (elem.type=='radio') {
				elem.checked = (elem.value==settings[elemName]);
			} else if (elem.type=='checkbox') {
				elem.checked = (settings[elemName]);
			} else {
				//select box...  tricky business...
				if (!settings[elemName]) {
					//most likely nothing selected?  Shouldn't happen...
					
					child = elem.down();
					while (child) {
						child.selected = false;
						child = child.next();
					}
				} else {
					child = elem.down();
					
					while (child) {
						//go through each "option" in a select box and see if it should
						//be selected or not.
						child.selected = (settings[elemName].indexOf(child.value) !== -1);
						child = child.next();
					}
				}
			}
		});
		
		gjUtil.addMessage('Finished loading settings!');
	},
	
	deleteSettingsClicked : function (action) {
		action.stop();
		//save button uses ajax to submit save form
		var url = 'AJAX.php?controller=addon_exporter&action=deleteSettings';
		
		var params = jQuery('#exportForm').serialize();
		new Ajax.Request(url, {
			method: 'post',
			parameters : params,
			onComplete: geoExport.deleteComplete
		});
	},
	
	deleteComplete : function (transport) {
		var data = transport.responseJSON;
		
		geoExport.handleCommonResponse(data);
	},
	
	handleCommonResponse : function (data) {
		if (data.error) {
			gjUtil.addError(data.error);
			return false;
		}
		
		if (data.message) {
			gjUtil.addMessage(data.message);
		}
		if (data.load_table) {
			$('loadTable').update(data.load_table);
			geoExport.reInit();
		}
		return true;
	}
};

Event.observe(window,'load',geoExport.init);
