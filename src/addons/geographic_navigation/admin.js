// 7.5.3-36-gea36ae7

var addonGeographic = {
	
		
	initRegions : function () {
		$$('span.geographicAddonExpand_').each(addonGeographic.initButton);
		
		//observe checkboxes
		$$('.regionCheckBox_').each(addonGeographic.initChecks);
		
		//observe forms
		$$('form.newRegionForm_').each (addonGeographic.initNewRegionForm);
		
		$$('form.editRegionForm_').each (addonGeographic.initEditRegionForm);
		
		$$('form.deleteRegionForm_').each (addonGeographic.initDeleteRegionForm);
		
		
		
		//observe buttons
		$$('input.editRegionButton_').each(addonGeographic.initEditButton);
		
		$$('input.cancelRegionButton_').each(addonGeographic.initCancelButton);
		
		//start sortables
		var regions = $$('ul.isRegion_');
		//have to reverse them because the childs needs to be first
		regions.reverse();
		regions.each(addonGeographic.initSortable);
	},
	
	initButton : function (element) {
		//fix the class name
		element.removeClassName('geographicAddonExpand_')
			.addClassName('geographicAddonExpand');
		//add observer for clicky
		element.observe('click', addonGeographic.buttonClick);
	},
	
	initChecks : function (element) {
		element.removeClassName('regionCheckBox_')
			.addClassName('regionCheckBox');
		
		addonGeographic.updateChecked(element);
		element.observe('click', addonGeographic.checkClicked);
	},
	
	buttonClick : function () {
		if (!this.up() || !this.up().next()) {
			//could not find element to show
			return;
		}
		var child = this.up().next();
		
		if (child) {
			child.toggle();
			
			if (child.visible()) {
				if (child.hasClassName('notRetrieved')) {
					//remove class name
					child.removeClassName('notRetrieved');
					
					//now see if child is state or another level
					if (child.hasClassName('isCountry')) {
						//it's a country, populate it with states
						new Ajax.Updater(child, 'AJAX.php?controller=addon_geographic_navigation&action=getStatesFor',{
							parameters: {country_id: child.identify()},
							evalScripts : true
						});
					} else if (child.hasClassName('isState')) {
						//it's a state, populate it with addon regions
						new Ajax.Updater(child, 'AJAX.php?controller=addon_geographic_navigation&action=getRegionsFor',{
							parameters: {state_id: child.identify()},
							evalScripts : true
						});
					} else {
						//it's an addon region, populate it with child regions
						new Ajax.Updater(child, 'AJAX.php?controller=addon_geographic_navigation&action=getRegionsFor',{
							parameters: {region_id: child.identify()},
							evalScripts : true
						});
					}
				}
				addonGeographic.changeToMinus(this);
			} else {
				addonGeographic.changeToPlus(this);
			}
		}
	},
	
	checkClicked : function () {
		addonGeographic.updateChecked(this);
		var url='AJAX.php?controller=addon_geographic_navigation&action=updateChecked';
		var rtype = '';
		if (this.hasClassName('isCountry')) {
			rtype = 'country';
		} else if (this.hasClassName('isState')) {
			rtype = 'state';
		}
		//submit
		
		new Ajax.Request( url,{
			method: 'post',
			parameters : {
				region_id : this.value,
				checked : (this.checked)?1:0,
				type : rtype
			}
		});
	},
	
	initNewRegionForm : function (element) {
		element.removeClassName('newRegionForm_')
			.addClassName('newRegionForm');
		
		element.observe('submit', addonGeographic.newRegionFormSubmit);
	},
	
	newRegionFormSubmit : function (action) {
		//stop the submit
		action.stop();
		
		var updateBox = this.up().up().up().up().up();
		
		//do an ajax call
		this.request({
			onComplete: function (transport) {
				updateBox.update(transport.responseText);
			}
		});
	},
	
	initEditRegionForm : function (element) {
		element.removeClassName('editRegionForm_')
			.addClassName('editRegionForm');
		
		element.observe('submit', addonGeographic.editRegionFormSubmit);
	},
	
	editRegionFormSubmit : function (action) {
		//stop the submit
		action.stop();
		
		var updateBox = this.up().up().up().up().up();
		
		//do an ajax call
		this.request({
			onComplete: function (transport) {
				updateBox.update(transport.responseText);
			}
		});
	},
	
	initDeleteRegionForm : function (element) {
		element.removeClassName('deleteRegionForm_')
			.addClassName('deleteRegionForm');
		
		element.observe('submit', addonGeographic.deleteRegionFormSubmit);
	},
	
	deleteRegionFormSubmit : function (action) {
		//stop the submit
		action.stop();
		var warning = "Are you sure?  This will also delete all regions below this one.";
		if (!confirm(warning)) {
			return;
		}
		var updateBox = this.up().up().up().up().up();
		
		//do an ajax call
		this.request({
			onComplete: function (transport) {
				updateBox.update(transport.responseText);
			}
		});
	},
	
	initEditButton : function (element) {
		element.removeClassName('editRegionButton_')
			.addClassName('editRegionButton');
		
		element.observe('click',addonGeographic.editClick);
	},
	
	editClick : function (action) {
		action.stop();
		this.up().hide();
		this.up().next().show();
	},
	
	initCancelButton : function (element) {
		element.removeClassName('cancelRegionButton_')
			.addClassName('cancelRegionButton');
		
		element.observe('click',addonGeographic.cancelClick);
	},
	
	cancelClick : function (action) {
		action.stop();
		this.up().up().hide();
		this.up().up().previous().show();
	},
	
	initSortable : function (element) {
		element.removeClassName('isRegion_')
			.addClassName('isRegion');
		
		Sortable.create(element, {
			handle : 'regionNameHook',
			only : 'regionMovable',
			onUpdate : addonGeographic.changeOrder
		});
	},
	
	updateChecked : function (element) {
		var child = element.up().up().next();
		var expandButton = element.up().previous();
		
		if (element.checked) {
			expandButton.show();
		} else {
			child.hide();
			addonGeographic.changeToPlus(expandButton);
			expandButton.hide();
		}
	},
	
	changeToPlus : function (element) {
		element = $(element);
		element.select('.minus')[0].hide();
		element.select('.plus')[0].show();
	},
	changeToMinus : function (element) {
		element = $(element);
		element.select('.plus')[0].hide();
		element.select('.minus')[0].show();
	},
	
	changeOrder : function (element) {
		new Ajax.Request("AJAX.php?controller=addon_geographic_navigation&action=sortRegions", {
			method: 'post',
			parameters: {'regions': Sortable.serialize(element.identify())}
		});
	}
		
};

Event.observe(window, 'load', function () {
	addonGeographic.initRegions();
});
