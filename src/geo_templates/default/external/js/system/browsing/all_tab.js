// 7.4beta1-74-gfceff14

/*
 * This file sets up the fancy stuff for the "all" tab when browsing, so that
 * we don't have to duplicate exact same stuff, instead use custom stuff
 * so that all tab just shows both sections.
 *
 */

jQuery(function () {
    jQuery('#allTab').gjTabs('onActive', function () {
        jQuery('#classifiedsTabContents').show();
        jQuery('#auctionsTabContents').show();
    });


    if (jQuery('#allTab').hasClass('activeTab')) {
        //it is currently active...  Just in case tab init has already happened
        jQuery('#classifiedsTabContents').show();
        jQuery('#auctionsTabContents').show();
    }
});
