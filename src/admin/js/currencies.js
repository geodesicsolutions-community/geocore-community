// 6.0.7-3-gce41f93

var geoCurrency = {
	inPlaceEditors : [],
	
	init : function () {
		$$('div.currency_edit').each(function (elem) {
			var inPlace = new Ajax.InPlaceEditor(elem, 'index.php?page=listing_currency_types_edit&json=1', {
				cancelControl : 'button',
				callback: function(form, value) { return 'auto_save=1&value='+encodeURIComponent(value); },
				onComplete : geoCurrency.editComplete,
				externalControl : elem.previous().previous()
			});
			//use alternate method for getting text to edit, so it gets html entities and all
			Object.extend(inPlace, {
				getText : geoCurrency.overload.getTextNormal
			});
		});
		
		$$('div.sb_currency_edit').each(function (elem) {
			var inPlace = new Ajax.InPlaceEditor(elem, 'index.php?page=listing_currency_types_edit&sb=1', {
				cancelControl : 'button',
				callback: function(form, value) { return 'auto_save=1&value='+encodeURIComponent(value); },
				onComplete : geoCurrency.editComplete,
				externalControl : elem.previous().previous()
			});
			//use alternate method for getting input
			Object.extend(inPlace, {
				createEditField : geoCurrency.overload.createEditFieldSB
			});
		});
	},
	editComplete : function (transport, element) {
		if (transport) {
			var data = transport.responseJSON;
			
			if (data.error) {
				geoUtil.addError(data.error);
			} else if (data.message) {
				geoUtil.addMessage(data.message);
			}
			
			if (data.refresh||data.session_error) {
				setTimeout(geoUtil.refreshPage, 4000);
			}
			var new_value;
			if (data.sb) {
				new_value = data.value_display;
				
				var new_select = new Element('select').hide();
				
				var current = element.previous().down();
				while (current) {
					new_select.insert(new Element('option', {
						value: current.value,
						selected: (current.value==data.value)
						}).insert(current.innerHTML));
					
					current = current.next();
				}
				element.previous().replace(new_select);
			} else {
				new_value = (data.value)? data.value:'';
				
				element.previous().value=new_value;
			}
			element.update(new_value);
		}
	},
	sb_click : function () {
		var edit_link = this.down();
		var settings = edit_link.next();
		var text = settings.next();
		
		edit_link.hide();
		settings.show();
		text.hide();
	},
	sb_cancel : function () {
		var edit_link = this.up.previous();
		var settings = edit_link.next();
		var text = settings.next();
		
		edit_link.show();
		settings.hide();
		text.show();
	},
	sb_ok : function () {},
	
	overload : {
		getTextNormal : function () {
			return this.element.previous().value;
		},
		createEditFieldSB: function() {
			var text = (this.options.loadTextURL ? this.options.loadingText : this.getText());
			var fld;
			//instead of creating input, we create select.. based on select a few up..
			fld = new Element('select', {
				name : this.options.paramName,
				className : 'editor_field'
			});
			var hidden_from = this.element.previous();
			fld.update(hidden_from.innerHTML);
			/*
			if (1 >= this.options.rows && !/\r|\n/.test(this.getText())) {
				fld = document.createElement('input');
				fld.type = 'text';
				var size = this.options.size || this.options.cols || 0;
				if (0 < size) fld.size = size;
			} else {
				fld = document.createElement('textarea');
				fld.rows = (1 >= this.options.rows ? this.options.autoRows : this.options.rows);
				fld.cols = this.options.cols || 40;
			}
			fld.name = this.options.paramName;
			fld.value = text; // No HTML breaks conversion anymore
			fld.className = 'editor_field';
			*/
			if (this.options.submitOnBlur)
				fld.onblur = this._boundSubmitHandler;
			this._controls.editor = fld;
			if (this.options.loadTextURL)
				this.loadExternalText();
			this._form.appendChild(this._controls.editor);
		}
	}
};

Event.observe(window,'load',geoCurrency.init);

