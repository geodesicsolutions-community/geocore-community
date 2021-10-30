// 6.0.7-3-gce41f93

var geoBid = {
	inPlaceEditors : [],
	
	init : function () {
		$$('div.lowBrackets').each(function (elem) {
			var lowValue = elem.previous().getValue();
			geoBid.inPlaceEditors[geoBid.inPlaceEditors.length] = new Ajax.InPlaceEditor (elem, 'AJAX.php?controller=bidIncrements&action=editIncrement', {
				cancelControl : 'button',
				size : 5,
				callback : function (form, value) {
					return 'low='+encodeURIComponent(lowValue)+'&newLow='+encodeURIComponent(value);
				},
				onComplete : function (transport, element) {
					if (transport) {
						geoBid.inPlaceEditors.invoke('dispose');
						geoBid.inPlaceEditors = [];
						var message = encodeURIComponent(transport.responseText);
						//alert('message: '+message);
						new Ajax.Updater ('currentBidFieldset', 'AJAX.php?controller=bidIncrements&action=updateTable&message='+message, {
							evalScripts : true
						});
					}
				},
				externalControl : elem.previous().previous()
			});
		});
		
		$$('div.bidIncrements').each(function (elem) {
			var lowValue = elem.previous().getValue();
			geoBid.inPlaceEditors[geoBid.inPlaceEditors.length] = new Ajax.InPlaceEditor (elem, 'AJAX.php?controller=bidIncrements&action=editIncrement', {
				cancelControl : 'button',
				size : 5,
				callback : function (form, value) {
					return 'low='+encodeURIComponent(lowValue)+'&newIncrement='+encodeURIComponent(value);
				},
				externalControl : elem.previous().previous()
			});
		});
		
		$('bracketDeleteCheckAll').observe('click',function () {
			var checked=this.checked;
			$$('input.deleteBracketCheckboxes').each (function (elem){
				elem.checked=checked;
			});
		});
	}
};

Event.observe(window,'load',geoBid.init);
