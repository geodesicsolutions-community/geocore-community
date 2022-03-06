// 7.5.3-30-gd909a22

/*
NOTE:  For tab ID's, can be whatever you want.  The "content divs" must use same
exact ID, but with "Content" added to end, as demonstrated in sample below.

Note 2:  If needed, this will work with multiple sets of tabbed contents, the
ul just needs to be right above the content divs for that set of tabs.

Using tabs example:

<ul class="tabList">
    <li id="firstTab">Tab 1</li>
    <li id="secondTab">Tab 2</li>
    <li id="funny" class="activeTab">Funny Tab</li>
</ul>

<div class="tabContents" id="firstTabContents">
    Tab 1 contents.
</div>

<div class="tabContents" id="secondTabContents">
    Tab 2 contents!
</div>

<div class="tabContents" id="funnyContents">
    Funny Tab!  Insert funny joke here:  ____
</div>


------
Possible AJAX loading alternate:  If need a loading image, have something like this (alternate
 to the way the divs are above, this one is stripped down for brevity):
<div class="tabContents">
    <div id="loadingImg"><img ...></div>
    <div id="firstTabContents">...</div>
    ...
</div>

Then using a callback (see below), would show the loading div and make an ajax
call to populate that tab.
*/

/*
 * Initializing:  Normally this is initialized in gjUtil.ready but can be initialized
 * by hand.  When initializing, there is one valid option, localStoragePersist.
 * Note that can force a group of tabs to "not persist" by adding the class ignoreActiveStored
 * to the UL element.  Or alternatively can pass in value of false for localStoragePersist, like this:
 *
 * jQuery('ul.tab_class_name').gjTabs({localStoragePersist : false});
 *
 * Note that having the class name ignoreActiveStored will overwrite any options
 * passed in during initialization.  Also note that it will always be false on
 * older browsers that do not support local storage.
 *
 */

/*

Callbacks:  It is possible to add a callback, by using:
jQuery('#tabId').gjTabs('onActive',function () {});

 the tabId would be the id if the tab, and the second parameter passed into gjTabs
 should be the function.  Note that this is the only instance where calling gjTabls
 for an individual tab will work, initialization requires calling it for the parent
 <ul> element containing the tab li items.

 It must also be done AFTER the tabs are initialized, or it will not work.

Callback example: (this snippet would be done inside JS script run when window
 is done loading):

//funny is ID for the "Funny Tab" in the tab example.
jQuery('#funny').gjTabs('onActive', function () {
    alert ('Funny tab clicked!');
    //Note:  Might show "loading" image here, and possibly make ajax call that
    //would populate the tab's contents.  Text search (in admin) uses this.
});

jQuery('#tabIdOrSelector').gjTabs('canUseLocalStorage',function () {return true});

 Callback should return bool value, true to allow using local storage, false to prevent it.

 It is called at time that the local storage would be used.  This can be useful to prevent using local storage to
 comply with GDPR or other compliance restrictions.

Callback example: (this snippet would be done inside JS script run when window is done loading, if your site is using
    the popular "cookiebot" 3rd party service):

// Do not save in local storage unless cookiebot (3rd party vendor) is loaded and user has consented to preferences
jQuery('ul.tabList').gjTabs('canUseLocalStorage', function () {
    return Cookiebot && Cookiebot.consent.preferences;
});

 */

