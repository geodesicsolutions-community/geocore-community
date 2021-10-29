//7.1.2-38-gb5497b1

jQuery(document).ready (function () {
	var combineOptionClick = function () {
		var stepOptions = jQuery(this).closest('div').find('.selectedSteps');
		if (jQuery(this).val()=='selected') {
			stepOptions.show('fast');
		} else {
			stepOptions.hide('fast');
		}
	};
	jQuery('input.combineSetting').click(combineOptionClick);
	//go ahead and run it now to show the stuff
	jQuery('input.combineSetting:checked').each(combineOptionClick);
	
	jQuery('.combined_checkbox').click(function () {
		var combinedList = jQuery(this).closest('ul').find('.combined_checkbox');
		
		//keep track of what it starts as
		var startingCheck = jQuery(this).prop('checked');
		
		//find the first checked
		var start=null, end=null;
		combinedList.each(function () {
			if (jQuery(this).is(':checked')) {
				var name = jQuery(this).attr('name');
				if (start==null) {
					start = name;
				}
				end = name;
			}
		});
		if (start==end) {
			//start and end the same, nothing will be in between
			return;
		}
		//now this time go through and make sure everything from start to end is checked
		var started=false,ended=false;
		combinedList.each(function(){
			if (jQuery(this).attr('name')==start) {
				started=true;
			}
			if (jQuery(this).attr('name')==end) {
				ended=true;
			}
			if (started && !ended) {
				jQuery(this).prop('checked',true);
			}
		});
		if (startingCheck != jQuery(this).prop('checked')) {
			//must have checked this one...
			var warning = jQuery(this).closest('div').find('p.page_note');
			for(var i=0;i<2;i++) {
				warning.fadeTo('fast', 0.5).fadeTo('fast', 1.0);
			}
		}
	});
});