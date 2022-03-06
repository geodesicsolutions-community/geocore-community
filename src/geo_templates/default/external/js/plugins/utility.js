// 7.5.3-36-gea36ae7

/**
 * These are a few simple utility functions.  Note that it uses namespace of
 * simply gj as a shortcut, although gjUtility will work as well as an alias.
 */
(function (jQuery) {
    var methods = {
        /**
         * Not used as this is basically a wrapper plugin for a bunch of random
         * utility thingies that are too "simple" to get their own dedicated plugins
         *
         *
         * @param options
         * @returns jQuery
         */
        init : function (options) {
            //Init doesn't need to do anything..
            return this;
        },

        /**
         * Finds the max width out of the selection, and sets that same width on
         * the rest of the elements in the selection.
         *
         * Example usage:
         * jQuery('li.className').gj('setMaxWidth');
         *
         * @returns jQuery
         */
        setMaxWidth : function () {
            return this.width(Math.max.apply(this, jQuery.map(this, function (e) {
                return jQuery(e).width();
            })));
        },

        /**
         * Finds the max height out of the selection, and sets that same height on
         * the rest of the elements in the selection.
         *
         * Example usage:
         * jQuery('li.className').gj('setMaxHeight');
         *
         * @returns jQuery
         */
        setMaxHeight : function () {
            return this.height(Math.max.apply(this, jQuery.map(this, function (e) {
                return jQuery(e).height();
            })));
        },

        /**
         * Gets the max width out of all of the elements in the selection.
         *
         * Example usage:
         * var maxWdith = jQuery('li.className').gj('getMaxWidth');
         *
         * @returns int
         */
        getMaxWidth : function () {
            return Math.max.apply(this, jQuery.map(this, function (e) {
                return jQuery(e).width();
            }));
        },

        /**
         * Gets the max height out of all of the elements in the selection.
         *
         * Example usage:
         * var maxHeight = jQuery('li.className').gj('getMaxHeight');
         *
         * @returns int
         */
        getMaxHeight : function () {
            return Math.max.apply(this, jQuery.map(this, function (e) {
                return jQuery(e).height();
            }));
        },

        /**
         * Moves each of the elements in the selection to the middle of the viewport,
         * taking into account the current scroll offset.
         *
         * Example usage:
         * jQuery('div.className').gj('moveToMiddle');
         *
         * @returns jQuery
         */
        moveToMiddle : function () {
            return this.each(function () {
                var $this = jQuery(this);
                var vpWidth = jQuery(window).width();
                var vpHeight = jQuery(window).height();

                var elWidth = $this.outerWidth() || 200;
                var elHeight = $this.height() || 200;
                offsetLeft = 0;
                if (vpWidth > elWidth) {
                    var offsetLeft = Math.max(0, ((vpWidth - elWidth) / 2) + jQuery(window).scrollLeft());
                }
                var offsetTop = Math.max(0, ((vpHeight - elHeight) / 2) + jQuery(window).scrollTop());
                //make sure it is absolute, it won't work at all otherwise....
                if ($this.css('position') != 'absolute') {
                    $this.css({position: 'absolute'});
                }

                //make sure it's parent is body...
                if ($this.parent().is('body')) {
                    //move it to be at the top level
                    $this.appendTo('body');
                }
                var locationCss = {
                    left : offsetLeft + 'px',
                    top : offsetTop + 'px'
                };

                if ($this.is(':visible')) {
                    //animate it
                    $this.animate(locationCss);
                } else {
                    //just move it there
                    $this.css(locationCss);
                }
            });
        },

        /**
         * Filter out all elements whose value does not match the value passed in.
         *
         * Example usage:
         * var selection = jQuery('input.className').gj('filterValue','someValue')
         *
         * That will return jquery selection with all inputs that the value does not
         * equal someValue filtered out
         *
         * @param valueToCheck The value to check against.  Uses .val() to check,
         *   so the value can be values that the .val() would return.
         * @returns jQuery object filtered by only items matching the value
         * @since Version 7.2.0
         */
        filterValue : function (valueToCheck) {
            if (typeof valueToCheck === 'undefined') {
                return this.hasClass('gjcheckthatwillfail');
            }
            return this.filter(function () {
                return jQuery(this).val() == valueToCheck;
            });
        }
    };

    jQuery.fn.gj = jQuery.fn.gjUtility = function (method) {
        //Method calling logic
        if (methods[method]) {
            return methods[method].apply(this,Array.prototype.slice.call(arguments,1));
        } else if (typeof method === 'object' || ! method) {
            return methods.init.apply(this,arguments);
        } else {
            jQuery.error('Method ' + method + ' does not exist on jQuery.gj');
        }
    };
}(jQuery));
