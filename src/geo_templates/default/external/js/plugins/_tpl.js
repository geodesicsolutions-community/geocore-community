// 7.5.3-36-gea36ae7

/**
 * This is a template for a new plugin...  Instructions:
 * 1.  Find gjTpl replace with the plugin name...  For instance, if file is named
 *     somePlugin.js you would find gjTpl replace with gjSomePlugin
 * 2.  Implement the init function.  Be sure to set any default params in the
 *     appropriate place, and don't start your own code until "//do init stuff here".
 *     - For parameters, use data.paramName to reference it.  Allows alternate
 *       parameters to be used.
 * 3.  Create any additional "supporting" methods for the plugin.  Keep in mind,
 *     these additional plugins would be used like:
 *
 *     $this.gjTpl('anotherFunction',{where:'right'});
 *
 * If new to jQuery plugins, see the following to get aquanted:
 * http://docs.jquery.com/Plugins/Authoring
 */
(function (jQuery) {
    var methods = {
        init : function (options) {
            return this.each(function () {
                var $this = jQuery(this),
                    data = $this.data('gjTpl');

                if (!data) {
                    $this.data('gjTpl',$this.extend({
                        //default options here
                        parameter_name : 'parameter value'
                    }, options));
                    data = $this.data('gjTpl');
                }
                //do init stuff here
            });
        },

        chainedFunction : function (options) {
            //chained function : example of what to do if the return does not matter,
            //this maintains chainability by basically doing the same stuff to each
            //of the matched elements, and returning this.  see the documentation
            //(linked above) for more info about chainability

            //To allow function to be "chainable" (make it act on every element in selection),
            //do something like this:
            return this.each(function () {
                var $this = jQuery(this),
                data = $this.data('gjTpl'),
                //example: perhaps this takes in a parameter unique to this function, like {where : 'right'}...
                //this would set it up so that if not specified, it would use 'left' for the value.
                where = options.where || 'left';

                if (!data) {
                    //if data is not set yet, we know we need to initialize still...
                    //for times when the plugin requires initialization
                    $this.gjTpl();
                    data = $this.data('gjTpl');
                    //NOTE: Can kill this part if doesn't require initialization.
                }
            });
        },

        firstElementOnlyGetInformation : function (options) {
            //This is an example of a function that ONLY acts upon the "first element"
            //in the set of matched elements.  This should only be used when the
            //return value matters, such as when the function gets a value or something
            //similar.  See the docs linked at the top for more info on this.

            var data = this.data('gjTpl');
            if (!data) {
                //if data is not set yet, we know we need to initialize still...
                //for times when the plugin requires initialization
                this.gjTpl();
                data = this.data('gjTpl');
                //NOTE: Can kill this part if doesn't require initialization.
            }
            //Do stuff here...

            //remember what you read on the linked documentation, that
            //this is the equivelent of jquery('selector'), so don't go doing
            //jQuery(this) (unless you are in a sub-function, as noted in docs for
            //jquery)

            //in this example, it returns the number of elements
            //that are matched...
            return this.length;
        }
    };

    jQuery.fn.gjTpl = function (method) {
        //Method calling logic
        if (methods[method]) {
            return methods[method].apply(this,Array.prototype.slice.call(arguments,1));
        } else if (typeof method === 'object' || ! method) {
            return methods.init.apply(this,arguments);
        } else {
            jQuery.error('Method ' + method + ' does not exist on jQuery.gjTpl');
        }
    };
}(jQuery));