// 6.0.7-3-gce41f93

//does stuff for calendar for discount codes.

Event.observe(window,'load',function (){
	Calendar.setup({
		dateField : 'startDate',
		triggerElement : 'startDateCalButton'
	});
	
	Calendar.setup({
		dateField : 'endDate',
		triggerElement : 'endDateCalButton'
	});
	$('isGroupSpecificCheck').observe('click',discountsGroupCheck);
	//run it now to begin with
	discountsGroupCheck();
});

var discountsGroupCheck = function () {
	$('groupSelect')[($('isGroupSpecificCheck').checked)? 'show' : 'hide']();
}
