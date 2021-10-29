// 7.5.3-36-gea36ae7

/**
 * This is a lightbox that is very fancy-like
 */
(function (jQuery) {
	
	/**
	 * This is used internally, with private scope, just as convienience for
	 * helping to organize things.
	 */
	var internal = {
		_hiddenElems : []
	};
	/**
	 * Primary callback function for onOpen that hides all of the elements that
	 * have issues in some browsers with an overlay
	 */
	internal.onOpen = function () {
		jQuery('object, select, embed').each (function () {
			if (this.style.visibility != 'hidden') {
				this.style.visibility = 'hidden';
				internal._hiddenElems[internal._hiddenElems.length] = this;
			}
		});
	};
	
	/**
	 * Primary callback function for onClose that un-hides all the stuff that
	 * was hidden by onOpen.
	 */
	internal.onClose = function () {
		jQuery.each(internal._hiddenElems, function () {
			this.style.visibility = 'visible';
		});
		internal._hiddenElems = [];
	};
	
	/**
	 * Called after the lightbox is open.  In it's own function because sometimes
	 * it happens right away (when opening lightbox when it was previously closed),
	 * and sometimes it is deffered until animations are done (when it is transitioning
	 * from one set of contents to another smoothly).
	 * 
	 * @param data The data object
	 * @param isNew Whether we need to fade the box in or not
	 */
	internal.afterOpen = function (data, isNew) {
		var alreadyOpen = !data.navBarHidden;
		//watch any image links...
		data.box.find('a.lightUpImg').gjLightbox('clickLinkImg');
		//watch normal links...
		data.box.find('a.lightUpLink').gjLightbox('clickLink');
		//watch disabled links..
		data.box.find('.lightUpDisabled').gjLightbox('clickDisabled');
		
		//umm, something to do with closing or opening in new window
		data.box.find('.lightUpBox_imageBox img,img.lightUpBigImage,.closeLightUpBox,.closeBoxX').each (function (){
			elem = jQuery(this);
			if (elem.parent().is('a.lightUpBox_link')) {
				//this is something that links somewhere, instead of causing it
				//to close, instead open the link in new window
				elem.parent('a.lightUpBox_link').on('click.gjLightbox', function (action) {
					window.open(this.href);
					return false;
				});
			} else {
				//close it.. note that this is the reason we use if, so that
				//we have an else to go into... we do not close the box fi the parent is
				//certain class.
				elem.gjLightbox('clickClose');
			}
		});
		
		//call any onComplete callbacks
		jQuery.each(data.onComplete, function() {this();});
		
		//stuff for calendars, for now keep using prototype to do that part,
		//until we get chance to replace all the calendars with jquery version
		gjUtil.initDatePicker();
		
		//special stuff for nav
		data.box.find('.lightUpBox_navigation').each(function () {
			slideshow.navObserver(data, jQuery(this));
			jQuery(this).css({opacity : (alreadyOpen)? 0.9: 0.08});
		});
		//make any disabled links grayed out
		data.box.find('.disabledLink').css({opacity: 0.4});
		
		//Make it draggable, if it finds applicable anchor
		if (data.box.find('.lightUpMover,.lightUpTitle').length) {
			//make it slightly see-through when dragging, for fun and profit
			data.box.draggable({
				handle : '.lightUpMover,.lightUpTitle',
				opacity : 0.75
			});
		}
		if (isNew) {
			//need to fade the box in
			data.box.fadeIn('slow');
		}
	};
	
	/**
	 * Another private scope object, used for organisation and such.  Holds
	 * things dealing with the image slideshow that takes place when the lightbox
	 * is used to show group of images for a listing.
	 */
	var slideshow = {};
	
	/**
	 * Sets up the navigation for the slideshow.
	 * @param data
	 * @param element
	 */
	slideshow.navObserver = function (data, element) {
		if (!element) {
			return;
		}
		//so we can reference it later easily
		data.navBar = element;
		
		//when hover over image
		if (data.box) {
			data.box.on('mouseenter.gjLightbox', function () {
				if (data.navBar) {
					data.navBarHidden = false;
					data.navBar.fadeTo('fast',0.9);
				}
			});
			data.box.on('mouseleave.gjLightbox', function () {
				if (data.navBar){
					data.navBarHidden = true;
					data.navBar.fadeTo('fast',0.08);
				}
			});
		}
		
		//play and pause observers
		var play = data.navBar.find('a.playLink');
		var pause = data.navBar.find('a.pauseLink');
		var disabledPlay = data.navBar.find('span.noplayLink');
		
		if (!play.length || !pause.length || !disabledPlay.length) {
			//couldn't find play button?  can't do anything beyond this point.
			return;
		}
		
		//clear any observers, just in case they are already being observed
		play.unbind('.gjLightbox');
		pause.unbind('.gjLightbox');
		//watch them for clicks
		play.on('click.gjLightbox', function () {
			jQuery(this).hide()
			//relies on play being first button
				.next().show();
			slideshow.start(data);
			return false;
		});
		pause.on('click.gjLightbox', function () {
			jQuery(this).hide()
			//relies on play being first button
				.prev().show();
			slideshow.stop(data);
			return false;
		});
		//init the play pause buttons
		slideshow.initPlayPause(data);
	};
	
	/**
	 * Starts up the slideshow.
	 * @param data
	 */
	slideshow.start = function (data) {
		if (data.slideshowPlaying) {
			//nothin to do, it's already started.
			return;
		}
		data.slideshowPlaying = true;
		slideshow._timeout(data);
	};
	
	/**
	 * Super-internal FTW!  Used by slideshow.start to start the timeout thingy
	 * that makes it go to the next image in the slideshow.  In it's own method
	 * because it's used in different places.
	 * @param data
	 */
	slideshow._timeout = function (data) {
		data._slideshow = setTimeout(function () {slideshow._nextImage(data);},1000*data.slideshowDelay);
	};
	
	/**
	 * Stop the slideshow from going on
	 * @param data
	 */
	slideshow.stop = function (data) {
		clearTimeout(data._slideshow);
		
		data.slideshowPlaying = false;
		data._startUpSlideshow = false;
	};
	
	/**
	 * Super-internal!  Used to get the next image for the slideshow.
	 * @param data
	 */
	slideshow._nextImage = function (data) {
		if (!data.nextImageId || !data.slideshowPlaying) {
			//nothing to do
			return;
		}
		//generate URL
		var url = 'get_image.php?id='+data.nextImageId+'&playing=1';
		data.nextImageId = 0;//so it doesn't keep just refreshing itself
		data._startUpSlideshow = true;
		
		if (data.navBar) {
			//hide the navigation so it can't be clicked during transition, since
			//clicks will have no effect during that time.
			data.navBar.hide();
		}
		jQuery(document).gjLightbox('get',url);
	};
	/**
	 * Sets up the play and pause buttons in the navigation
	 * @param data
	 */
	slideshow.initPlayPause = function (data) {
		var play = data.navBar.find('a.playLink');
		var pause = data.navBar.find('a.pauseLink');
		var disabledPlay = data.navBar.find('span.noplayLink');
		
		if (!play.length || !pause.length || !disabledPlay.length) {
			//couldn't find play button?  can't do anything beyond this point.
			//alert('no can do anything.'+data.navBar);
			return;
		}
		
		if (data.nextImageId) {
			//we can play!
			disabledPlay.hide();
			if (data.slideshowPlaying) {
				//show pause
				pause.show();
			} else {
				play.show();
			}
		}
	};
	/**
	 * These of course, are the main methods that can be accessed the standard way
	 * for any jQuery plugin.
	 */
	var methods = {
		/**
		 * Sets up the lightbox on the document and the default settings.  Note that
		 * this will ONLY work on the document, it will not work for any other
		 * jQuery selector.  The same goes for anything that acts on the lightbox.
		 * 
		 * @param options
		 * @returns jQuery so can chain this with other stuff.
		 */
		init : function (options) {
			return this.each(function(){
				var $this=jQuery(this),
					data = $this.data('gjLightbox');
				
				if (!$this.is(document)) {
					//this is not valid to call on anything except for the document
					jQuery.error('This is not a valid selector.  Can only initialize the lightbox on the document.');
					return;
				}
				
				if (!data) {
					$this.data('gjLightbox',$this.extend({
						//default options here
						slideshowDelay : 5,
						overlayOpacity : 0.6,
						boxState : 'closed',
						onOpen : [internal.onOpen],
						onClose : [internal.onClose],
						onComplete : [],
						nextImageId : 0,
						_startUpSlideshow : false,
						box : jQuery('<div />', {
							'class' : 'gjLightbox'
						}).prependTo('body').hide(),
						overlay : jQuery('<div />', {
							'class' : 'gjLightboxOverlay',
							click: function () {$this.gjLightbox('close');}
						}).prependTo('body').hide()
					}, options));
					//re-get the data so it is the data object that will be used
					//(and more importantly, the one that is updated) in the rest of
					//the plugin.
					data = $this.data('gjLightbox');
					
					//go ahead and bind the esc character to close lightbox
					$this.on('keydown.gjLightbox', function (e){
						if (e.keyCode==27) {
							//escape key pressed
							if (data.boxState=='open') {
								//lightbox is open, close it
								jQuery(document).gjLightbox('close');
								//and stop the event from bubbling up further
								return false;
							}
						}
					});
					
					//also watch for resize window
					jQuery(window).resize(function () {
						jQuery('.gjLightboxOverlay').css({
							width : jQuery(document).width()+'px',
							height : jQuery(document).height()+'px'
						});
					});
				}
			});
		},
		/**
		 * Used internally, just un-binds any observers specific to this plugin
		 */
		destroy : function () {
			return this.each(function() {
				//remove binding for... well any of the binding we may have added
				jQuery(this).unbind('.gjLightbox');
			});
		},
		
		/**
		 * Meant to mimic jQuery.get() with a small change, the contents are automatically
		 * loaded into the lightbox and displayed.
		 * 
		 * @param href
		 * @param options Passed to open() method, see that for more info.
		 * @returns chained jQuery()
		 */
		get : function (href, options) {
			return this.each(function(){
				var $this=jQuery(this),
					data=$this.data('gjLightbox');
				
				if (!href) {
					jQuery.error('HREF link not specified, nothing to get!');
					return;
				}
				
				if (!data) {
					//has not yet been initialized
					$this.gjLightbox();
					data=$this.data('gjLightbox');
					if (!data) {
						//problem initializing, do not proceed
						return;
					}
				}
				
				//get contents of link
				jQuery.get(href, function (contents) {
					$this.gjLightbox('open', contents, options);
					if (data._startUpSlideshow) {
						data._startUpSlideshow = false;
						slideshow._timeout(data);
					}
				}, 'html');
			});
		},
		
		/**
		 * Much like get, except that this uses POST to get the contents to display
		 * in the lightbox.
		 * 
		 * @param href The URL to post to
		 * @param params The POST parameters to send
		 * @param options Passed to open() method, see that for more info.  Added
		 *   in version 7.2.0
		 * @returns Chained jQuery()
		 */
		post : function (href, params, options) {
			return this.each(function(){
				var $this=jQuery(this),
					data=$this.data('gjLightbox');
				
				if (!href) {
					jQuery.error('HREF link not specified, nothing to get!');
					return;
				}
				
				if (!data) {
					//has not yet been initialized
					$this.gjLightbox();
					data=$this.data('gjLightbox');
					if (!data) {
						//problem initializing, do not proceed
						return;
					}
				}
				
				//get contents of link
				jQuery.post(href, params, function (contents) {
					$this.gjLightbox('open', contents, options);
					if (data._startUpSlideshow) {
						data._startUpSlideshow = false;
						slideshow._timeout(data);
					}
				}, 'html');
			});
		},
		
		/**
		 * Similar to get(), except this is specifically for getting an image directly
		 * instead of getting HTML that might contain a reference to an image.
		 * This will display the image in the lightbox.
		 * 
		 * @param href URL to the image to show
		 * @param options Passed along to open()
		 * @returns Chained jQuery()
		 */
		getImg : function (href,options) {
			return this.each(function(){
				var $this=jQuery(this),
					data=$this.data('gjLightbox');
				
				if (!href) {
					jQuery.error('HREF link not specified, nothing to get!');
					return;
				}
				
				if (!data) {
					//has not yet been initialized
					$this.gjLightbox();
					data=$this.data('gjLightbox');
					if (!data) {
						//problem initializing, do not proceed
						return;
					}
				}
				var biggerImg = jQuery('<img />',{
					src : href,
					alt : '',
					'class' : 'lightUpBigImage'
				});
				$this.gjLightbox('open', biggerImg, options);
			});
		},
		
		/**
		 * Opens the lightbox (if not currently open) and displays the given conents
		 * inside the lightbox.  Note that contents must be encapsulated by HTML
		 * as it uses .html() to insert into the lightbox.  See the documentation
		 * for jQuery's .html() for more info.  (in particular, if text seems to
		 * be stripped, you probably need to stick the text inside a <div>)
		 * 
		 * @param contents
		 * @param options Object containing the options.  Current default options:
		 *   { useOverlay : true } (if set to false, overlay will not be used
		 * @returns Chained jQuery()
		 */
		open : function (contents, options) {
			return this.each(function(){
				var $this=jQuery(this),
					data=$this.data('gjLightbox'),
					params = jQuery.extend({
						useOverlay : true
					}, options);
				
				if (!data) {
					//has not yet been initialized
					$this.gjLightbox();
					data=$this.data('gjLightbox');
					if (!data) {
						//problem initializing, do not proceed
						return;
					}
				}
				//now on to the actual business of showing the contents...
				
				var newBox = jQuery('<div />').html(contents);
				
				if (data.boxState=='closed') {
					//box is currently closed, so open it up
					
					//first call any open callbacks
					jQuery.each(data.onOpen, function() {this();});
					
					if (params.useOverlay) {
						//set dimensions of the overlay
						data.overlay.css({
							width : jQuery(document).width()+'px',
							height : jQuery(document).height()+'px'
						}).fadeTo('fast',data.overlayOpacity);
					}
					
					newImg = newBox.find('.lightUpBox_imageBox img,img.lightUpBigImage');
					if (newImg.length) {
						if (newImg.width()==0) {
							newImg.load(function () {
								//move it to middle
								if (data.boxState=='open') {
									//once it's done loading image, if the lightbox is still open,
									//move it to the middle again
									data.box.gj('moveToMiddle');
								}
							});
						}
					}
					
					//just in case the version that hard-codes the width/height is used,
					//reset width/height here to not be set
					data.box.css({
						width: '',
						height: ''
					});
					
					//shove the contents in the box and show it...
					data.box.html(newBox)
						.gj('moveToMiddle');
					
					internal.afterOpen(data,true);
				} else {
					//it's already open...  we want a "smooth" transition here!
					//OK get the current width and "stick" it
					var oldInner = data.box.children(':first');
					
					//Need to offset width / height since using border-box box sizing
					//(but only if site has not changed this for some reason)
					var borderOffset = 0;
					if (data.box.css('box-sizing')=='border-box') {
						//account for border offset
						borderOffset += parseInt(data.box.css('border-left-width'))*2;
					}
					var startingD = {
						width : Math.max(150,oldInner.width())+borderOffset,
						height : Math.max(150,oldInner.height())+borderOffset
					};
					
					//"stick" the width before we get rid of the innards
					data.box.css({
						width: startingD.width+'px',
						height: startingD.height+'px'
					});
					
					//fade out the innards
					oldInner.fadeOut('slow', function () {
						//OK hide the outer box
						newBox.hide();
						//shove it in the page
						data.box.html(newBox);
						
						//add the observer
						var newImg = newBox.select('.lightUpBox_imageBox img');
						
						var morphingTime = function () {
							//get new dimensions
							var newD = {
								width : Math.max(150,newBox.width())+borderOffset,
								height : Math.max(150,newBox.height())+borderOffset
							};
							//make sure overflow isn't shown
							data.box.css({overflow: 'hidden'});
							
							//now morph it into correct size!
							data.box.animate({
								width : newD.width+'px',
								height : newD.height+'px'
							}, function () {
								//when done moving it to the proper width / height,
								//unset CSS width and height to allow them to adjust
								data.box.css({width:'',height:''});
							});
						};
						if (newImg.length) {
							if (newImg.width() > 0) {
								//it's already loaded
								morphingTime();
							} else {
								//size is 0, so bind the morph to when the image
								//is loaded
								newImg.on('load.gjLightbox', morphingTime);
							}
						}
						internal.afterOpen(data,false);
						//fade in the new box
						newBox.fadeIn('fast');
						//destroy the old data to free up the memory
						oldInner.remove();
					});
				}
				
				//set state to open
				data.boxState = 'open';
			});
		},

		/**
		 * Easy way to tell if a lightbox is open or not
		 * @returns Boolean
		 */
		isOpen : function() {
			var $this=jQuery(this),
			data = $this.data('gjLightbox');
			if(data && data.boxState=='open') {
				return true;
			}
			return false;
		},
		
		/**
		 * Closes the lightbox and restores everything to normal.
		 * @param options Currently there are no valid options that can be passed in.
		 * @returns Chained jQuery()
		 */
		close : function (options) {
			return this.each(function () {
				var $this=jQuery(this),
					data = $this.data('gjLightbox');
				if (!data) {
					//it should already be initialized if trying to close it!
					$this.gjLightbox();
					data = $this.data('gjLightbox');
					if (!data) {
						//initialize went wrong, don't do anything
						return;
					}
				}
				if (data.boxState=='closed') {
					//already closed
					//jQuery.error('lightbox already closed.');
					return;
				}
				//get rid of the overlay
				data.overlay.fadeOut('fast');
				
				//close the box
				data.box.fadeOut('fast', function () {
					//go through all the onClose callbacks
					jQuery.each(data.onClose, function() {this();});
				});
				
				//stop the slideshow
				slideshow.stop(data);
				
				//change the state
				data.boxState = 'closed';
			});
		},
		
		/**
		 * Use to add click observer on all of the matched elements to open the
		 * links in lightbox.  This expects to be an <a> tag with the href pointing
		 * to the URL of the contents to load in the lightbox.
		 */
		clickLink : function () {
			return this.each(function () {
				jQuery(this).unbind('.gjLightbox').on('click.gjLightbox', function () {
					jQuery(document).gjLightbox('get', jQuery(this).attr('href'));
					//make sure the ones that are links or what not don't continue
					return false;
				});
			});
		},
		
		/**
		 * Adds a click observer to all matched elements that opens an image in
		 * the lightbox.  This expects to be an <a> tag with the href pointing to
		 * the URL of the image to open.
		 * @returns Chained jQuery()
		 */
		clickLinkImg : function () {
			return this.each(function() {
				jQuery(this).unbind('.gjLightbox').on('click.gjLightbox', function() {
					jQuery(document).gjLightbox('getImg', jQuery(this).attr('href'));
					//make sure the ones that are links or what not don't continue
					return false;
				});
			});
		},
		
		/**
		 * Adds click observer that when clicked, will close the lightbox.
		 * @returns Chained jQuery()
		 */
		clickClose : function () {
			return this.each(function() {
				jQuery(this).unbind('.gjLightbox').on('click.gjLightbox', function() {
					jQuery(document).gjLightbox('close');
					//make sure the ones that are links or what not don't continue
					return false;
				});
			});
		},
		
		/**
		 * DEPRECATED: This was implemented in the old prototype version, but doesn't
		 * look like it is used anywhere in the core code anymore.  It may be removed
		 * in a future release, so do not rely on it.
		 * @returns Chained jQuery()
		 */
		clickDisabled : function () {
			return this.each(function() {
				jQuery(this).unbind('.gjLightbox')
					.on('click.gjLightbox', function () { return false; })
					.css({opacity:0.3})
					.addClass('lightboxLinkDisabledProcessed');
			});
		},
		
		/**
		 * Start up the slideshow
		 * @returns Chained jQuery()
		 */
		startSlideshow : function () {
			return this.each(function(){
				var $this=jQuery(this),
					data=$this.data('gjLightbox');
				if (!data) {
					//has not yet been initialized
					$this.gjLightbox();
					data=$this.data('gjLightbox');
					if (!data) {
						//problem initializing, do not proceed
						return;
					}
				}
				
				slideshow.start(data);
			});
		},
		
		/**
		 * Stop the slideshow
		 * @returns Chained jQuery()
		 */
		stopSlideshow : function () {
			return this.each(function(){
				var $this=jQuery(this),
					data=$this.data('gjLightbox');
				if (!data) {
					//has not yet been initialized
					$this.gjLightbox();
					data=$this.data('gjLightbox');
					if (!data) {
						//problem initializing, do not proceed
						return;
					}
				}
				
				slideshow.stop(data);
			});
		}
	};
	/**
	 * This part makes the plugin work in jQuery.
	 */
	jQuery.fn.gjLightbox = function (method) {
		//Method calling logic
		if (methods[method]) {
			return methods[method].apply(this,Array.prototype.slice.call(arguments,1));
		} else if (typeof method === 'object' || ! method) {
			return methods.init.apply(this,arguments);
		} else {
			jQuery.error('Method '+method+' does not exist on jQuery.gjLightbox');
		}
	};
}(jQuery));
