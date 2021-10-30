// 7.4.0-10-g049fbf6

var addonNavigation = {
	init : function () {
		jQuery('.addonNavigation_regionSelect').change(function(){addonNavigation.selectObserve(this);});
		jQuery('.geographic_navigation_changeLink').click(function(event) {addonNavigation.changeRegionClick(this,event);});
	},

	//////////////////////////////////////////////////////////////
	//used for populating dropdowns in the "search box 1" module//
	//////////////////////////////////////////////////////////////
	selectObserve : function (element) {
		var je = jQuery(element);
		if (je.val() == 0 && je.prev().length) {
			//selected "select one" option on a level other than the top one
			//pretend they just did the previous one, otherwise all of them get cleared.
			je = je.prev();
		}
		jQuery.post('AJAX.php?controller=addon_geographic_navigation&action=selectRegion',
				{
					region_id: je.val(),
					fieldName: je.prop('name')
				},
				function(returned) {
					je.parent().html(returned);
				},
				'html'
		);

	},
	
	///////////////////////////////////////////////////////////////////////
	//everything below here: used for "change_region_link" popup selector//
	///////////////////////////////////////////////////////////////////////
	
	navChangeBox : null,
	navTriggeringElement: null,
	triggerTop: 0,
	triggerLeft: 0,
	getParams : {},
	
	//stuff starts here
	changeRegionClick : function (element,event) {
		var je = jQuery(element);
		event.preventDefault();
		
		//replace triggering element with a box to display stuff in
		addonNavigation.navChangeBox = jQuery("<div id='geoNavChangeBox' style='display: none;'>Loading...</div>");
		
		addonNavigation.navTriggeringElement = je; //save the triggering element for use later
		//save posistion of triggering element
		var offsets = je.offset();    
	    addonNavigation.triggerTop = offsets.top;
	    addonNavigation.triggerLeft = offsets.left;
	    
		//hide triggering element and add selector box in its place
	    //note: we put the box at the end of <body> and then give it the absolute position of the trigger.
	    //		That way, it's not affected by / doesn't affect surrounding elements and can just sit on top of everything
	    je.css('visibility','hidden'); //use visibility: hidden instead of hide() because we want the trigger to still occupy (vertical) space on the page
	    jQuery("body").append(addonNavigation.navChangeBox);
	    addonNavigation.absolutizeNavBox();
	    addonNavigation.navChangeBox.show();
		
		//get new box contents
		jQuery.post('AJAX.php?controller=addon_geographic_navigation&action=chooseRegionBox',
				addonNavigation.getParams,
				function(returned) {
					addonNavigation.handleChangeBox(returned);
				},
				'html'
		);
	},
	
	absolutizeNavBox : function () {
		
		    var box = jQuery(addonNavigation.navChangeBox);
		    
		    box.css("position", "absolute");
		    box.css("top", addonNavigation.triggerTop + 'px');
		    
		    var newLeft = addonNavigation.triggerLeft;
		    //make sure it doesn't go off the right edge of the window
		    //note: IE10+ overlays the page scroll bar instead of embedding it in the window, so it still may cover part of the box
		    var windowWidth = jQuery(window).width();
		    var boxWidth = box.width();
		    if(addonNavigation.triggerLeft + boxWidth > windowWidth) {
		    	newLeft = windowWidth - boxWidth;
		    	newLeft -= 40; //give it a bit more padding, for good measure
		    }
		    box.css("left", newLeft + 'px');
	},
	
	//separate function for updating the box since it's done in a couple different places
	handleChangeBox: function (boxContents) {
		//update box contents and set observers on its controls
		addonNavigation.navChangeBox.html(boxContents);
		addonNavigation.absolutizeNavBox();
		jQuery('.chooseNavCancel').click(addonNavigation.closeChangeBox);
		jQuery('.narrowRegionLink').click(function(){addonNavigation.changeChooseRegion(this);}).dblclick(function(event) {addonNavigation.changeDblClick(this,event);});
		jQuery('.narrowRegionSelect').change(function(){addonNavigation.changeChooseRegion(this);});
	},
	
	//kill the popup box
	closeChangeBox : function () {
		//remove the nav box
		addonNavigation.navChangeBox.remove();
		//show the original trigger
		addonNavigation.navTriggeringElement.css('visibility',''); //intentionally not using hide()/show() for this
	},
	
	//user selected a region -- show a new box (containing the region's children along with buttons or links to set it as the active region)
	changeChooseRegion : function (element) {
		var je = jQuery(element);
		var regionId = 0;
		
		if (je.hasClass('narrowRegionLink')) {
			//triggered from a link
			regionId = je.next().val();
		} else {
			//triggered from a select
			regionId = je.val();
		}
		//add user-selected region to parameters to send to ajax to get the modified box
		var params = addonNavigation.getParams;
		params.region = regionId;
		
		//get new box contents according to selected region
		jQuery.post('AJAX.php?controller=addon_geographic_navigation&action=chooseRegionBox',
				params,
				function(returned) {
					addonNavigation.handleChangeBox(returned);
				},
				'html'
		);
	},
	
	//user double-clicked a final link. go directly to that chosen region
	changeDblClick : function (element, event) {
		event.preventDefault();
		addonNavigation.closeChangeBox();
		window.location = jQuery(element).next().next().val();
	}
};

//Make it so.
jQuery(document).ready(addonNavigation.init);