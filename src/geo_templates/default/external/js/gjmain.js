// 17.01.0-37-g075e182

//Main jQuery based JS, this is where "new" JS goes, or any of the existing JS
//that has been converted to work with jQuery instead of Prototype.

//NOTE:  you don't have to customize this JS file to change vars, instead add some
//JS to your own custom JS file (or in script tags on a template), most of
//the plugins were written to allow some custimization to how they work.


/*
 * This is the main namespace for generic JS utilities.  Note: gj is short for
 * "Geodesic jQuery"..  Anything with the "old" prefix of "geo" should be considered
 * deprecated, to be converted to use jQuery in future version.
 */
var gjUtil = {
	runHeartbeat : false,
	inAdmin : false,
	
	/**
	 * This one is for stuff to do when DOM is done loading
	 */
	ready : function () {
		if (typeof console !== 'undefined' && typeof console.log !== 'undefined') {
			jQuery.error = console.log;
		}
		if (typeof window.IN_ADMIN !== 'undefined') {
			if (window.IN_ADMIN) {
				gjUtil.inAdmin = true;
			}
		}
		
		//call back to run the heartbeat
		if (gjUtil.runHeartbeat && !gjUtil.inAdmin) {
			//ping cron.php
			jQuery.get('cron.php?action=cron');
			//then set runHeartbeat to false, keep it from running again
			gjUtil.runHeartbeat=false;
		}
		
		//Browsing: make sort dropdown work:
		jQuery('select.browse_sort_dropdown').change(gjUtil.browseSortChange);
		
		jQuery('.openLightboxLink').click(function () {
			jQuery(document).gjLightbox('get',jQuery(this).attr('href'));
			return false;
		});
		
		gjUtil.initRWD();
		
		gjUtil.lightbox.initClick();
		
		//now that lightbox triggers are in place, remove ones that shouldn't be there for a small screen
		//and watch for screen size to change, and add or remove as needed
		if(jQuery(document).width() <= 832) { //=52em
			//window is at a mobile width. disable some lightboxes that shouldn't trigger
			jQuery('.mobile-lightbox-disable').unbind('click').css('cursor', 'default');
		}
		jQuery(window).resize(function () {
			if(jQuery(document).width() <= 832) {
				jQuery('.mobile-lightbox-disable').unbind('click').css('cursor', 'default');
			} else {
				jQuery('.mobile-lightbox-disable').css('cursor','pointer');
				gjUtil.lightbox.initClick();
			}
		});
		
		gjUtil.leveledFields.init(jQuery(document));
		
		//advanced search
		gjUtil.searchCategory.init();
		
		//fade images (or dives with whatever in them) in and out
		jQuery('.gj_image_fade').gjImageFade();
		
		//make show / collapse work
		jQuery('.section-collapser').click(function () {
			var isCollapsed = jQuery(this).hasClass('section-collapser-expanded');
			jQuery(this).toggleClass('section-collapser-expanded',!isCollapsed)
				.next().toggleClass('expand',!isCollapsed);
			
		});
		
		//Make the social hovers work
		gjUtil.initSocialHovers();
		
		//tag search autocomplete
		gjUtil.initTagSearch();
		
		//calendar inputs
		gjUtil.initDatePicker();
		
		jQuery('ul.tabList').gjTabs();
		
		//disable an element after it has been clicked once (most useful on buttons that submit forms)
		jQuery('.click-once').click(function() {
			jQuery(this).prop('disabled',true);
		});
	},
	
	/**
	 * This is for things to do when window is done loading (all images loaded,
	 * NOT just the DOM loaded
	 */
	load : function () {
		//initialize gallery / carousel stuff
		gjUtil.initGallery();
		
		//init image gallery
		gjUtil.initImgGallery();
		
		//Sometimes the carousel row width is off, if loaded on document ready, so
		//init this on page load not on dom ready.
		gjUtil.initCarousel();
	},
	
	getCookie : function (sName) {
		var aCookie = document.cookie.split('; ');
		for (var i=0; i < aCookie.length; i++) {
			var aCrumb = aCookie[i].split('=');
			if (sName == aCrumb[0]) {
				return unescape(aCrumb[1]);
			}
		}
		return null;
	},
	
	initRWD : function () {
		jQuery('.fixed-link').click(function () {
			//expand it
			var selector = this.hash.replace('#','.');
			if (!selector || !jQuery(selector).length) {
				//couldn't find...
				return false;
			}
			if (jQuery(selector).is(':visible')) {
				//just hide it...
				//when done hiding it, remove the "display" CSS so that the class CSS
				//kicks in to prevent it from being hidden if screen resized
				jQuery('.fixed-menu').hide('fast', function () {jQuery(this).css({display : ''});});
			} else {
				//first hide any other sections that might be showing
				jQuery('.fixed-menu').hide('fast');
				//then show this one
				jQuery(selector).show('fast');
			}
			
			//return false to stop action
			return false;
		});
	},
	
	/**
	 * Initializes the listing gallery.  (Just makes sure the height on all the
	 * gallery items match up to make it all lined up)
	 */
	initGallery : function () {
		//Find all galleries that use columns/rows, and make sure each gallery
		//entry matches in height.  Done this way because heights need to match
		//for each gallery, but not across different galleries that might be on
		//the page.
		jQuery('.listing_set.gallery').has('.gallery_row').each(function(){
			//note that we first reset the height to "auto" and THEN calculate the max
			//this is to fix an issue where galleries on a hidden tab have their height explicitly set to 0
			jQuery(this).find('.article_inner').css('height','auto').gj('setMaxHeight');
		});
	},
	/**
	 * Initializes the listing gallery carousel
	 */
	initCarousel : function () {
		//init the simple carousel on any elements with CSS class of "gj_simple_carousel"
		jQuery('.gj_simple_carousel .listing_set.gallery').gjSimpleCarousel();
		
		if (jQuery('.gj_carousel_keySlide').length) {
			//there are carousels to move back and forth, so register back/forth arrows
			jQuery(document).bind('keypress', function (e){
				if (e.keyCode==37) {
					//slide to the left
					jQuery('.gj_carousel_keySlide').gjSimpleCarousel('slide',{where:'left'});
				} else if (e.keyCode==39) {
					//slide to the right
					jQuery('.gj_carousel_keySlide').gjSimpleCarousel('slide',{where:'right'});
				}
			});
		}
	},
	
	/**
	 * Initializes the image gallery, the gallery for showing images on an individual
	 * listing.
	 */
	initImgGallery : function () {
		//large image block links...  Let's just stick this in here
		jQuery('.largeImageBlockLink').click(function () {
			//set top offset to 5 px up
			var topOffset = jQuery(this.hash).offset().top - 5;
			jQuery('html, body').animate({
				scrollTop : topOffset
			}, 2000);
			return false;
		});
		
		//This is actually the "gallery view" for images in one single listing.
		jQuery('.galleryContainer, .filmstrip_container').each(function(){
			$this = jQuery(this);
			var bigImg = $this.find('.bigLeadImage, .filmstrip_main_img');
			var bigDesc = $this.find('.imageTitle');
			
			$this.find('.thumb').each(function (){
				jQuery(this).click(function(){
					var txtNode = jQuery(this).next();
					var bigClass = txtNode.prop('id');
					var txt = txtNode.html();
					
					//hide everything
					bigImg.find('a:visible').hide();
					//then show just what we want
					bigImg.find('a.'+bigClass).show();
					//then shove in the p contents
					bigDesc.html(txt);
				});
				jQuery(this).hover(function () {
					//trigger the normal click behavior
					jQuery(this).click();
				}, function () {});
			});
			
			//make sure the width of the outer is set to max height...
			$this.find('.galleryBigImage').width($this.find('.galleryBigImage img').gj('getMaxWidth'));
			
			if($this.find('.galleryBigImage').length) {
				//specific to "gallery" view: ("filmstrip" does this a bit further down, with different class names)
				//Set min width/height on big image according to largest dimensions
				//so that the big img doesn't jump around.
				var tallestBig=0;
				$this.find('.bigLeadImage a > img').each(function(){
					//this only correctly gets dimensions for the first image
					tallestBig = Math.max(tallestBig, jQuery(this).outerHeight());
				});
				$this.find('.bigLeadImage a').each(function(){
					//this does NOT correctly get dimensions for the first image
					tallestBig = Math.max(tallestBig, jQuery(this).outerHeight());
				});
				
				$this.find('.bigLeadImage').css({
					'min-height':(tallestBig+1)+'px' //+1 here prevents some more jumping
				});
			}
			
			if ($this.find('.filmstrip_strip_container').length) {
				//specific to filmstrip: make hover over arrows smooth scroll...
				
				//first, make the height so that the stuff inside actually shows
				var tallestThumb=90;
				
				$this.find('.filmstrip_entry').each(function() {
					tallestThumb = Math.max(tallestThumb, jQuery(this).outerHeight());
				});
				
				//calculate amount of buffer needed for the overall width, NOT
				//including any extra stuff possibly added by the image caption.
				//first figure out the buffer surounding the main div
				var mainFilmBuffer = $this.find('.filmstrip_main_img').outerWidth(true)-$this.find('.filmstrip_main_img').innerWidth();
				//now add any buffer for the image itself
				mainFilmBuffer = mainFilmBuffer + $this.find('.filmstrip_main_img img').outerWidth(true)-$this.find('.filmstrip_main_img img').innerWidth();
				
				//now set the main container to match width plus the buffer, so that
				//long captions do not push it really wide
				$this.find('.filmstrip_main').width($this.find('.filmstrip_main img').gj('getMaxWidth')+mainFilmBuffer);
				
				$this.find('.filmstrip_strip_container').height(tallestThumb);
				
				//Set min width/height on big image according to largest dimensions
				//so that the big img doesn't jump around.
				var tallestBig=0;
				$this.find('.filmstrip_main_img a>img').each(function(){
					//this only correctly gets dimensions for the first image
					tallestBig = Math.max(tallestBig, jQuery(this).outerHeight());
				});
				$this.find('.filmstrip_main_img a').each(function(){
					//this does NOT correctly get dimensions for the first image
					tallestBig = Math.max(tallestBig, jQuery(this).outerHeight());
				});
				
				$this.find('.filmstrip_main_img').css({
					'min-height':(tallestBig+1)+'px' //+1 here prevents some more jumping
					});
				
				//now get the infernal hovers to work
				//first figure out how wide we are total...
				var innerWidth = $this.find('.filmstrip_strip').outerWidth();
				
				//and the width of the surrounding part
				var windowWidth = $this.find('.filmstrip_strip_container').innerWidth();
				
				var hideScroll = function (elem) {
					elem.css({'opacity':'0.2', cursor:'default'})
						.addClass('no_hover');
				};
				var showScroll = function (elem) {
					elem.css({'opacity':'1', cursor:'pointer'})
						.removeClass('no_hover');
				};
				
				var leftB = $this.find('.filmstripLeftScrollButton');
				var rightB = $this.find('.filmstripRightScrollButton');
				
				if (innerWidth<windowWidth) {
					//all the images fit inside the window, no scrolling needed...
					hideScroll(leftB);
					hideScroll(rightB);
				} else {
					//set up hover effects
					
					var overflow = innerWidth-windowWidth;
					
					//function to update whether buttons show or not
					var updateB = function (filmstrip) {
						if (typeof filmstrip == 'undefined') {
							var filmstrip = jQuery(this).closest('.filmstrip_container').find('.filmstrip_strip');
						}
						var d = filmstrip.position().left;
						
						if (d==0) {
							//all the way to left...
							hideScroll(leftB);
							showScroll(rightB);
						} else if ((d*-1) == overflow) {
							//all the way to the right...
							showScroll(leftB);
							hideScroll(rightB);
						} else {
							//somewhere in middle
							showScroll(leftB);
							showScroll(rightB);
						}
					};
					
					//go ahead and updateB now
					updateB($this.find('.filmstrip_strip'));
					
					//goal:  umm, how about 100px / second...
					var speed = 100;
					
					leftB.hover(function () {
						//find the part that gets moved around, relative to this button
						var filmstrip = jQuery(this).closest('.filmstrip_container').find('.filmstrip_strip');
						//now figure out the distance (d) from left
						var d = filmstrip.position().left;
						if (d==0) {
							//already full left
							return;
						}
						
						//note: d is negative, need it to be positive, thus the -1000
						var duration = (d * -1000) / speed;
						filmstrip.filter(':not(:animated)')
							.animate({left:'0px'}, {
								'duration':duration,
								'complete': updateB
							});
					}, function () {
						jQuery(this).closest('.filmstrip_container').find('.filmstrip_strip').stop(true,false);
						updateB(jQuery(this).closest('.filmstrip_container').find('.filmstrip_strip'));
					});
					
					rightB.hover(function () {
						//find the part that gets moved around, relative to this button
						var filmstrip = jQuery(this).closest('.filmstrip_container').find('.filmstrip_strip');
						//now figure out the distance (d) from right
						var d = (-1*overflow) - filmstrip.position().left;
						if (d==0) {
							//already full right to left
							return;
						}
						var duration = (d * -1000) / speed;
						
						filmstrip.filter(':not(:animated)')
							.animate({left:'-'+overflow+'px'}, {
								'duration':duration,
								'complete': updateB
							});
					}, function () {
						jQuery(this).closest('.filmstrip_container').find('.filmstrip_strip').stop(true,false);
						updateB(jQuery(this).closest('.filmstrip_container').find('.filmstrip_strip'));
					});					
				}
			}
		});
	},
	
	/**
	 * Makes the fancy social hovers work.  Adapted from something found on:
	 * 
	 * http://www.marcofolio.net/css/display_social_icons_in_a_beautiful_way_using_css3.html
	 */
	initSocialHovers : function () {
		// Hide all the tooltips.. and un-do the display: none since we now hide with opacity
		jQuery("#social_hovers li a strong").css({opacity: 0, display: ''});
		
		jQuery("#social_hovers li").hover(function() { // Mouse over
			jQuery(this)
				.stop().fadeTo(500, 1)
				.siblings().stop().fadeTo(500, 0.2);
			
			jQuery(this).find('a strong')
				.stop()
				.animate({ opacity: 1, top: '-10px' }, 300);
		}, function() { // Mouse out
			jQuery(this)
				.stop().fadeTo(500, 1)
				.siblings().stop().fadeTo(500, 1);
			
			jQuery(this).find('a strong')
				.stop()
				.animate({ opacity: 0, top: '-1px' }, 300);
		});
	},
	
	initTagSearch : function () {
		if (!jQuery('.tagSearchField').length) {
			//no tag search fields...
			return;
		}
		
		jQuery('.tagSearchField').autocomplete({
			source : function (request, response) {
				jQuery.getJSON('AJAX.php?controller=ListingTagAutocomplete&action=getSuggestions', {
					tags : request.term,
					showCounts : 1
				}, response);
			},
			select : function (event, ui) {
				jQuery(this).val(ui.item.value).closest('form').submit();
			}
		});
	},
	
	initDatePicker : function () {
		jQuery('.dateInput,.datepicker').attr('placeholder', gjUtil._dateDefaultText)
			.datepicker({ dateFormat: 'yy-mm-dd' });
	},
	
	/**
	 * Observer for when the browse sort-by dropdown changes.
	 */
	browseSortChange : function () {
		jQuery(this).find('option:selected').each(function(){
			//start from hidden a tag href.. Needed to force IE to take base
			//location into effect.  Trying to use just relative URL will
			//not work in IE when URL is re-written already.
			var href = jQuery(this).parent('select').prev('a').get(0).href;
			
			if (href.indexOf('?')==-1) {
				//this is re-written, add ? to end
				href += '?c=';
			}
			href += jQuery(this).val();
			window.location.href=href;
		});
	},
	
	/**
	 * Handles taking user to next page automatically when logging in or
	 * registering
	 * 
	 * Note: Uses jQuery!
	 * 
	 * @param string form ID of form to submit
	 * @param string replaceTxt
	 */
	autoSubmitForm : function (form, replaceTxt) {
		jQuery(function () {
			setTimeout(function() {
				var myForm = jQuery('#'+form);
				if (myForm && myForm.length) {
					if (replaceTxt) {
						window.location.replace(replaceTxt);
					}
					myForm.submit();
				}
			}, 2000); //wait two seconds after page loads, then go!
		});
	},
	
	searchCategory : {
		_onComplete : [],
		
		init : function () {
			jQuery('#adv_searchCat').on('change', gjUtil.searchCategory.categoryChange);
			
			gjUtil.searchCategory.onComplete(function () {
				//function to call when new results are loaded...
				//be sure to initialize the stuff for leveled fields.
				gjUtil.leveledFields.init(jQuery('#catQuestions'));
				
				//update the calendar stuff
				gjUtil.initDatePicker();
			});
			gjUtil.searchCategory.categoryChange();
		},
		
		
		
		onComplete : function (callback) {
			if (typeof callback !== 'function') {
				jQuery.error('Invalid callback specified, not a function.');
				return;
			}
			gjUtil.searchCategory._onComplete[gjUtil.searchCategory._onComplete.length] = callback;
		},
		
		categoryChange : function () {
			//use reference to ID instead of this, that way can use this method
			//directly
			var catId = jQuery('#adv_searchCat').val();
			
			if (!catId) {
				//empty the contents of the search field thingy
				gjUtil.searchCategory.emptyCatFields();
				return;
			}
			
			var url = 'AJAX.php?controller=AdvancedSearch&action=getCatFields&catId='+catId;
			jQuery('#catQuestions').load(url,function (resultTxt) {
				if (!resultTxt.length) {
					//empty results, close it
					gjUtil.searchCategory.emptyCatFields();
					return;
				}
				jQuery(this).show('slow');
				//do anything for onload
				jQuery.each(gjUtil.searchCategory._onComplete, function() {this();});
			});
		},
		
		emptyCatFields : function () {
			if (jQuery('#catQuestions').is(':empty')) {
				//already empty, no work to do
				return;
			}
			//hide it and empty it
			jQuery('#catQuestions').hide().empty();
		}
	},
	
	/**
	 * Shortcuts for doing things with the gjLightbox plugin
	 */
	lightbox : {
		/**
		 * Shortcut to initialize the lightbox.  This is shortcut for doing:
		 * jQuery(document).gjLightbox();
		 */
		init : function () {
			jQuery(document).gjLightbox();
		},
		
		/**
		 * Starts watching all of the common classes that do stuff with the lightbox,
		 * like lightUpLink and such.
		 */
		initClick : function () {
			jQuery('.lightUpImg').gjLightbox('clickLinkImg');
			jQuery('.lightUpLink').gjLightbox('clickLink');
			jQuery('.lightUpDisabled').gjLightbox('clickDisabled');
		},
		
		/**
		 * Easy way to close the lightbox if it's open.  This is a shortcut for:
		 * jQuery(document).gjLightbox('close');
		 */
		close : function () {
			jQuery(document).gjLightbox('close');
		},
		
		/**
		 * Add a callback to be called at the time the lightbox is opened.  Note
		 * that this happens when the lightbox is going from "closed" state to
		 * "open" state, typically you would use this to hide things that
		 * don't work well with overlays.
		 * 
		 * @param callback
		 */
		onOpen : function (callback) {
			if (typeof callback !== 'function') {
				jQuery.error('Not a valid callback function.');
				return;
			}
			//first need to make sure lightbox is initialized
			jQuery(document).gjLightbox();
			//get the data
			var data = jQuery(document).data('gjLightbox');
			if (!data) {
				//not initialized or something went wrong
				jQuery.error('Could not retrieve data, so not able to set next image ID');
				return false;
			}
			data.onOpen[data.onOpen.length] = callback;
		},
		
		/**
		 * Add a callback to be called at the time the lightbox is closed.  Note
		 * that the precise time this happens is when the "fadeOut" animation is
		 * complete for the lightbox.  This is best place to show things that
		 * may have been hidden by an onOpen callback.
		 * 
		 * @param callback
		 */
		onClose : function (callback) {
			if (typeof callback !== 'function') {
				jQuery.error('Not a valid callback function.');
				return;
			}
			//first need to make sure lightbox is initialized
			jQuery(document).gjLightbox();
			//get the data
			var data = jQuery(document).data('gjLightbox');
			if (!data) {
				//not initialized or something went wrong
				jQuery.error('Could not retrieve data, so not able to set next image ID');
				return false;
			}
			data.onClose[data.onClose.length] = callback;
		},
		
		/**
		 * Add a callback to be called at the time the lightbox is showing
		 * new contents, at the point that the contents are done being inserted
		 * into the document DOM.  This is best place to add any new "observers"
		 * on any contents that may have been loaded into the lightbox.
		 * 
		 * @param callback
		 */
		onComplete : function (callback) {
			if (typeof callback !== 'function') {
				jQuery.error('Not a valid callback function.');
				return;
			}
			//first need to make sure lightbox is initialized
			jQuery(document).gjLightbox();
			//get the data
			var data = jQuery(document).data('gjLightbox');
			if (!data) {
				//not initialized or something went wrong
				jQuery.error('Could not retrieve data, so not able to set next image ID');
				return false;
			}
			data.onComplete[data.onComplete.length] = callback;
		},
		
		/**
		 * Gets the jQuery selection of current contents of the lightbox.  Note that
		 * this returns a jQuery('...') object, not the element itself.
		 * 
		 * @returns jQuery selection of the lightbox
		 */
		contents : function () {
			//first need to make sure lightbox is initialized
			jQuery(document).gjLightbox();
			//get the data
			var data = jQuery(document).data('gjLightbox');
			if (!data) {
				//not initialized or something went wrong
				return null;
			}
			return data.box;
		},
		
		/**
		 * Used by the slideshow to set the next image ID
		 */
		setNextImgId : function (id) {
			//first need to make sure lightbox is initialized
			jQuery(document).gjLightbox();
			//get the data
			var data = jQuery(document).data('gjLightbox');
			if (!data) {
				//not initialized or something went wrong
				jQuery.error('Could not retrieve data, so not able to set next image ID');
				return false;
			}
			data.nextImageId = id;
			return true;
		}
	},
	/**
	 * Used for multi-level (leveled) fields, at this point it is simple, might
	 * want to convert it to plugin if it ever gets more complicated.
	 */
	leveledFields : {
		/**
		 * If this is set to true, when someone clicks on a multi-level field value,
		 * it will scroll down to have that value as the first one in the box.  If
		 * this is false, it will ONLY scroll to the value when the page is first loading,
		 * to make sure the "current selected" value is within the scroll box.
		 */
		alwaysScrollToValueOnClick : false,
		
		/**
		 * Initializes any leveledField selections on the page for the given parent
		 * jQuery selection passed in
		 */
		init : function (parent) {
			//watch for clicks
			parent.find('.leveled_value')
				.click(gjUtil.leveledFields.valueClick);
			
			//similate click if a radio is checked already
			var currentSelected = parent.find('input.leveled_radio:checked')
				.closest('.leveled_value');
			
			//do the main part for selecting that option
			currentSelected.each(function () {
				//NOTE: We use "each" so that it runs the function once per selected
				//value, it can break things if pass in a selector with multiple selections
				//in it!
				gjUtil.leveledFields.selectValue(jQuery(this),true);
			});
			
			//watch the pagination
			parent.find('.leveled_pagination a').click(function () {
				var url = this.href;
				url = url.replace(/&selected=[0-9]+/g, '');
				//have to populate selected value...
				//now add correct selected
				var selected = 0;
				var currentChecked = jQuery(this).closest('.leveled_level_box').find(':checked');
				if (currentChecked.length) {
					selected = currentChecked.val();
				}
				url = url + '&selected='+selected;
				jQuery(this).closest('.leveled_level_box').load(url, function () {
					//init contents
					gjUtil.leveledFields.init(jQuery(this));
				});
				//note: do NOT close boxes "after" because the "selected" value is maintained
				//when doing pagination.
				return false;
			});
			
			parent.find('.leveled_clear').click(gjUtil.leveledFields.clearClick);
		},
		
		clearClick : function () {
			
			var container = jQuery(this).closest('.leveled_level_box');
			
			//handle the fancy ajax questions on the advanced search page
			var isSearchCat = container.find('input.leveled_cat_search').length > 0;
			if(isSearchCat) {
				var previousCat = container.prev().find('.selected_value input').val();
				if(typeof previousCat === 'undefined') {
					//clearing the top-level category selection
					previousCat = 0;
				}
				jQuery('#adv_searchCat').val(previousCat);
				gjUtil.searchCategory.categoryChange();
			}
			
			//Un-check the currently checked radio.. hopefully this works
			container.find('input.leveled_radio:checked')
				.attr('checked',false);
			
			//remove the selected CSS from any that have it
			container.find('.leveled_value.selected_value').removeClass('selected_value');
						
			//clear out other children
			gjUtil.leveledFields.closeAfter(container);
			return false;
		},
		
		/**
		 * Function to use for click observer on individual value
		 */
		valueClick : function () {
			var valueBox = jQuery(this);
			
			return gjUtil.leveledFields.selectValue(valueBox, false);
		},
		
		/**
		 * Function that does the "work" for selecting a specific value, just pass
		 * in the jQuery object with the .leveled_value in question as the selection
		 * 
		 * 
		 */
		selectValue : function (valueBox, scrollToValue) {
			var radio = valueBox.find('input.leveled_radio');
			var valuesBox = valueBox.closest('.leveled_values');
			
			if (!valueBox.length || !radio.length || !valuesBox.length) {
				//something wrong...
				return;
			}
			
			var isSearchCat = valueBox.find('input.leveled_cat_search').length > 0;
			if(isSearchCat) {
				jQuery('#adv_searchCat').val(radio.val());
				gjUtil.searchCategory.categoryChange();
			}
			
			//remove the selected value class from the old selection
			valuesBox.find('.leveled_value.selected_value').removeClass('selected_value');
			
			//we'll use this in a sec... whether it was already checked or not
			var alreadyActive = radio.prop('checked');
			
			//make the radio option clicked
			radio.prop('checked',true);
			
			if (scrollToValue || gjUtil.leveledFields.alwaysScrollToValueOnClick) {
				//figure how much it should be scrolled
				var offset = valueBox.position().top + valuesBox.scrollTop();
				
				//scroll to the offset
				valuesBox.animate({
					scrollTop: offset+'px' 
				}, 'fast');
			}
			
			//set some CSS on the value box...
			valueBox.addClass('selected_value');
			
			//see if we need to populate the next
			var container = valuesBox.closest('.leveled_level_box');
			var next = container.next('.leveled_level_box');
			
			var isCat = container.closest('.leveled_cat').length;
			
			if (!alreadyActive && isCat && !next.length) {
				//create the next box dynamically
				var level = container.closest('div').find('.leveled_level_box').length;
				
				jQuery('<div/>').hide()
					.append(jQuery('<ul class="leveled_values leveled_cat"><li class="leveled_value_empty"></li></ul>'))
					.addClass('leveled_level_box')
					.addClass('leveled_cat_'+level)
					.insertAfter(container);
				next = container.next('.leveled_level_box');
			}
			
			if (next.is(':empty') || next.find('li.leveled_value_empty').length || !alreadyActive) {
				//next box is empty so populate it
				var loadNextUrl = 'AJAX.php?controller=LeveledFields&action=getLevel&parent='+radio.val();
				if (gjUtil.inAdmin) {
					loadNextUrl = '../'+loadNextUrl+'&inAdmin=1';
				}
				if (container.find('.leveled_clear').length) {
					//let it know to populate the clear selection link
					loadNextUrl = loadNextUrl+'&showClearSelection=1';
				}
				if (isCat) {
					//this is actually a category
					loadNextUrl = loadNextUrl+'&cat=1';
					if(isSearchCat) {
						loadNextUrl = loadNextUrl + '&searchcat=1';
					}
				}
				if (jQuery('#listing_types_allowed').length) {
					loadNextUrl = loadNextUrl+'&listing_types_allowed='+jQuery('#listing_types_allowed').val();
				}
				if (jQuery('#recurringpp').length) {
					loadNextUrl = loadNextUrl+'&recurringpp='+jQuery('#recurringpp').val();
				}
				next.load(loadNextUrl, function (responseTxt) {
						if (responseTxt.length) {
							jQuery(this).show('slow');
							gjUtil.leveledFields.init(jQuery(this));
						} else {
							//no values...
							jQuery(this).hide('slow');
						}
						if (jQuery(this).closest('.combined_update_fields').length) {
							//this is on a combined step, so update things
							geoListing.combinedUpdate(jQuery(this).closest('.combined_step_section').attr('id'));
						}
					});
				gjUtil.leveledFields.closeAfter(next);
			}
		},
		
		/**
		 * Closes any boxes after the one selected, emptying any that are not "always open"
		 * @param elem
		 */
		closeAfter : function (elem) {
			var next = elem.next('.leveled_level_box');
			if (next.length) {
				if (next.find('li.leveled_value_empty').length==0) {
					next.hide('slow', function () {jQuery(this).empty();});
				}
				gjUtil.leveledFields.closeAfter(next);
			}
		}
	},
	imageUpload : {
		_ajaxUrl : 'AJAX.php',
		_adminId : 0,
		_userId : 0,
		_maxImages : 0,
		_maxUploadSize : 0,
		_progressProps : {
			lineCap : 'round',
			width: 80,
			height: 80,
			thickness : .2,
			inputColor : '#777777',
			fgColor : '#87CEEB',
			bgColor : '#DDDDDD'
		},
		//msgs set in head by language text
		_msgs : {},
		_pl : null,
		
		observers : function () {
			//image titles
			jQuery('.editImgageTitle').unbind('.imgTitle')
				.on('blur.imgTitle',gjUtil.imageUpload.titleUpdate)
				.on('keyup.imgTitle', function (e) {
					if (e.keyCode == 27) {
						//esc key pressed... cancel
						jQuery(this).text(jQuery(this).prev('input').val())
							.blur();
					} else if (e.keyCode == 13) {
						//trigger blur event which in turn should save the value
						jQuery(this).blur();
					}
				});
			//image sorting
			jQuery('.editImageSort').unbind('.imgSort')
				.on('blur.imgSort',gjUtil.imageUpload.sortUpdate)
				//prevent form submission....
				.on('keydown.imgSort', function (e) {
					if (e.which == 13) {
						e.preventDefault();
						jQuery(this).blur();
					}
				})
				.on('keyup.imgSort', function (e) {
					if (e.keyCode == 27) {
						//esc key pressed... cancel
						jQuery(this).val(jQuery(this).attr('value'));
						e.preventDefault();
					} else if (e.keyCode == 13) {
						//trigger blur event which in turn should save the value
						jQuery(this).blur();
						e.preventDefault();
					}
				});
			//image deleting
			jQuery('.deleteImage').unbind('.imgDel')
				.on('click.imgDel', function (e) {
					e.preventDefault();
					var imgId = this.hash.replace('#','');
					
					//prevent form from submitting until ajax is done
					jQuery('form').unbind('.imgSave').on('submit.imgSave', function(e) {e.preventDefault();});
					
					jQuery.ajax({
						type: 'POST',
						url: gjUtil.imageUpload._ajaxUrl+'?controller=UploadImage&action=delete&adminId='+gjUtil.imageUpload._adminId+'&userId='+gjUtil.imageUpload._userId,
						data: {
							'image_id' : imgId
						}
					}).done(function (response) {
						jQuery('form').unbind('.imgSave');
						if (response.error) {
							gjUtil.imageUpload.handleError(response.error);
							return;
						}
						if (response.msg) {
							gjUtil.addMessage(response.msg, 2000);
						}
						
						if (response.preview) {
							gjUtil.imageUpload.previewUpdate(response.preview);
						}
						if (response.debug) {
							console.log('Debug: '+response.debug);
						}
					}).error(function () {
						//some error deleting...
						jQuery('form').unbind('.imgSave');
						gjUtil.addError(gjUtil.imageUpload._msgs.m500682);
					});
				});
			
			jQuery('.rotateImage').unbind('.imgRot')
				.on('click.imgRot', function (e) {
					e.preventDefault();
					var imgId = this.hash.replace('#','');
					
					//prevent form from submitting until ajax is done
					jQuery('form').unbind('.imgSave').on('submit.imgSave', function(e) {e.preventDefault();});
					
					jQuery.ajax({
						type: 'POST',
						url: gjUtil.imageUpload._ajaxUrl+'?controller=UploadImage&action=rotate&adminId='+gjUtil.imageUpload._adminId+'&userId='+gjUtil.imageUpload._userId,
						data: {
							'image_id' : imgId,
							'degrees' : 270
						}
					}).done(function (response) {
						jQuery('form').unbind('.imgSave');
						if (response.error) {
							gjUtil.imageUpload.handleError(response.error);
							return;
						}
						if (response.debug) {
							console.log('Debug: '+response.debug);
						}
						
						//manually rotate the image here for just this page-load (to show the result of what has already happened on the server)
						var previewImg = jQuery('#imagesPreview_'+imgId+' div.media-preview-image img');
						var currentAngle = previewImg.getRotateAngle();
						if(!currentAngle) { 
							currentAngle = 0;
						}
						
						var rotateTo = Math.round(1*currentAngle + 90);
						previewImg.rotate(rotateTo);
						
						//if the full-size popout image is present, cachebust each rotation position
						previewLink = jQuery('#imagesPreview_'+imgId+' div.media-preview-image a');
						if(previewLink.attr('href').length > 0) {
							if(!previewLink.data('cachebuster')) {
								//store (only) the original href
								previewLink.data('cachebuster',previewLink.attr('href'));
							}
							previewLink.attr('href', previewLink.data('cachebuster') + "?_="+(rotateTo%360));
						}						
					}).error(function () {
						//some error deleting...
						jQuery('form').unbind('.imgSave');
					});
				});
		},
		
		titleUpdate : function () {
			var $this = jQuery(this);
			var title = $this.text().replace("\n",'');
			
			//simple method to generate plain text
			var plain = function (txt) {
				return jQuery('<div>').html(txt).text();
			};
			
			//at this point, the title should be clean...  Go ahead and stick it
			//back in so that newlines and stuff are not displayed for as long
			$this.text(title);
			
			var oldTitle = plain($this.prev('input').val());
			if (title===oldTitle) {
				//nothing to do
				return;
			}
			var imgId = $this.closest('.media-preview').attr('id').replace('imagesPreview_','');
			if (!imgId) {
				//failsafe...
				$this.text(oldTitle);
				return;
			}
			//prevent form from submitting until ajax is done
			jQuery('form').unbind('.imgSave').on('submit.imgSave', function(e) {e.preventDefault();});
			
			jQuery.ajax({
				type: 'POST',
				url: gjUtil.imageUpload._ajaxUrl+'?controller=UploadImage&action=editTitle&adminId='+gjUtil.imageUpload._adminId+'&userId='+gjUtil.imageUpload._userId,
				data: {
					'image_id' : imgId,
					'title' : title,
					'edit_title' : 1
				}
			}).done(function (response) {
				jQuery('form').unbind('.imgSave');
				if (response.error) {
					gjUtil.imageUpload.handleError(response.error);
					return;
				}
				
				if (response.success) {
					//update the image title displayed (to account for anything trimmed off),
					//and also update the hidden input so it knows when changes are made.
					$this.text(plain(response.img_title))
						.prev('input').val(response.img_title);
					$this.siblings('.media-editable-saved').addClass('media-editable-saved-show');
					//after 1 second hide it again
					setTimeout(function () {
						$this.siblings('.media-editable-saved').removeClass('media-editable-saved-show');
					}, 1000);
				}
				
				if (response.debug) {
					console.log('Debug: '+response.debug);
				}
			}).error(function () {
				jQuery('form').unbind('.imgSave');
				//changing title ajax call failed
				gjUtil.addError(gjUtil.imageUpload._msgs.m500689);
			});
		},
		
		sortUpdate : function () {
			var $this = jQuery(this);
			var sort = $this.val();
			var oldSort = $this.attr('value');
			if (sort===oldSort) {
				//nothing to do
				return;
			}
			var imgId = $this.closest('.media-preview').attr('id').replace('imagesPreview_','');
			if (!imgId) {
				//failsafe...
				$this.val(oldSort);
				return;
			}
			//prevent form from submitting until ajax is done
			jQuery('form').unbind('.imgSave').on('submit.imgSave', function(e) {e.preventDefault();});
			
			jQuery.ajax({
				type: 'POST',
				url: gjUtil.imageUpload._ajaxUrl+'?controller=UploadImage&action=sortInput&adminId='+gjUtil.imageUpload._adminId+'&userId='+gjUtil.imageUpload._userId,
				data: {
					'image_id' : imgId,
					'sort' : sort
				}
			}).done(function (response) {
				jQuery('form').unbind('.imgSave');
				if (response.error) {
					gjUtil.imageUpload.handleError(response.error);
					return;
				}
				
				if (response.msg) {
					gjUtil.addMessage(response.msg, 2000);
				}
				
				if (response.preview) {
					gjUtil.imageUpload.previewUpdate(response.preview);
				}
				
				if (response.debug) {
					console.log('Debug: '+response.debug);
				}
			}).error(function () {
				jQuery('form').unbind('.imgSave');
				//Sort ajax failed
				gjUtil.addError(gjUtil.imageUpload._msgs.m500689);
			});
		},
		
		previewUpdate : function (preview) {
			//update preview window
			jQuery('#imagesUploaded').html(jQuery(preview)).find('.lightUpImg').gjLightbox('clickLinkImg');
			
			jQuery('#imagesUploaded').find('div.media-preview-image img').each(function() {
				//add a cachebuster to force redownloading images.
				//That way, images that have been rotated server-side will appear correctly
				jQuery(this).attr('src', jQuery(this).attr('src')+"?_="+jQuery.now());
			});
			
			//figure out how many images are being used
			var currentCount = jQuery('#imagesUploaded > .media-preview').length;
			//update the current number count
			jQuery('#imagesCurrentCount').html(currentCount);
			if (currentCount >= gjUtil.imageUpload._maxImages) {
				//max images reached, hide the button for uploads
				jQuery('#imagesPickfiles').hide('fast');
			} else {
				jQuery('#imagesPickfiles').show('fast');
			}
			
			//need to re-bind the image title stuff
			gjUtil.imageUpload.observers();
		},
		
		plFilesAdded : function (up, files) {
			//first, make sure they are not uploading more than they should...
			var currentCount = jQuery('#imagesUploaded > .media-preview').length;
			var maxFiles = gjUtil.imageUpload._maxImages - currentCount;
			if (maxFiles < files.length) {
				//too many in the queue!
				filesKept = [];
				jQuery.each(files, function (i, file) {
					if ((i+1) > maxFiles) {
						//remove this one
						up.removeFile(file);
					} else {
						filesKept[i]=file;
					}
				});
				files = filesKept;
				gjUtil.addMessage(gjUtil.imageUpload._msgs.tooManyFiles+' '+gjUtil.imageUpload._maxImages, 2000);
			}
			var dummyCss = jQuery('#imagesProgressBarCss');
			var props = gjUtil.imageUpload._progressProps;
			if (dummyCss.length) {
				props.width = props.height = dummyCss.width() || props.width;
				
				//OK thickness is supposed to be something from 0 to 1...  use height
				//and divide by 100...
				var h = dummyCss.height() / 100;
				if (h > 0 && h <= 1) {
					props.thickness = h;
				}
				props.fgColor = dummyCss.css('color') || props.fgColor;
				props.bgColor = dummyCss.css('backgroundColor') || props.bgColor;
			}
			
			var thumbsStarted = 0, upStarted = false;
			
			jQuery.each(files, function(i, file) {
				if (file.status === plupload.FAILED) {
					//failed before it even got started!
					return;
				}
				jQuery('#imagesFilelist').append(
					'<div id="' + file.id + '" class="media-queue-entry clearfix">'
						+ '<div class="media-queue-progress"><input value="0" id="progress_'+file.id+'"></div>'
						+ '<div class="queue-thumb"></div>'
						+ file.name + ' (' + plupload.formatSize(file.size) + ')'
						+ '<div class="queue-message"></div>'
					+'</div>');
				jQuery('#progress_'+file.id).gjProgress(props);
				
				
				//provide an image preview during the upload
				var img;
				img = new o.Image;
				
				img.onload = function() {
					img.embed(jQuery('#' + file.id + ' .queue-thumb')[0], { 
						width: 80, 
						height: 80, 
						crop: true,
						swf_url: mOxie.resolveUrl(up.settings.flash_swf_url),
						xap_url: mOxie.resolveUrl(up.settings.silverlight_xap_url)
					});
				};
				
				img.onembedded = function() {
					thumbsStarted--;
					if (!upStarted && thumbsStarted<=0) {
						//start the upload, all the thumbs are loaded!
						upStarted=true;
						up.start();
					}
					img.destroy();
				};
				
				img.onerror = function() {
					// error logic here
					thumbsStarted--;
					if (!upStarted && thumbsStarted<=0) {
						//start the upload, all the thumbs are loaded!
						upStarted=true;
						up.start();
					}
				};
				thumbsStarted++;
				img.load(file.getSource());
			});
		},
		
		plUploadProgress : function (up, file) {
			jQuery('#progress_'+file.id).val(file.percent).trigger('change');
			if (file.percent>90) {
				//if it's more than 90%... go ahead and pretend it's being processed
				//because sometimes the 100% complete is skipped for some reason
				jQuery('#progress_'+file.id).val('...').attr({ 'title' : gjUtil.imageUpload._msgs.m500667 });
			}
		},
		plBeforeUpload : function (up, file) {
			//save the filename in a POST parameter, since HTML5 sends the filename
			//as "blob" when the image is processed.
			up.settings.multipart_params = {filename : file.name};
		},
		
		plError : function (up, err) {
			gjUtil.imageUpload.handleError(err);
			
			up.refresh(); // Reposition Flash/Silverlight
		},
		
		plFileUploaded : function (up,file,info) {
			jQuery('#progress_'+file.id).val(100).trigger('change');
			
			if (info && info.response) {
				var response = jQuery.parseJSON(info.response);
				if (response.error) {
					gjUtil.imageUpload.handleError(response.error);
					return;
				}
				
				if (response.preview) {
					gjUtil.imageUpload.previewUpdate(response.preview);
				}
				if (response.msg) {
					//add message to the message note thingy in the queue
					jQuery('#'+file.id+' .queue-message').html(response.msg);
				}
				
				if (response.debug) {
					console.log('Debug: '+response.debug);
				}
			} else {
				//just trigger it with nothing 
				gjUtil.imageUpload.handleError({error : 'null response'});
			}
			
			//make it hide after a while
			setTimeout(function () {
				jQuery('#'+file.id).hide('slow');
				}, 500);
		},
		handleError : function (error) {
			//start with prefix of "error"...
			var msg = gjUtil.imageUpload._msgs.m500677;
			if (error.code != -100) {
				//some error generated by plupload itself (vs. error generated by Geo)
				//Might want to handle specific cases here...
				if (error.code === plupload.FILE_SIZE_ERROR) {
					//file size too big, give specific message
					msg += gjUtil.imageUpload._msgs.m500818+plupload.formatSize(gjUtil.imageUpload._maxUploadSize);
				} else {
					//for the rest of them use the generic error
					msg += gjUtil.imageUpload._msgs.m500678;
				}
				//add some debug code...
				msg += ' (';
				if (error.file) {
					//show the file name as well
					msg += error.file.name + ' : ';
				}
				msg += error.code+')';
			} else {
				//error generated by Geo software so go ahead and use the message given
				msg += error.message;
			}
			if (error.file) {
				//Note that sometimes this can happen before the file is in the
				//queue...  In which case this would not be populated
				jQuery('#' + error.file.id + ' .queue-message').html(msg);
			}
			gjUtil.addError(msg);
		},
		//this is over-ridden by head
		init : function () {},
		_init : function () {
			
			jQuery('#imagesUploaded').sortable({
				delay: 200,
				handle: '.slot-label',
				update: function (event, ui) {
					var params = jQuery('#imagesUploaded').sortable('serialize');
					
					jQuery.ajax({
						type: 'POST',
						url: gjUtil.imageUpload._ajaxUrl+'?controller=UploadImage&action=sortDrag&adminId='+gjUtil.imageUpload._adminId+'&userId='+gjUtil.imageUpload._userId,
						data: params
					}).done(function (response) {
						if (response.error) {
							gjUtil.addError(response.error.message+' ('+response.error.code+')');
							return;
						}
						
						if (response.preview) {
							gjUtil.imageUpload.previewUpdate(response.preview);
						}
						if (response.msg) {
							gjUtil.addMessage(response.msg, 2000);
						}
						
						if (response.debug) {
							console.log('Debug: '+response.debug);
						}
					}).error(function () {
						gjUtil.addError(gjUtil.imageUpload._msgs.m500689);
					});
				}
			});
		}
	},
	_initMsg : function () {
		if (!jQuery('#_msgDialog').length) {
			//create a container to put dialogs in
			jQuery(document.body).append('<div id="_msgDialog">empty</div>');
			jQuery('#_msgDialog').dialog({
				autoOpen: false,
				show:{effect : 'fade',duration:400},
				hide:{effect : 'fade',duration:400}
			});
		}
		//return the container, but make sure it is closed so it can be updated
		return jQuery('#_msgDialog').dialog('close');
	},
	addMessage : function (msg, autoClose) {
		var box = gjUtil._initMsg();
		box.html(msg)
			.dialog('open');
		if(autoClose > 0) {
			setTimeout(function(){if(box.dialog('isOpen')){box.dialog('close');}}, autoClose);
		}
	},
	addError : function (msg) {
		gjUtil.addMessage(msg);
	}
};

/* Mini-object for handling loading/unloading wysiwyg's
 * TODO: move this into a plugin or something
 */
var gjWysiwyg = {

	
	loadTiny : function () {
		//This meant to be over-written by admin/client side
		return false;
	},
	
	removeTiny : function () {
		if(typeof tinyMCE !== 'undefined') tinyMCE.remove();
		localStorage.tinyMCE = 'off';
	},
	
	//This one used to re-load tiny after it has been removed
	restoreTiny : function () {
		gjWysiwyg.loadTiny();
	},
	
	toggleTinyEditors : function () {
		if(localStorage.tinyMCE=='on') {
			gjWysiwyg.removeTiny();
		} else {
			gjWysiwyg.restoreTiny();
		}
	}
};

//For older scripts that still do things old way
var getCookie = gjUtil.getCookie;