(function (jQuery) {
    var internal = {};

    internal.tabCallbacks = {};

    internal.tabClick = function (action) {
        var $this = jQuery(this);

        var data = $this.closest('ul').data('gjTabs');
        if (!data) {
            //not initialized!
            return;
        }
        internal.activateTab($this.attr('id'), data, action);
    };
    internal.activateTab = function (tab, data, action) {
        tab = jQuery('#' + tab);
        if (!internal.precheck(tab)) {
            //pre-checks failed, do not proceed.
            //NOTE:  can over-write the precheck if needed, normally it just checks
            //to make sure that tab is not already the current active tab.
            return;
        }
        var tabId = tab.attr('id');
        var storageName = 'activeTab';
        tab.closest('ul').find('li').each(function () {
            var elem = jQuery(this);
            var elemId = elem.attr('id');
            storageName += '__' + elemId;
            elem.removeClass('activeTab');
            if (!jQuery('#' + elemId + 'Contents').length && (gjUtil.inAdmin)) {
                alert('Tabs set up incorrectly!  There is no element with ID ' + elemId + 'Contents');
            }
            jQuery('#' + elemId + 'Contents').hide();
        });

        tab.addClass('activeTab');
        jQuery('#' + tabId + 'Contents').show();

        if (typeof internal.tabCallbacks[tabId] === 'function') {
            internal.tabCallbacks[tabId](action);
        }

        if (data.localStoragePersist && internal.canUseLocalStorage()) {
            localStorage.setItem(storageName, tabId);
        }

        //re-do the gallery height calculations for any that were initially hidden (no effect if no galleries present)
        gjUtil.initGallery();
    };
    internal.precheck = function (elem) {
        if (elem.hasClass('activeTab')) {
            //has active tab, don't allow to proceed
            return false;
        }
        return true;
    };

    /**
     * Additional check run at time of using storage for whether should use local storage, this is in addition to normal
     * browser and config checks.  Useful to prevent storage for GDPR consent reasons.
     */
    internal.canUseLocalStorage = function () {
        return true;
    };

    var methods = {
        init : function (options) {
            return this.each(function () {
                var $this = jQuery(this),
                    data = $this.data('gjTabs');

                if (!data) {
                    var defaultPersist = !(typeof(Storage) === 'undefined' || $this.hasClass('ignoreActiveCookie') || $this.hasClass('ignoreActiveStored'));

                    $this.data('gjTabs',$this.extend({
                        localStoragePersist : defaultPersist
                    }, options));
                    data = $this.data('gjTabs');
                }

                //do init stuff here

                var storageName = 'activeTab';
                var activeTab = null;
                $this.find('li').each(function () {
                    var elem = jQuery(this);
                    elem.click(internal.tabClick);
                    if (!elem.hasClass('activeTab')) {
                        //hide the contents
                        var content = jQuery('#' + elem.attr('id') + 'Contents');
                        if (!content.length) {
                            alert('Page did not finish loading, or tabs may be set up incorrectly!  There is no content div with ID of ' + elem.identify() + 'Contents');
                            return;
                        }
                        content.hide();
                    } else {
                        //has activeTab class, must be "default" active tab
                        activeTab = elem.attr('id');
                        //remove class name so it can "activate" the tab without failing pre-checks
                        elem.removeClass('activeTab');
                    }
                    storageName += '__' + elem.attr('id');
                });
                if (data.localStoragePersist) {
                    var activeStored = localStorage.getItem(storageName);
                    if (activeStored && jQuery('#' + activeStored).length) {
                        activeTab = activeStored;
                    }
                }

                if (activeTab && jQuery('#' + activeTab).length) {
                    internal.activateTab(activeTab, data);
                }
            });
        },

        onActive : function (callback) {
            if (typeof callback !== 'function') {
                //invalid callback!
                return this;
            }

            return this.each(function () {
                internal.tabCallbacks[jQuery(this).attr('id')] = callback;
            });
        },

        /**
         * This is a way to overwrite the internal precheck, use with caution!
         */
        precheck : function (callback) {
            if (typeof callback === 'undefined') {
                //return the current callback
                return internal.precheck;
            }
            if (typeof callback !== 'function') {
                //not a function
                return;
            }
            internal.precheck = callback;
            return this;
        },

        /**
         * Callback to determine if local storage should be used to save values, in addition to internal checks and
         * properties
         */
        canUseLocalStorage : function (callback) {
            if (typeof callback === 'undefined') {
                //return the current callback
                return internal.precheck;
            }
            if (typeof callback !== 'function') {
                //not a function
                return;
            }
            internal.canUseLocalStorage = callback;
            return this;
        }
    };

    jQuery.fn.gjTabs = function (method) {
        //Method calling logic
        if (methods[method]) {
            return methods[method].apply(this,Array.prototype.slice.call(arguments,1));
        } else if (typeof method === 'object' || ! method) {
            return methods.init.apply(this,arguments);
        } else {
            jQuery.error('Method ' + method + ' does not exist on jQuery.gjTabs');
        }
    };
}(jQuery));
