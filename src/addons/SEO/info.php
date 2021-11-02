<?php

//addons/SEO/info.php

# SEO Addon (Search Engine Optimization)

class addon_SEO_info
{
    //The following are required variables
    var $name = 'SEO';
    var $version = '3.2.1';
    var $core_version_minimum = '17.01.0';
    var $title = 'SEO';
    var $author = "Geodesic Solutions LLC.";
    var $icon_image = 'menu_seo.gif';
    var $description = '
		To use this addon for the first time, be sure to <strong style="color: red;">Read all instructions for this addon
		in the user manual</strong>.
		<br /><br />
		Use search engine friendly URLs.  This add-on "re-writes" all applicable
		urls so that they look like static pages.  For instance, the link to a category normally looks like:
		<strong>index.php?a=5&b=11</strong>  This addon will change the URL to be
		<strong>category/11/Automobiles.html</strong>
		<br /><br />
		<strong style="color: red;">Failure to follow the instructions carefully can result
		in broken (non-working) links, broken images, etc.</strong>  Note that this addon requires the
		ability to use MOD_REWRITE, and to be able to use a .htaccess file.  More info can be found
		in the user manual.';
    var $auth_tag = 'geo_addons';
    var $core_events = array (
    'filter_display_page',
    'rewrite_single_url',
    );
}
/**
 * SEO Changelog
 *
 * v3.2.1 - Geo 17.09.0
 *  - rewrite_single_url hook can now properly handle https addresses
 *
 * v3.2.0 - Geo 17.01.0
 *  - Implemented new admin design
 *
 * v3.1.0 - Geo 7.6.0
 *  - Allow specifying text for category names and parent category names to be used in URLs, per-category.
 *
 * v3.0.5 - Geo 7.4.0
 *  - Bug 1014 : make listing links use full sub-domain if setting in geo navigation
 *    is set to force using full subdomain for listings
 *  - Don't auto-insert the full URL in front of simple hash URL's
 *  - Fixed HTML being mangled for some links containing multibyte characters
 *  - Fixed single URLs being rewritten when addon is enabled but not in use
 *
 * v3.0.4 - Geo 7.3.0
 *  - Fixed minor issue where it does weird things
 *  - Allow one-off rewrites to specify that they should never use https addresses, even if generated from an https page (typically for use in creating emails)
 *
 * v3.0.3 - Geo 7.2.5
 *  - Add option to URL encode the title in URL's that are generated, for use
 *    with RSS feed.
 *
 * v3.0.2 - Geo 7.2.0
 *  - new core event to allow rewriting single urls
 *
 * v3.0.1 - Geo 7.0.2
 *  - Added an internal switch to allow skipping conflict checks
 *
 * v3.0.0 - Geo 6.0.3
 *  - Add the QSA option to generated HTACCESS contents, so re-written urls can have
 *    query parameters and still work properly
 *  - We want to do a "quick release" as the above actually fixes the FB login on
 *    re-written URL's..  But in future version this will allow us to have many more
 *    re-written URL's, ones that previously were not re-written because of extra parameters.
 *  - Fix regression in 2.4.5 where it messed up url's in RSS feed, changed to hopefully
 *    prevent future regressions.
 *
 * v2.4.5 - Geo 6.0.2
 *  - Change to preserve the absolute URL if the full URL is used, to prevent breaking
 *    absolute URL's in e-mails or similar places that need to be absolute.
 *
 * v2.4.4 - Geo 6.0.1
 *  - Fixed issue with auto-enabling via AJAX for trial demos and possibly other locations
 *
 * v2.4.3 - Geo 6.0.0
 *  - changes for Smarty 3.0
 *  - Fixed editing "replace & with" and being surrounded by - or spaces
 *  - Fixed display of htaccess contents during "wizard" in IE8.
 *  - Base tag now added by main system, not in display_page filter core event
 *  - Fixed when viewing RSS feed, and domain name different than what set in settings
 *  - Fixed issue with having something looking like a variable in title
 *
 * v2.4.2 - Geo 5.2.0
 *  - Changed wording on "is this link working" step of the wizard to avoid confusion
 *    about the linked page displaying without formatting
 *
 * v2.4.1 - Geo 5.1.2
 *  - Fixed installation to not mess up if accessing admin from different URL than set in URL settings
 *  - Made htaccess generate happen automatically to prevent weirdness in re-written url.
 *  - Made the force re-write URL preserve current sub-domain.
 *
 * v2.4.0 - Geo 5.1.0
 *  - Made title cleaning bulk of work done in geoFilter::cleanUrlTitle()
 *  - Added SEO URL's for browsing tags
 *
 * v2.3.1 - Geo 5.0.3
 *  - Updated to hopefully preserve sub-domain in re-written URL's if any exists
 *  - Now requires at least 5.0.3 since new sub-domain stuff relies on 5.0.3
 *    changes.
 *  - Fixed issue with % in title
 *  - Changes for updated license system
 *
 * v2.3.0 - Geo 5.0.1
 *  - Updated SEO to hopefully work with Arabic language and other non-Latin alphabets.
 *
 * v2.2.1 - Geo 5.0.0
 *  - Made SEO work on links with a newline directly after the end quote after the URL.
 *
 * v2.2.0 - Geo 4.1.4
 *  - Added new breadcrumb design to first-run wizard
 *
 * v2.1.9 - Geo 4.1.0
 *  - Fixed cleanish to work with - instead of _ so that you can set the & replacement
 *    with -and- properly.
 *
 * v2.1.8 - Geo 4.0.8
 *  - Fixed conflict detection to work again (it broke after recent changes to
 *    use - instead of _)
 *
 * v2.1.7 - Geo 4.0.7
 *  - Fix applied for addon license checks
 *
 * v2.1.6 - Geo 4.0.6
 *  - Added license checks.
 *
 * v2.1.5 - Geo 4.0.6
 *  - Made it possible for other addons to "talk" to this one, to allow other
 *    addons to add their own SEF URL's.  Case in point: the Storefront addon
 *    uses it.
 *  - Fixed problem where if installed in a sub-directory, and the sub-direcotry
 *    appeared in a re-written URL (for intance, in a listing title), it would
 *    cause infinite re-directs.
 *
 * v2.1.4 - Geo 4.0.3
 *  - When re-writing URL's, if it cannot get the "title" data, it will not
 *    re-write the URL.
 *
 * v2.1.3 - Geo 4.0.3
 *  - When re-writing URL's, change it so that if the page is specified but is set to page 1, it
 *    ignores the page #, to remove different URL's with same content.  For example:
 *    ?a=5&b=100
 *    ?a=5&b=100&page=1
 *    In the second case, the page # is ignored when re-writing now since it is page #1.
 *
 * v2.1.2 - Geo 4.0.2
 *  - Made SEO addon work with new RSS listing feed to convert links between <link></link>
 *
 * v2.1.1 - Geo 4.0.0
 *  - Fixed SEO adding slashes to quotes in javascript links
 *  - Fixed ajax to save settings properly
 *
 * v2.1.0 - Geo 4.0.0RC11
 *  - Fixed a bug that caused links included in listing descriptions to break if SEO was used
 *  - Upped "feature release" number from 2.0 to 2.1 - for following reason:
 *  - In titles, it now uses - instead of _ for word seperator, as google does not consider
 *    _ to be a word seperator.  Will require .htaccess to be re-generated for people updating.
 *  - Changed the "force SEO urls" setting to do a 301 redirect for ANY url that is not correct,
 *    including if it uses _ instead of - or if a title of a category or listing has changed.
 *
 * v2.0.3 - Geo 4.0.0RC9
 *  - First version using changelog block for SEO addon
 *  - Fixed bug where on/off setting didn't work on sites that updated from 1.0
 *
 */
