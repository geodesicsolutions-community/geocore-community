// 7.3beta2-61-g2bc2314

/**
 * Simple plugin, expects to have a number of divs in the selection, it will cycle
 * through those dives fading one in and another out.
 *     
 * If new to jQuery plugins, see the following to get aquanted:
 * http://docs.jquery.com/Plugins/Authoring
 */
(function (jQuery) {
	var methods = {
		init : function (options) {
			return this.each(function(){
				var $this=jQuery(this);
				
				if (!$this.find('div.active').length) {
					$this.find('div:first').addClass('active');
				}
				
				$this.find('div:not(.active)').hide();
				$this.find('div.active').show();
				
				//do init stuff here
				setInterval(function () {
					var active = $this.find('div.active');
					if (!active.length) {
						active = $this.find('div:last');
					}
					var next = active.next().length ? active.next()
							: $this.find('div:first');
					active.addClass('last_active');
					next.addClass('active');
					next.fadeIn(1000);
					active.fadeOut(1000, function () {
						active.removeClass('active last_active');
					});
				}, 5000);
			});
		},
	};
	
	jQuery.fn.gjImageFade = function (method) {
		//Method calling logic
		if (methods[method]) {
			return methods[method].apply(this,Array.prototype.slice.call(arguments,1));
		} else if (typeof method === 'object' || ! method) {
			return methods.init.apply(this,arguments);
		} else {
			jQuery.error('Method '+method+' does not exist on jQuery.gjImageFade');
		}
	};
}(jQuery));