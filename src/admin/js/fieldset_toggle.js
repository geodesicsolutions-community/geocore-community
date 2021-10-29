
var geoFieldset = {
	fieldsetAnimation : false,
	maximizedLegendBackground : "url('admin_images/design/admin_arrow_up.gif')",
	minimizedLegendBackground : "url('admin_images/design/admin_arrow_down.gif')",
	
	init : function () {
		//gives ability to toggle fieldsets to be hidden or shown
		$$('legend').each(function (elem) {
			
			var startClosed = elem.hasClassName('startClosed');
			elem.observe('click',geoFieldset.legendClick);
			elem.setStyle({
				backgroundImage: (startClosed)? geoFieldset.minimizedLegendBackground : geoFieldset.maximizedLegendBackground,
				backgroundPosition: 'right center',
				backgroundRepeat: 'no-repeat',
				cursor: 'pointer'
				});
			var contents = geoFieldset.wrapFieldset(elem);
			if (startClosed) contents.hide();
		});
		
		
		//load tiny mce AFTER this is done, or things get jacked up
		if(localStorage.tinyMCE == 'on') {
			gjWysiwyg.loadTiny();
		}
		
	},
	
	legendClick : function (event) {
		if (geoFieldset.fieldsetAnimation) return;
		geoFieldset.fieldsetAnimation = 1;
		
		var fieldsetContents = geoFieldset.wrapFieldset(this);
		
		if (fieldsetContents){
			this.setStyle({backgroundImage: (fieldsetContents.visible())? geoFieldset.minimizedLegendBackground: geoFieldset.maximizedLegendBackground});
			
			new Effect.toggle(fieldsetContents,'slide',{duration:defaultDuration});
		}
		var timeout = defaultDuration * 1000;
		setTimeout('geoFieldset.fieldsetAnimation=0;',timeout);
	},
	wrapFieldset : function (elem) {
		var fieldsetContents = $(elem.identify() + 'Contents');
		if (!fieldsetContents){
			//wrap the contents in a div
			var wrapper = new Element('div',{'class': 'fieldsetContents', 'id': elem.identify()+'Contents'});
			fieldsetContents = Element.wrap(elem.next(),wrapper);
			elem = elem.next();
			while (elem && elem.next()) {
				//add the element to the contents
				fieldsetContents.insert({after: elem.next()});
				elem = elem.next();
			}
		}
		return fieldsetContents;
	}
};

//initialize fieldset hide/show on window load
Event.observe(window,'load',geoFieldset.init);


