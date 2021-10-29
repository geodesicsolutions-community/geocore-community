function updateSelectBox( selectBox, jsonObj, filterLevel ) {
	selectBox = $(selectBox);
	
	selectBox.update('');
	
	jsonObj.options.each( function( option ) {
		selectBox.insert(new Element ('option', {'value': option.value}).update(option.key));
		selectBox.value=jsonObj.selected;
	});
	
	if (jsonObj.options && filterLevel) {
		$('level_'+filterLevel+'_optionals_message').hide();
		$('level_'+filterLevel+'_optionals_settings').show();
	} else if (filterLevel) {
		$('level_'+filterLevel+'_optionals_message').show();
		$('level_'+filterLevel+'_optionals_settings').hide();
	}
}
	

function Geo_Filter_Admin( levels, lang ) {
	var levels = levels;
	var lang = lang;
	var debug = false;
	
	this.handleTimeout = function( request ) {
		request.transport.abort();
		alert('Sorry, the server is not responding');
		// Run the onFailure method if we set one up when creating the AJAX object
		if (request.options['onFailure']) {
			request.options['onFailure'](request.transport, request.json);
		}
	}
	
	this.showSettings = function ( level ) {
		$( 'level_' + level + '_choices_settings' ).show();
		$( 'level_' + level + '_choices_none_selected' ).hide();
		$( 'level_' + level + '_choices_no_choices' ).hide();
	}
	
	this.showNoneSelected = function ( level ) {
		$( 'level_' + level + '_choices_settings' ).hide();
		$( 'level_' + level + '_choices_none_selected' ).show();
		$( 'level_' + level + '_choices_no_choices' ).hide();
	}
	
	this.showNoChoices = function ( level ) {
		$( 'level_' + level + '_choices_settings' ).hide();
		$( 'level_' + level + '_choices_none_selected' ).hide();
		$( 'level_' + level + '_choices_no_choices' ).show();
		$('level_'+level+'_optionals_message').show();
		$('level_'+level+'_optionals_settings').hide();
	}
	
	this.loadChildren = function( filterLevel, parentSelect ) {
		// Cannot load children if nothing is selected in parent
		if( parentSelect.selectedIndex === null || parentSelect.selectedIndex < 0 ) {
			return false;
		}
		
		
		// Get parent ID of children
		var parentId = parentSelect.options[parentSelect.selectedIndex].value;
				
		var sendLoadRequest = function() {
			new Ajax.Request( 'AJAX.php', {
				method: 'get',
				parameters: { controller: 'Filter', action: 'getChildren', lang: lang, parent: parentId },
				onComplete: function(transport) {
	    			var json = transport.responseJSON;
					if ( json && json.options && json.options.size() > 0 ) {
	    				geoFilter.showSettings( filterLevel + 1 );
	    				// Get children's select box
						var selectBox = $('level_' + (filterLevel + 1));
	   					updateSelectBox( selectBox, json,  (filterLevel + 1));
	    			} else {
						geoFilter.showNoChoices( filterLevel + 1 );
	    			}
	    			geoFilter.resetLevels( filterLevel + 2 );
				}
			});
		}
		
		// Add children's select box if it doesn't exist
		if ( !$('level_' + (filterLevel + 1)) ) {
			this.addLevel( parentId, sendLoadRequest );
		} else {
			sendLoadRequest();
		}
	}
		
	this.addLevel = function( selectedParent, callback ) {	
		if( selectedParent < 0 ) {
			alert('Sorry, unable to add a new level');
			return false;
		}
		
		if( selectedParent == 0 ) {
			new Ajax.Updater( 'levelsHolder', 'AJAX.php', {
				method:'get',
				insertion: Insertion.Bottom,
				parameters: {controller:'Filter', action:'addLevel', level: (levels + 1), lang: lang },
				onComplete: function() {
					levels++;
					$('level_'+levels+'_optionals_message').show();
					$('level_'+levels+'_optionals_settings').hide();
					if ( callback ) {
						callback();
					}
				}
			});
		} else {
			new Ajax.Updater( 'levelsHolder', 'AJAX.php', {
				method:'get',
				insertion: Insertion.Bottom,
				parameters: {controller:'Filter', action:'addLevel', level: (levels + 1), lang: lang, parent: selectedParent },
				onComplete: function() {
					levels++;
					
					if ( callback ) {
						callback();
					} else {
					}
					if(!$('level_'+levels) || !$('level_'+levels).down()) {
						$('level_'+levels+'_optionals_message').show();
						$('level_'+levels+'_optionals_settings').hide();
					}
				}
			});
		}
	}
	
	this.addChoice = function( filterLevel ) {
		var parentId = 0;
		if( filterLevel > 1 ) {
			var parentSelectBox = $( 'level_' + (filterLevel - 1) );
			var selectedIndex = parentSelectBox.selectedIndex;
			if( selectedIndex < 0 ) {
				alert( 'Please select a parent' );
				return false;
			}
			var parentId = parentSelectBox.options[parentSelectBox.selectedIndex].value;
			if( !parentId ) {
				alert( 'Please select a parent' );
				return false;
			}
		}
	
		var optionText = prompt( 'Enter a name: ');
		if( !optionText ) {
			return false; // prevent hyperlink from being followed
		}
		new Ajax.Request( 'AJAX.php', {
			method:'get',
			parameters: {controller:'Filter', action:'addChoice', name:optionText, lang: lang, parent: parentId, level: filterLevel},
			onSuccess: function( transport ) {
				var selectBox = $( 'level_' + filterLevel );
				var json = transport.responseJSON;
				if ( json && json.options && json.options.size() ) {
					geoFilter.showSettings( filterLevel );
    				updateSelectBox( selectBox, json, filterLevel );
					$('level_'+filterLevel+'_optionals_settings').show();
					$('level_'+filterLevel+'_optionals_message').hide();
					geoFilter.updatePreview( 0 );
					if( !$( 'level_' + (filterLevel + 1) + '_choices_settings') ) {
						geoFilter.addLevel();
					}
					geoFilter.showNoChoices( filterLevel + 1 );
					geoFilter.resetLevels( filterLevel + 2);
    			} else {
					geoFilter.resetLevels( filterLevel + 1);
    			}
    			
			},
			onFailure: function (transport) {
				geoFilter.resetLevels( filterLevel + 1);
			}
		});
		return false; // prevent hyperlink from being followed
	}
	
	this.delChoice = function ( filterLevel ) {
		var selectBox = $( 'level_' + filterLevel );
		var choiceId = selectBox.options[selectBox.selectedIndex].value;
		var choiceName = selectBox.options[selectBox.selectedIndex].text;
	
		var childrenSelectBox = $( 'level_' + (filterLevel + 1) );
		if( childrenSelectBox && childrenSelectBox.options.length ) {
			alert( 'Cannot delete choices that contain subchoices' );
			return false;
		}
	
		if( !confirm( 'Are you sure you want to delete ' + choiceName + '?') ) {
			return false; // prevent hyperlink from being followed
		}
	
		new Ajax.Request('AJAX.php', {
			method:'get',
			parameters: {controller:'Filter', action:'delChoice', id: choiceId, lang: lang},
			onComplete: function( transport ) {
				var json = transport.responseJSON;
				if ( json && json.options && json.options.size() ) {
					geoFilter.showSettings( filterLevel );
    				updateSelectBox( selectBox, json, filterLevel );
    			} else {
    				selectBox.innerHTML = "";
    				geoFilter.showNoChoices( filterLevel );
    			}
				geoFilter.resetLevels( filterLevel + 1 );
				geoFilter.updatePreview( 0 );
    		}
		});
		return false; // prevent hyperlink from being followed
	}
	
	this.editChoice = function ( filterLevel ) {
		var selectBox = $( 'level_' + filterLevel );
		var choiceId = selectBox.options[selectBox.selectedIndex].value;
		var choiceName = selectBox.options[selectBox.selectedIndex].text;
	
		choiceName = prompt( 'Enter a new name for ' + choiceName + ': ');
		if( !choiceName ) {
			return false; // prevent hyperlink from being followed
		}
	
		new Ajax.Request( 'AJAX.php', {
			method:'get',
			parameters: {controller:'Filter', action:'editChoice', id: choiceId, lang: lang, name: choiceName},
			onSuccess: function( transport ) {
				var json = transport.responseJSON;
				if ( json && json.options && json.options.size() ) {
    				updateSelectBox( selectBox, json );
    			}
				geoFilter.updatePreview( 0 );
    		}
		});
		return false; // prevent hyperlink from being followed
	}
	this.moveChoiceUp = function ( filterLevel ) {
		var selectBox = $( 'level_' + filterLevel );
		var choiceId = selectBox.options[selectBox.selectedIndex].value;
		new Ajax.Request( 'AJAX.php', {
			method:'get',
			parameters: { controller: 'Filter', action: 'moveChoiceUp', id: choiceId, lang: lang },
			onComplete: function(transport) {
				var json = transport.responseJSON;
				if ( json && json.options && json.options.size() ) {
    				updateSelectBox( selectBox, json );
    			}
				geoFilter.updatePreview( 0 );
			}
		});
		return false; // prevent hyperlink from being followed
	}
	
	this.moveChoiceDown = function ( filterLevel ) {
		var selectBox = $( 'level_' + filterLevel );
		var choiceId = selectBox.options[selectBox.selectedIndex].value;
		new Ajax.Request( 'AJAX.php', {
			method:'get',
			parameters: { controller: 'Filter', action: 'moveChoiceDown', id: choiceId, lang: lang },
			onSuccess: function( transport ) {
				var json = transport.responseJSON;
				if ( json && json.options && json.options.size() ) {
    				updateSelectBox( selectBox, json );
    			}
				geoFilter.updatePreview( 0 );
    		}
		});
		return false; // prevent hyperlink from being followed
	}
	
	this.moveChoiceTop = function ( filterLevel ) {
		var selectBox = $( 'level_' + filterLevel );
		var choiceId = selectBox.options[selectBox.selectedIndex].value;
		new Ajax.Request( 'AJAX.php', {
			method:'get',
			parameters: { controller: 'Filter', action: 'moveChoiceTop', id: choiceId, lang: lang },
			onSuccess: function( transport ) {
				var json = transport.responseJSON;
				if ( json && json.options && json.options.size() ) {
    				updateSelectBox( selectBox, json );
    			}
				geoFilter.updatePreview( 0 );
    		}
		});
		return false; // prevent hyperlink from being followed
	}
	
	this.moveChoiceBottom = function ( filterLevel ) {
		var selectBox = $( 'level_' + filterLevel );
		var choiceId = selectBox.options[selectBox.selectedIndex].value;
		new Ajax.Request( 'AJAX.php', {
			method:'get',
			parameters: {controller:'Filter', action:'moveChoiceBottom', id: choiceId, lang: lang },
			onSuccess: function( transport ) {
				var json = transport.responseJSON;
				if ( json && json.options && json.options.size() ) {
    				updateSelectBox( selectBox, json );
    			}
				geoFilter.updatePreview( 0 );
    		}
		});
		return false; // prevent hyperlink from being followed
	}
	
	this.resetLevels = function( level ) {
		// as long as current exists
		while ( current = $( 'level_' + level ) ) {
			// show "Please choose . . ."
			geoFilter.showNoneSelected( level );
			current.innerHTML = "";
			level++;
		}
	}
	
	this.setRegistrationOptional = function( level ) {
		var selectBox = $('level_' + level + '_registration_optionals');
		var optionalId = selectBox.options[selectBox.selectedIndex].value;
		new Ajax.Request( 'AJAX.php', {
			method: 'get',
			parameters: {controller: 'Filter', action: 'setRegistrationOptional', level: level, id: optionalId},
			onSuccess: function( transport ) {
				var json = transport.headerJSON;
				if ( json && json.options && json.options.size() ) {
    				updateSelectBox( selectBox, json );
    			}
    		}
		});
	}
	
	this.setSiteOptional = function( level ) {
		var selectBox = $('level_' + level + '_site_optionals');
		var optionalId = selectBox.options[selectBox.selectedIndex].value;
		new Ajax.Request( 'AJAX.php', {
			method: 'get',
			parameters: {controller: 'Filter', action: 'setSiteOptional', level: level, id: optionalId},
			onSuccess: function( transport ) {
				var json = transport.headerJSON;
				if ( json && json.options && json.options.size() ) {
    				updateSelectBox( selectBox, json );
    			}
    		}
		});
	}
	
	this.updatePreview = function( level ) {
		for( var i = level + 1; i <= 10; i++ ) {
			$( 'level_' + i + '_preview_placeholder' ).innerHTML = '';
		}
		if ( level == 0 ) {
			parentId = 0;
		} else {
			parentSelect = $( 'level_' + level + '_preview' );
			parentId = parentSelect.options[parentSelect.selectedIndex].value;
		}
		new Ajax.Updater( 'level_' + (level + 1) + '_preview_placeholder', 'AJAX.php', {
			method: 'get',
			parameters: {controller: 'Filter', action: 'showPreviewDropdown', lang: lang, level: level + 1, parent: parentId }
		});
	}	
	
	Ajax.Responders.register({
		onFailure: function ( request ) {
			alert("Sorry, I cannot complete your request");
		},
		onCreate: function(request) {
			request['timeoutId'] = window.setTimeout(
				function() {
					// If we have hit the timeout and the AJAX request is active, abort it and let the user know
					switch (request.transport.readyState) {
						case 1: case 2: case 3:
							geoFilter.handleTimeout( request );
							break;
							
						case 4:
							if( request.transport.status == 0 ) {
								geoFilter.handleTimeout( request );
							}
							break;
					}
				}, 5000
			); // Five seconds
		},
		onComplete: function( requester, XHR ) { 
			if( !debug ) {
				if( XHR.responseText.indexOf('<error>') >= 0 ) {
					// Extract error
					var begin = XHR.responseText.indexOf('<error>') + 7;
					var end = XHR.responseText.indexOf('</error>');
					XHR.responseText.substring(begin, end);
				}
				if ( XHR.responseText.indexOf('<user_error>') >= 0 ) {
					// Extract user error
					var begin = XHR.responseText.indexOf('<user_error>') + 12;
					var end = XHR.responseText.indexOf('</user_error>');
				}
			} else {
				alert( XHR.responseText );
			}
		}
	});
}