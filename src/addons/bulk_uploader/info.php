<?php

class addon_bulk_uploader_info
{
    public $name = 'bulk_uploader';
    public $version = '3.6.1';
    public $core_version_minimum = '17.01.0';
    public $title = 'Bulk Uploader';
    public $author = "Geodesic Solutions LLC.";

    public $description = '
	This allows the admin to upload multiple listings simultaneously from a single .csv source file.<br /><br />
	It also allows revolving inventory CSV files.';

    public $icon_image = 'menu_bulk_uploader.gif';

    public $auth_tag = 'geo_addons';
    public $upgrade_url = 'http://geodesicsolutions.com/component/content/article/52-importing-exporting/60-bulk-uploader.html?directory=64';
    public $author_url = 'http://geodesicsolutions.com';
    public $info_url = 'http://geodesicsolutions.com/component/content/article/52-importing-exporting/60-bulk-uploader.html?directory=64';
}

/*
 * CHANGELOG - Bulk Uploader
 *
 * v3.6.1 - Geo 17.12.0
 *  - Added switch to make Revolving Inventory update only a single file with each pass
 *
 * v3.6.0 - REQUIRES 17.01.0
 *  - Implemented new admin design
 *
 * v3.5.6 - Geo 16.01.0
 *  - Improved reporting for folders with bad permissions
 *
 * v3.5.5 - GeoCore 7.6.4
 *  - Report on first page if tokens/uploads folders fail is_writable()
 *  - Report on page 3 if title not selected on page 2
 *
 * v3.5.4 - GeoCore 7.6.2
 *  - Handle Mac line endings (\r) during Revolving Inventory refreshes
 *
 * v3.5.3 - GeoCore 7.6.0
 *  - Allow removal of in-progress multi-part upload sets
 *
 * v3.5.2 - GeoCore 7.5.2
 *  - Allow creating "unlimited duration" listings via bulk upload
 *
 * v3.5.1 - GeoCore 7.5.0
 *  - Allow using extant (but not creating new) image tokens when using "Faster" image processing
 *
 * v3.5.0 - GeoCore 7.4.3 (REQUIRES GeoCore 7.4.2)
 *  - Added image tokenization
 *
 * v3.4.3 - GeoCore 7.4.2
 *  - Added language_id to the list of fields that can be populated via bulk upload
 *
 * v3.4.2 - GeoCore 7.4.1
 *  - Fixed a regression that would cause deleting an upload log to not actually remove associated listings.
 *  - Corrected certain fields ignoring their "default" values if set
 *
 * v3.4.1 - REQUIRES GeoCore 7.4beta2
 *  - Refactored Revolving Inventory registry storage to support large numbers of revolving sets
 *  - Revolving Inventory uploads will no longer create a separate "upload log" for every time they run
 *  - Added ability to bulk upload the new Cost Options fields
 *  - Added protections to prevent listing duplication if revolving inventory dies mid-upload
 *  - The upload process will now attempt to remove server memory/time limits on the fly
 *  - Added reporting of total listings added/skipped to final results page
 *
 * v3.4.0 - REQUIRES GeoCore 7.4beta1
 *  - Added Multi-Part Uploads feature
 *  - Compatible with new category structure
 *
 * v3.3.2 - GeoCore 7.3.0
 *  - Fixed Youtube videos not appearing on bulk uploaded listings
 *  - Fixed "quantity remaining" not being set
 *  - Allow "price applies to item"
 *
 *  v3.3.1 - GeoCore 7.2.2
 *   - Fixed a regression in Region setter
 *
 *  v3.3.0 - GeoCore 7.2.0
 *   - Add support for additional regions and multi-level fields
 *
 *  v3.2.3 - GeoCore 7.2beta4
 *   - Fix "skip first row" option not working
 *
 *  v3.2.2 - GeoCore 7.1.0
 *   - Fix listing Regions not being set properly by "use default user data" switch
 *   - Allow listings to show "no photos" icon if all their given image URLs are bad
 *
 *  v3.2.1 - REQUIRES GeoCore 7.0.3
 *   - switch setting used for image thumbnail sizes to be consistent with the way the main image uploader does it
 *
 *  v3.2.0 - GeoCore 7.0.3 (this should have "required" 7.0.3 but didn't)
 *   - Swap Country and Zip order in created Mapping Locations (to match similar change in core)
 *   - Allow adding image captions
 *   - Change name of sessionHandler class to geoBulkUploaderSessionHandler, to fix conflict with PHP5.4's built-in SessionHandler
 *   - Optimizations for uploading remote images via the Faster method
 *
 *  v3.1.8 - GeoCore 7.0.1
 *   - Allow Regions to be set by "unique name"
 *   - Allow Regions to be set by "abbreviation" (at the State level only)
 *   - Allow uploading classifieds with the Sold flag already set
 *   - Make the Delete process delete Region data.
 *
 *  v3.1.7 - GeoCore 7.0.0
 *   - Fixed the license check
 *
 *  v3.1.6 - Geo 7.0.0
 *  - Changes for 7.0 license compatibility
 *  - Updated to use new Region functionality
 *
 *  v3.1.5 - Geo 6.0.5
 *  - Added more protection to prevent corrupted uploads
 *  - Clarified text for and corrected functionality of revolving "adjust times" switch
 *
 *  v3.1.4 - Geo 6.0.4
 *  - Removed an extraneous strtoupper() on the state fields
 *  - Fixed GeoNav integration entering an infinite loop when using top-level regions that don't correspond to Geographic Setup
 *  - Fixed assigning GeoNav regions as one level too low
 *  - Fixed listing tags not resetting between listings
 *  - Auto-populate admin label from filename of source csv
 *  - Save and persist duration settings between uploads
 *  - Restore ability to specify seller by username as well as ID
 *  - Fixed a bug that could prevent listing tags from saving properly
 *  - Added an option to automatically adjust end times on revolving reload when using exact dates
 *
 *  v3.1.3 - Geo 6.0.2
 *  - New "Images Updated" option for revolving uploads doesn't appear on first pageload of step 2
 *  - Fixed a bug that caused expired listings to not remove themselves from the upload log
 *  - Fixed a bug that would make the Revolving Inventory fail to properly update listings with non-numeric unique IDs
 *  - Fixed a bug where images attached to listings removed by Revolving Inventory were not removed from db or filesystem
 *  - Hide rows with 0 listings (update-only revolving runs) from delete log
 *  - Fixed youtube videos allowing one more upload than they should have
 *
 *  v3.1.2 - Geo 6.0.0
 *  - Added ability to specify Listing Tags
 *  - Fixed listing duration not resetting on revolving inventory refreshes
 *  - Changes for Smarty 3.0
 *  - Fixed a bug that could cause an entire upload to fail if the seller/buyer db column wasn't created
 *  - Fixed blank Default Field boxes multiplying themselves on step 3
 *  - Added a switch to allow bypassing image resets for revolving inventory updates, on a per-listing basis. If not specifically included, will function as before and always process images.
 *  - Fixed Category Specific Questions not populating auto-title
 *  - Delete log improved. Incomplete uploads can now be removed with a single click!
 *  - Added a button to clear the Session table
 *  - Allow uploading Youtube videos
 *  - Create new section for Addon fields, move Storefront Category to it
 *  - Add ability to interface with two more addons: can now set a Geographic Navigation location and a Twitter Feed username in the bulk upload data
 *  - Add ability to specify Categories by name as well as ID number
 *  - Add business type to data added by Use Default User Data checkbox
 *
 * v3.0.0 - Geo 5.2.0
 *  - Enough new features lately to merit a new major version. Today's Bulk Uploader is really light years apart from 2.0
 *    - Pre-releases of this since Geo 5.1.4 may have versions 2.8.0 or 2.9.x
 *  - Auto-populate log's user label field if doing a revolving upload (since it already has a label of sorts)
 *  - Added "Checkbox List" option to category specific fields -- specifying a (comma-separated) list of the numerical IDs of checkboxes on a listing will turn those boxes on
 *  - Rewrote image handling. It now:
 *      - Uses common functions from the geoImage class
 *      - Accepts either URLs or local paths for each image
 *      - Resizes images
 *      - Creates thumbnails
 *      - Saves the images and thumbnails locally (even if a URL image is specified)
 *      - Can optionally bypass the new resize routines and use the old way of doing things, for sites where speed is a concern
 *  - Steps 2 and 3 of the upload process will now remember and automatically populate data from the last bulk upload session
 *  - Removed erroneous 'field type' select boxes in Step 3
 *  - Fixed enumeration of fields in Step 3
 *  - Rewrote internal Javascript to use Prototype
 *  - checkUserLimits switch now plays nicer with revolving uploads (space under the limit left by revolving deletions will now be filled if possible)
 *  - Fixed a bug that caused Revolving Inventory uploads to not save correctly
 *  - Removed some old, unused files that used to be erroneously included with the addon
 *
 * v2.7.0 -- Geo 5.1.4
 *  - reworked revolving uploader filename patterns so that each source file now has its own folder inside uploads/
 *  - added 'check user limits' switch that restricts bulk uploading according to the price plan limitations of each seller
 *  - added 'use default user data' switch that populates listings' default contact data based on seller's registration data
 *  - restored functionality to the 'Images List' option
 *  - revolving uploader can now handle listings that expire during the period between cron runs (by creating a new listing for them)
 *
 * v2.6.1
 *  - Added check to watch for "junk" rows left in a source file by Excel
 *  - Added to warning about possibly setting zip compression method wrong
 *  - Added check to make it not set default precurrency if price not set, so that postcurrency-only labeling can be used
 *
 * v2.6.0 -- Geo 5.1.3
 *  - Added ability to set a Storefront Category's ID number as a column during upload
 *
 * v2.5.6 -- Geo 5.1.2
 *  - Removing a revolving inventory session will now properly delete the associated CSV file, allowing the label to be re-used
 *  - Allow re-using revolving inventory labels for upload sessions that were not completed
 *  - Fixed issue when NOT using multiple categories that caused category to not be set.
 *  - Changed revolving back-end to map what each "unique value" field maps to what listing
 *    ID so that upon updating ore removing, it doesn't affect the wrong one.
 *  - Optimized parts to run more efficiently, it should run a little faster now
 *    on super huge imports, in theory...
 *  - Fixed it to allow submitting form on manage uploads if there aren't any uploads listed currently
 *  - try to chmod(777) recurring upload source files after they're created, to make sure FTP users can modify them
 *  - fixed viewed/forwarded/responded stats resetting to 0 when a recurring inventory upload updates.
 *  - fixed a bug where malformed user input could cause the revolving uploader to delete more listings than it otherwise should
 *
 * v2.5.3 - v2.5.5
 *  - internal version numbers, changes included in 2.6.0
 *
 * v2.5.2 -- Geo 5.1.1
 *  - added a trim() to state fields, since the preloaded state data in some versions of the base software has trailing spaces
 *
 * v2.5.1 -- Geo 5.1.0
 *  - Version bump, due to db structure changes after pre-releasing 2.5.0
 *  - Moved delete log to Manage Uploads page
 *  - Added ability to set admin-readable labels on entries in the log table
 *  - Added ability for revolving inventory to update/delete existing listings
 *
 * v2.5.0 -- Geo 5.0.4
 *  - Added Revolving Inventory
 *
 * v2.4.0 -- Geo 5.0.3
 *  - Added capability to bulk upload to multiple categories at a time
 *
 * v2.3.0 -- Geo 5.0.2
 *  - made page layout on step 3 more consistent with the rest of the admin
 *  - restored functionality of "default" field settings (in step 3)
 *
 * v2.2.0 -- Geo 5.0.0
 *  - various quality-of-life improvements, to make options easier to understand
 *  - a couple of visual tweaks to make the bulk uploader better match the rest of the admin
 *  - now handles images with spaces in the filename
 *
 * v2.1.8 -- Geo 4.1.3
 *  - fix die()ing on malformed image url references
 *  - fixed 'skip first row' setting also causing images attached to the first real listing to be skipped
 *
 * v2.1.7 -- Geo 4.1.2
 *  - fixed a bug that would cause site errors when bidding on bulk uploaded auctions.
 *  - added ability to upload auctions with seller/buyer turned on
 *  - added a check to make sure listings' priceplan id is always set
 *  - make sure delete logs don't get deleted unless their ads have really expired
 *
 * v2.1.6 -- Geo 4.1.0
 *  - fixed pre/post currency defaulting to $/USD instead of site settings, if values not present in upload data
 *
 * v2.1.5 -- Geo 4.0.9
 *  - sessions should now properly clear when the bulk upload completes,
 *    so that returning to the bulk uploader later should show page 1 instead of page 4
 *  - fixed a bug that prevented the bulk uploader addon from being uninstalled
 *  - stop progress bar from showing 5/4 when starting a new upload
 *  - fixed a bug that prevented the bulk uploader from working on some Windows servers
 *  - fixed "step 4" displaying as a blank screen in IE8
 *
 * v2.1.4 -- Geo 4.0.8
 *  - fixed a bug that caused some bulk uploads to significantly impair the site search functionality
 *
 * v2.1.3 -- Geo 4.0.6
 *  - fixed SQL crash error that prevented all bulk uploading
 *  - fixed another SQL crash related to blank values for category-specific fields
 *  - fixed a bug that could cause the bulk uploader to save corrupted session data (causing a SQL crash later)
 *  - category-specific checkboxes now save properly.
 * 		CSV values:
 * 		  any non-zero number (such as 1) or the text strings "true," "yes," and "on" will cause a checkbox to be "set"
 * 		  any other text string, or anything PHP evaluates to boolean false (such as 0) will cause a checkbox to not be set
 *  - category-specific values are now front-side-searchable in bulk-uploaded listings
 *  - toDB'd info posted to db, so that it's inline with the way the rest of the software does it and doesn't break the search functions
 *
 * v2.1.2 -- Geo 4.0.4
 *  - fixed title generation (again)
 *
 * v2.1.1 -- Geo 4.0.1
 * - Removed old documentation
 *
 * v2.1.0 -- Geo 4.0.0RC11
 * -NEW: category specific fields (but maybe not checkboxes ?) can now be uploaded
 * -fixed a bug that could prevent logging into the admin if admin logged out in the middle
 * 		of a bulk upload session
 * -added "location_address" to the list of selectable fields when uploading
 * - fixed some bugs in the way the uploader determined Geo product version and which fields to show
 *
 *
 * v2.0.3 -- Geo 4.0.0RC10
 * -fixed category counts not updating after upload
 *
 * v2.0.2 -- Geo 4.0.0RC9
 * -fixed a bug in auto-title-generation
 *
 * v2.0.1 -- Geo 4.0.0RC8
 * -changelog creation
 */
//leave whitespace at the end of this, or Eclipse dies
