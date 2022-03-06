// 7.5.3-36-gea36ae7

/**
 * This is a simple carousel that the software makes use of
 */
(function (jQuery) {
    var methods = {
        /**
         * Initializes the carousel on the selection, as long as it contains
         * elements that the carousel knows how to work with.  Specifically, that
         * it has at least 2 "gallery_row" elements to slide between.
         *
         * @param options
         * @returns Chained jQuery()
         */
        init : function (options) {
            return this.each(function () {
                var $this = jQuery(this),
                    data = $this.data('gjSimpleCarousel');

                if (!data) {
                    $this.data('gjSimpleCarousel',$this.extend({
                        auto_slide : true,
                        hover_pause : true,
                        key_slide : true,
                        auto_slide_seconds : 5,
                        dot_text : '&bull;'
                    }, options));
                    data = $this.data('gjSimpleCarousel');
                }

                var sections = $this.find('.gallery_row').length;
                if (sections < 2) {
                    //do not bother...  only one or 0 sections
                    return;
                }

                //figure out the dots before doing any cloning.
                var dot = '<span class="gallery_carousel_dot"> ' + data.dot_text + ' </span>';
                var dotActive = '<span class="gallery_carousel_dot_active"> ' + data.dot_text + ' </span>';

                var dots = dotActive;
                for (var i = 1; i < $this.find('.gallery_row').length; i++) {
                    dots += dot;
                }

                if (sections < 3) {
                    //needs to have at least 3, one for right before and one for
                    //after...  So needs to close the 2 sections so they show correctly
                    $this.find('.gallery_row').clone().insertAfter($this.find('.gallery_row:last'));
                }

                //set up each row to be inline
                var row_width = $this.outerWidth(true);
                $this.find('.gallery_row').css({'display':'inline-block', 'width':row_width + 'px'});

                //show the dots
                $this.find('.leftScroll').after(dots);

                //Now add images if needed
                $this.find('.galleryScroll').show();

                //bind them
                $this.find('.leftScroll').click(function () {
                    jQuery(this).parents('.listing_set.gallery').gjSimpleCarousel('slide',{where:'left'});
                });

                $this.find('.rightScroll').click(function () {
                    jQuery(this).parents('.listing_set.gallery').gjSimpleCarousel('slide',{where:'right'});
                });

                //Move the last item before the first one, so if they click back it goes
                //to the last one
                $this.find('.gallery_row:first').before($this.find('.gallery_row:last'));

                //set the left
                $this.find('.gallery_inner').css({
                    'left':'-' + row_width + 'px'
                });

                var slideMe = function () {
                    $this.gjSimpleCarousel('slide',{where:'right'});};

                //check if auto sliding is enabled
                if (data.auto_slide) {
                    //set the interval to call function to slide with option "right"
                    var timer = setInterval(slideMe, data.auto_slide_seconds * 1000);

                    if (data.hover_pause) {
                        $this.hover(function () {
                            clearInterval(timer);
                        }, function () {
                            //re-start timer
                            timer = setInterval(slideMe,data.auto_slide_seconds * 1000);
                        });
                    }
                }

                if (data.key_slide) {
                    $this.addClass('gj_carousel_keySlide');
                }
            });
        },
        /**
         * Slide to left or right.
         * @param options Object, something like {where : 'left'}.  To slide to
         *   the right, specify {where:'right'}.
         * @returns Chained jQuery()
         */
        slide : function (options) {
            var where = options.where || 'left';
            return this.each(function () {
                var $this = jQuery(this),
                    data = $this.data('gjSimpleCarousel');

                if (!data) {
                    //must not be initialized...
                    $this.gjSimpleCarousel();
                    data = $this.data('gjSimpleCarousel');
                    if (!data) {
                        //probably not valid for some reason
                        return;
                    }
                }
                var item_width = $this.find('.gallery_row').outerWidth();
                var left_indent = parseInt($this.find('.gallery_inner').css('left'));

                //alert('left indent:'+left_indent+' item width: '+item_width);

                if (where == 'left') {
                    left_indent = left_indent + item_width;
                } else {
                    left_indent = left_indent - item_width;
                }

                $this.find('.gallery_inner:not(:animated)').animate({'left' : left_indent}, 500, function () {
                    //when the animation finishes move next item around to give
                    //illusion of infinit stuff
                    if (where == 'left') {
                        $this.find('.gallery_row:first').before($this.find('.gallery_row:last'));
                        if ($this.find('.gallery_carousel_dot_active').prev().is('img')) {
                            //it is first, so move it to last
                            $this.find('.rightScroll').before($this.find('.gallery_carousel_dot_active'));
                        } else {
                            $this.find('.gallery_carousel_dot_active').prev().before($this.find('.gallery_carousel_dot_active'));
                        }
                    } else {
                        $this.find('.gallery_row:last').after($this.find('.gallery_row:first'));
                        if ($this.find('.gallery_carousel_dot_active').next().is('img')) {
                            //it is last, so move it to first
                            $this.find('.leftScroll').after($this.find('.gallery_carousel_dot_active'));
                        } else {
                            $this.find('.gallery_carousel_dot_active').next().after($this.find('.gallery_carousel_dot_active'));
                        }
                    }
                    $this.find('.gallery_inner').css({'left':'-' + item_width + 'px'});
                });
            });
        }
    };

    jQuery.fn.gjSimpleCarousel = function (method) {
        //Method calling logic
        if (methods[method]) {
            return methods[method].apply(this,Array.prototype.slice.call(arguments,1));
        } else if (typeof method === 'object' || ! method) {
            return methods.init.apply(this,arguments);
        } else {
            jQuery.error('Method ' + method + ' does not exist on jQuery.gjSimpleCarousel');
        }
    };
}(jQuery));