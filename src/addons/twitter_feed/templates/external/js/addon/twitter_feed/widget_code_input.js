checkTwitterFeedInput = function () {
    jQuery('#twitter_feed_input').keyup(function () {
        if (jQuery('#twitter_feed_input').val().length == 0) {
            //nothing to do here
            return;
        }
        //lock out input field and show "loading" message
        jQuery('#twitter_feed_input').prop('disabled',true);
        jQuery('#twitter_feed_parse_error').hide();
        jQuery('#twitter_feed_loading_message').show();

        //send data from form to processor via ajax
        jQuery.post(
            "AJAX.php?controller=addon_twitter_feed&action=processWidgetCode",
            { code: jQuery('#twitter_feed_input').val() },
            function (data) {
                processTwitterFeedCode(data); },
            'json'
        );


        //un-do form lockout (here instead of in processTwitterFeedCode, in case ajax fails)
        jQuery('#twitter_feed_loading_message').hide();
        jQuery('#twitter_feed_input').prop('disabled',false);
    });
};

processTwitterFeedCode = function (data) {
    if (data.status == 'error') {
        //data missing or in unexpected format
        //console.log('ERROR: '+data.errNum);
        //stuff to recover from error
        jQuery('#twitter_feed_input').val('');
        jQuery('#twitter_feed_href').val('');
        jQuery('#twitter_feed_data_id').val('');
        jQuery('#twitter_feed_parse_error').show();
    } else if (data.status == 'ok') {
        //populate hidden fields with data.href and data.data_id

        //clear input and replace with user-friendly display
        jQuery('#twitter_feed_input').val('');
        jQuery('#twitter_feed_input_container').hide();
        jQuery('#twitter_feed_results_container').show();

        jQuery('#twitter_feed_href').val(data.href);
        jQuery('#twitter_feed_data_id').val(data.data_id);
    }
};

twitterFeedReleaseCode = function () {
    jQuery('#twitter_feed_href').val('');
    jQuery('#twitter_feed_data_id').val('');
    jQuery('#twitter_feed_input').val('');
    jQuery('#twitter_feed_results_container').hide();
    jQuery('#twitter_feed_input_container').show();
};

//listen to the input box for keyups
jQuery(document).ready(function () {
    checkTwitterFeedInput();
    if (typeof geoListing == 'object' && geoListing !== null) {
        //this will only execute when using the "combined" listing process
        //otherwise, geoListing is not a thing and throws JS errors
        geoListing.onComplete(checkTwitterFeedInput);
    }
});