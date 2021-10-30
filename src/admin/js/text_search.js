// 7.4beta1-74-gfceff14


var geoSearch = {
	init : function () {
		$('text_search_form').observe('submit', geoSearch.searchFormSubmit);
		
		//let tabs know about stuff
		jQuery('#textTab').gjTabs('onActive', geoSearch.textTabClicked);
		jQuery('#filenameTab').gjTabs('onActive', geoSearch.filenameTabClicked);
		jQuery('#contentTab').gjTabs('onActive', geoSearch.contentTabClicked);
		jQuery('#addonTab').gjTabs('onActive', geoSearch.addonTabClicked);
		
		var cookieQuery = geoUtil.getCookie('admin_last_text_search');
		if (!$('text_query').getValue() && cookieQuery) {
			$('text_query').setValue(cookieQuery);
		}
		
		if ($('text_query').getValue()) {
			//something filled in for the query, so automatically show it
			geoSearch.textTabClicked();
			geoSearch.updateQueryLink();
			$('searchResultsBox').show();
		}
		$('text_search_form').getElements().each (function (elem) {
			elem.observe('change', geoSearch.updateQueryLink);
		});
	},
	
	updateQueryLink : function () {
		var params = $('text_search_form').serialize().replace('&auto_save=1','');
		var url = location.protocol+'//'+location.hostname+location.pathname+'?page=text_search&'+params;
		
		$('permaLink').update(url);
		$('permaLinkBox')[$('text_query').getValue() ? 'show' : 'hide']();
	},
	
	searchFormSubmit : function (action) {
		action.stop();
		
		if ($('filenameTab').hasClassName('activeTab')) {
			//filename tab active
			geoSearch.filenameTabClicked();
		} else if ($('contentTab').hasClassName('activeTab')) {
			//content tab active
			geoSearch.contentTabClicked();
		} else {
			//default to text tab active
			geoSearch.textTabClicked();
		}
		
		//save search in a cookie
		if ($('text_query').getValue()) {
			document.cookie = "admin_last_text_search=" + escape($('text_query').getValue()) + "; path=/";
		}
		
		$('searchResultsBox').show();
	},
	
	tabClicked : function (id) {
		var params = $('text_search_form').serialize(true);
		
		$('loadingBox').show();
		//run an ajax request
		new Ajax.Updater(id, 'index.php?page=text_search', {
			parameters : params,
			onComplete : function () {
				if ($(id).visible()) {
					//hide the loading thingy
					$('loadingBox').hide();
				}
			}
		});
	},
	
	textTabClicked : function () {
		$('searchType').setValue('text');
		geoSearch.tabClicked('textTabContents');
	},
	
	addonTabClicked : function () {
		$('searchType').setValue('addon');
		geoSearch.tabClicked('addonTabContents');
	},
	
	filenameTabClicked : function () {
		$('searchType').setValue('filename');
		geoSearch.tabClicked('filenameTabContents');
	},
	
	contentTabClicked : function () {
		$('searchType').setValue('content');
		geoSearch.tabClicked('contentTabContents');
	}
};

Event.observe(window, 'load', function () {
	geoSearch.init();
});
