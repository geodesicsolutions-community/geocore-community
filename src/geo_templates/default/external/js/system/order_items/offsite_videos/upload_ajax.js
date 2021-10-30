// 7.4.6-18-ge7c5fdd

//This file allows offsite_video video ajax to work during listing process.

var geoVidProcess = {
	text : {
		addButton : 'Add This Video',
		editButton : 'Apply Changes'
	},
	
	currentSlot : 0,
	
	ajaxUrl : 'AJAX.php',
	
	adminId : 0,
	userId : 0,
	draggable : null,
	
	//If you are constantly getting the server error message, change this to
	//true and it will display additional debug information about what is wrong.
	debug : false,
	
	//scroll setting for sortable (dragging image boxes around), if it causes
	//problems on your layout you can change it to null to disable.
	scrollSetting : window,
	
	//if this is true, that means something is happening, so no actions can take
	//place.  Be careful of deadlock/race conditions on this!
	_inTransition : '',
	
	init : function () {
		//init everything here
		
		jQuery('.offsite_video_action_buttons').html('');
		
		if (geoVidProcess.currentSlot) {
			//insert the add button
			
			for (var i=1; i<=geoVidProcess.currentSlot; i++) {
				var buttonContainer = jQuery('#offsite_videoButtons_'+i);
				if (buttonContainer.length) {
					var msg = (i==geoVidProcess.currentSlot)? geoVidProcess.text.addButton : geoVidProcess.text.editButton;
					var addButton = jQuery('<a class="button" href="#">')
						.html(msg);
					//watch the button like a hawk!
					addButton.click(geoVidProcess.buttonClick);
					
					buttonContainer.append(addButton);
				}
			}
		}
		
		//keep watch on delete buttons
		jQuery('.delete_offsite_video').unbind().click(geoVidProcess.deleteVideo);
		
		//transfer the offsite video slot sortable class to the parent...
		jQuery('.offsite_video_slot').each (function (index) {
			var elem=jQuery(this);
			if (elem.children().hasClass('offsite_video_is_sortable')) {
				elem.removeClass('offsite_video_slot_not_sortable');
			} else {
				elem.addClass('offsite_video_slot_not_sortable');
			}
		});
		
		//do the draggable bit
		geoVidProcess.initSortableSlots();
	},
	
	buttonClick : function (event) {
		event.preventDefault();
		
		if (geoVidProcess.doingSomething('')) return;
		
		geoVidProcess.doingSomething('applyChanges');
		
		//make the animation show
		jQuery(this).parent().prev().show();
		
		//trick is, we are sending ALL fields for all the offsite_video videos, so user
		//can actually enter in all of the fields before submitting.  It will skip
		//processing on any that have not changed.
		
		var params = jQuery('#offsite_videos_outer').find('input').serializeArray();
		
		params[params.length] = {
			name : 'adminId',
			value: geoVidProcess.adminId
		};
		
		params[params.length] = {
			name : 'userId',
			value: geoVidProcess.userId
		};
		jQuery.ajax({
			url : geoVidProcess.ajaxUrl+'?controller=OffsiteVideos&action=uploadVideo',
			type : 'POST',
			data : jQuery.param(params),
			dataType : 'json'
		}).done(geoVidProcess.uploadResponse);
	},
	
	deleteVideo : function (event) {
		event.preventDefault();
		
		if (geoVidProcess.doingSomething('')) return;
		
		//figure out for which slot
		var deleteVideoSlot = jQuery(this).attr('id').replace('deleteYoutube_','');
		
		//TODO: add a confirmation for delete
		
		//send an ajax call to delete the image.
		geoVidProcess.doingSomething('deleteVideo');
		var params = [
			{
				name: 'videoSlot',
				value : deleteVideoSlot
			},
			{
				name: 'userId',
				value: geoVidProcess.userId
			},
			{
				name: 'adminId',
				value: geoVidProcess.adminId
			}
		];
		jQuery.ajax({
			url : geoVidProcess.ajaxUrl+"?controller=OffsiteVideos&action=deleteVideo",
			type : 'POST',
			data : jQuery.param(params),
			dataType : 'json'
		}).done(geoVidProcess.deleteSuccess);
	},
	
	deleteSuccess : function (data) {
		if (data) {
			geoVidProcess.processResponse(data);
		}
		geoVidProcess.doNothing();
	},
	
	uploadResponse : function (data) {
		if (data) {
			geoVidProcess.processResponse(data);
		}
		//not doing anything any more
		geoVidProcess.doNothing();
	},
	
	initSortableSlots : function () {
		//NOTE: If element is already sortable, it destroys it first automatically
		//for us!  So we can call geoVidProcess whenever we need the sortable items to be
		//re-done, no need to call destroy ourselves		
		
		if (true || !jQuery('#offsite_videos_outer').length) {
			//jQuery.sortable() breaks data entry on phones/tablets, so neutering this until a solution can be found (see Bug #1503, 1504)
			return;
		}
		
		video_starting_order = null;
		jQuery('#offsite_videos_outer').sortable({
			cancel: ".offsite_video_slot_not_sortable, input, a, textarea, button, select, option",
			cursor: "move",
			start: function(event, ui) {
				video_starting_order = jQuery('#offsite_videos_outer').sortable('serialize');
			},
			stop: function(event, ui) {
				//save sort here
				video_final_order = jQuery('#offsite_videos_outer').sortable('serialize');
				if(video_starting_order == video_final_order) {
					//no change in order; nothing to do
					return;
				}				
				jQuery.post(geoVidProcess.ajaxUrl+"?controller=OffsiteVideos&action=sortVideos",
					{
						'videoSlots': video_final_order,
						'userId' : geoVidProcess.userId,
						'adminId' : geoVidProcess.adminId
					},
					function(data) {
						geoVidProcess.sortableResponseSuccess(data);
					}
				
				);
			}
			
		});
	},
	sortableResponseSuccess : function (response) {
		if (response) {
			geoVidProcess.processResponse(response);
		}
		geoVidProcess.doNothing();
	},
	
	processResponse : function (data) {
		if (data.error) {
			gjUtil.addError(data.error);
		}
		if (data.errorSession) {
			gjUtil.addError(data.errorSession);
		}
		
		if (data.msg){
			gjUtil.addMessage(data.msg, 2000);
		}
		
		if (data.changed_slots) {
			jQuery.each(data.changed_slots, function (index, item) {
				jQuery('#offsite_video_slot_'+item.slotNum).html(item.contents);
			});
			
			geoVidProcess.currentSlot = data.edit_slot;
			geoVidProcess.init();
		}
		
		if (data.upload_slots_html) {
			jQuery('#offsite_videos_outer').html(data.upload_slots_html);
			
			geoVidProcess.currentSlot = data.edit_slot;
			geoVidProcess.init();
		}
		jQuery('.offsite_video_loading_container').hide();
	},
	
	doingSomething : function (notThis) {
		return (geoVidProcess._inTransition == notThis)? false: true;
	},
	
	doSomething : function (what) {
		//sanity check
		if (geoVidProcess.doingSomething('')) {
			//already doing something, stop that!
			if (geoVidProcess.debug) alert('Attempting to do something '+what+' when already '+geoVidProcess._inTransition);
			return false;
		}
		geoVidProcess._inTransition = what;
		return true;
	},
	doNothing : function () {
		geoVidProcess._inTransition = '';
	}
};

