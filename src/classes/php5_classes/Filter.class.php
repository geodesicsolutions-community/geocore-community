<?php
//Filter.class.php
/**
 * Holds the geoFilter class.
 * 
 * @package System
 * @since Version 4.0.0
 */
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## 
##    18.02.0
## 
##################################

/**
 * A few utility methods, good for filtering stuff.
 * 
 * @package System
 * @since Version 4.0.0
 */
class geoFilter
{
	/**
	 * Auto-insert the <base href="..." /> tag when the text contains the <head>.
	 * 
	 * If it sees that the base tag already exists, or has already been added in
	 * this page load, it will NOT add another base tag.
	 * 
	 * @param string $full_text
	 * @param bool $preview If true, and if in admin panel, will remove admin
	 *   folder name from base href URL.  Param added in version 6.0.0
	 * @return string Text with base tag inserted.
	 */
	public static function baseHref($full_text, $preview=false)
	{
		$preview = ($preview && defined('IN_ADMIN'));
		$firstHead = stripos($full_text,'<head');
		$debugHead = strpos($full_text,'\n<head>\n');
		
		if ($firstHead===false) {
			//don't bother with any other checks if didn't even find <head in the text
			return $full_text;
		}
		
		//first check to make sure this isn't just the head from {debug}
		/**
		 * This is ugly hack, but it is the best way to accomplish this 
		 * while avoiding:
		 *  - Changes to debug.tpl which is part of smarty
		 *  - changes to base smarty library files
		 *  
		 *  TODO:  In future, need to use a smarty tag to generate <base> tag
		 *  based on current environment, so we don't have to use a filter to auto-add
		 *  it.
		 */
		if ($debugHead!==false && ($debugHead+2)==$firstHead) {
			//this is debug head only!  skip it.
			return $full_text;
		}
		
		/**
		 * Do a better check than just blindly looking for <head as that could match
		 * on <header> which is a new HTML5 tag.  Make sure <head is followed by
		 * white space or > character.
		 */
		if (!preg_match('/<head(>|[\s\n]{1})/',$full_text)) {
			//we may have found <head but not followed by white space or > so skip
			return $full_text;
		}
		
		
		if (stripos($full_text,'<base')=== false) {
			//only add if there is not already a <base> tag
			$base_href = self::getBaseHref();
			if ($preview) {
				$base_href = str_replace(ADMIN_LOCAL_DIR, '', $base_href);
			}
			$full_text = preg_replace("/(<head(>|[\s\n]{1}[^>]*>))/i","\$1<base href='".$base_href."' />",$full_text);
		}
		
		
		return $full_text;
	}
	/**
	 * Gets the base href URL that would be put in a base href tag.
	 * @return string
	 */
	public static function getBaseHref ()
	{
		if (!defined('GEO_BASE_HREF')){
			$db = DataAccess::getInstance();
			$indexfile = $db->get_site_setting('classifieds_file_name');
			$setting_name = 'classifieds_url';
			if (geoSession::isSSL() && strlen(trim($db->get_site_setting('classifieds_ssl_url')))) {
				$setting_name = 'classifieds_ssl_url';
			}
			$url =  str_replace($indexfile,'',$db->get_site_setting($setting_name));
			if ($url[strlen($url)-1] !== '/') $url .= '/'; //add ending slash if not there
			
			if ($_SERVER['HTTP_HOST']) {
				//get rid of beginning part and domain, replace it with actual domain used
				//so it will work for sub-domains as well.
				$url = preg_replace('|^https?://[^/]+|','',$url);
				
				$http = (geoSession::isSSL())? 'https://' : 'http://';
				
				$url = $http.geoPC::cleanHostName($_SERVER['HTTP_HOST'], true).$url;
			}
			
			define('GEO_BASE_HREF',$url);
		}
		return GEO_BASE_HREF;
	}
	
	/**
	 * Internal use.
	 * @internal
	 */
	private static $_htmlTags;
	/**
	 * Internal use.
	 * @internal
	 */
	private static $_keepTagsNotDefined, $_add_nofollow_user_links;
	
	/**
	 * Used to populate self::$_htmlTags and self::$_keepTagsNotDefined so that
	 * only have to get settings once in one page load.
	 * 
	 * 
	 */
	private static function initHtmlAllowed()
	{
		if (self::$_htmlTags) {
			//already initialized.
			return;
		}
		$db = DataAccess::getInstance();
		$tags = $db->GetAll("SELECT * FROM ".geoTables::html_allowed_table);
		foreach ($tags as $tag) {
			self::$_htmlTags[$tag['tag_name']] = $tag;
		}
		self::$_keepTagsNotDefined = $db->get_site_setting('keep_tags_not_defined');
		self::$_add_nofollow_user_links = $db->get_site_setting('add_nofollow_user_links');
	}
	
	/**
	 * Used by geoFilter::replaceDisallowedHtml() to replace each individual
	 * HTML tag found in the text.  Uses settings in DB.
	 * 
	 * @param string $fullTag The entire tag
	 * @param string $tag Just the first bit, like for <a href... this would be "a"
	 * @param bool $removeAll if true, will remove/replace the tag no matter what.
	 * @return string
	 */
	public static function replaceTag ($fullTag, $tag, $removeAll = 0)
	{
		self::initHtmlAllowed();
		
		$allowed = false;
		$replace = ' ';
		if ($removeAll) {
			//shortcut, just replace it without doing extra junk
			return $replace;
		}
		$tag = strtolower($tag);
		
		//prevent XSS from someone using a script handler (e.g. "onmouseover") on an otherwise-allowed HTML tag
		//with the advent of HTML5, the number of handlers in use has grown exponentially, and browsers are adding their own all the time
		//so use a regex to block on{anything}= and replace it with data-{something} so that the ending html is still valid (usually)
		$fullTag = preg_replace("/on(.)+?=/i", "data-sanitized-event=", $fullTag);
		
		if (isset(self::$_htmlTags[$tag])) {
			$tagInfo = self::$_htmlTags[$tag];
			if (!$tagInfo['tag_status']) {
				//tag_status = 0:  tag allowed
				$allowed = true;
			}
			if (strlen(trim($tagInfo['replace_with']))) {
				//special case!  This is not implemented in the admin (to avoid confusion between
				// this and "filter badwords" feature), but if replace_with is set in DB then
				//use that to replace the tag with instead of a space.
				$replace = $tagInfo['replace_with'];
			}
		} else if (self::$_keepTagsNotDefined) {
			//tag not defined, and we do not remove un-defined tags
			$allowed = true;
		}
		if ($allowed && !$removeAll) {
			//allowed to use this html tag, so return full tag un-modified.
			
			if ($tag === 'a' && self::$_add_nofollow_user_links && substr($fullTag, 0, 2)!=='</') {
				//ensure that rel="nofollow" is in the tag.  To prevent any attempts
				//to bypass it by having it in the title or similar, force it to be at the end
				if (substr($fullTag, -16)!==' rel="nofollow">') {
					
					$fullTag = preg_replace('/>$/', ' rel="nofollow">', $fullTag);
				}
			}
			
			return $fullTag;
		}
		//not allowed, so return replacement of tag.
		return $replace;
	}
	
	/**
	 * Removes any HTML tags not allowed in the admin "allowed HTML"
	 * 
	 * @param string $text
	 * @param bool $remove_all
	 * @return string Text with disallowed HTML tags removed
	 */
	public static function replaceDisallowedHtml ($text,$remove_all=0)
	{
		$addon_result = geoAddon::triggerDisplay('overload_geoFilter_replaceDisallowedHtml', 
			array ('text'=>$text, 'remove_all'=>$remove_all), geoAddon::OVERLOAD);
		if ($addon_result !== geoAddon::NO_OVERLOAD) {
			//an addon has replaced this function
			return $addon_result;
		}
		
		self::initHtmlAllowed();
		
		//kill HTML comments
		$text = preg_replace('/<!--[\S\s]*?-->/','',$text);
		
		//remove starting comments, that could result in the entire page being commented out..
		$text = str_replace('<!--','',$text);
		
		
		
		
		
		//remove any not-allowed tags using self::replaceTag() for each tag found in the text.
		
		//IF YOU Change the next line, be sure to TEST THOROUGHLY!  Needs to handle <{$var}>, along with
		//any combo of \slashes/, "double quotes" and 'single quotes' all "inside" something looking like a tag.
		$text = preg_replace_callback("/(<\/?)([^\s>]+)([^>]*>)/", function ($matches) use ($remove_all) {
			return geoFilter::replaceTag($matches[0], $matches[2], $remove_all);
			}, $text);

		//allow any addons to do fancy filtering:
		$text = geoAddon::triggerDisplay('filter_geoFilter_replaceDisallowedHtml', $text, geoAddon::FILTER);
		
		return $text;
	}
	
	
	/**
	 * Filters the description for a listing, so that it is suitable for display
	 * in category browsing.
	 * 
	 * @param string $description
	 * @param bool $forceHtml If true, will strip all HTML despite what is set
	 *   in admin panel.  Param added in version 6.0.4
	 * @return string
	 */
	public static function listingDescription ($description, $forceHtml = false)
	{
		$addon_result = geoAddon::triggerDisplay('overload_geoFilter_listingDescription', 
			array ('description'=>$description), geoAddon::OVERLOAD);
		if ($addon_result !== geoAddon::NO_OVERLOAD) {
			//an addon has replaced this function
			return $addon_result;
		}
		
		$description = geoString::fromDB($description);
		if ($forceHtml || !DataAccess::getInstance()->get_site_setting('allow_html_description_browsing')) {
			$description = preg_replace('/<br([\s]*)?\/?>/i', " \n", $description);
			//Use this instead of strip tags, as strip tags doesn't handle mal-formed HTML too well
			//(as it says on php.net).  Also this adds hooks to allow addons to do filtering if needed.
			$description = self::replaceDisallowedHtml($description, 1);
			//Fix it so that silly listings with < that don't have matching > won't mess up layout
			$description = str_replace('<','&lt;',$description);
		}
		$description = trim($description);
		$description = geoAddon::triggerDisplay('filter_geoFilter_listingDescription', $description, geoAddon::FILTER);
		return $description;
	}
	
	/**
	 * Shortens the description to the given length, for use in category
	 * browsing.
	 * 
	 * @param string $description
	 * @param int $len The length to allow for the listing.
	 * @return string
	 */
	public static function listingShortenDescription ($description,$len)
	{
		$addon_result = geoAddon::triggerDisplay('overload_geoFilter_listingShortenDescription', 
			array ('description'=>$description, 'len'=>$len), geoAddon::OVERLOAD);
		if ($addon_result !== geoAddon::NO_OVERLOAD) {
			//an addon has replaced this function
			return $addon_result;
		}
		
		if(strlen($description) > $len) {
			$small_string = geoString::substr($description,0,$len);
			$position = strrpos($small_string," ");
			$smaller_string = (($position > 0)? geoString::substr($small_string,0,$position): $small_string) . '...';
		} else {
			//didn't need to shorten the description
			$smaller_string = $description;		
		}
		$smaller_string_filtered = geoAddon::triggerDisplay('filter_geoFilter_listingShortenDescription', $smaller_string, geoAddon::FILTER);
		return $smaller_string_filtered;
	}
	
	/**
	 * Internal use.
	 * @internal
	 */
	private static $_badwords;
	
	/**
	 * Filter a string using badwords as set in admin panel.
	 * @param string $string The string that should get filtered
	 * @return string Filtered string
	 * @since Version 4.1.2
	 */
	public static function badword ($string)
	{
		$db = DataAccess::getInstance();
		if (!isset(self::$_badwords)) {
			self::$_badwords = $db->GetAll("SELECT * FROM ".geoTables::badwords_table." ORDER BY `badword_id`");
			//go through each one and HTML encode it if it is different
			foreach (self::$_badwords as $badword) {
				if ($badword['badword'] && geoString::specialChars($badword['badword']) !== $badword['badword']) {
					//add special char version to array of badwords to check, so that it catches
					//for inputs that are filtered...
					$badword['badword'] = geoString::specialChars($badword['badword']);
					self::$_badwords[] = $badword;
				}
			}
		}
		
		if (self::$_badwords) {
			foreach (self::$_badwords as $badword) {
				if (strlen(trim($string)) == 0) {
					//entire string has already been replaced with nothing
					break;
				}
				if (!strlen(trim($badword['badword'])) || $badword['badword'] == $badword['badword_replacement']) {
					//bad badword, search is same as replacement
					continue;
				}
				if (stripos($string,$badword['badword']) !== false) {
					//it's in there somewhere
					if ($badword['entire_word']){
						//lets let the badwords have the weird chars in them, w/o breaking eregi_replace.
						$cleaned = preg_quote($badword['badword'], '/');
						
						$string = preg_replace("/\b($cleaned)\b/i", $badword['badword_replacement'],$string);
					} else {
						$string = str_ireplace($badword['badword'],$badword['badword_replacement'],$string);
					}
				}
			}
		}
		
		return $string;
	}
	
	/**
	 * Cleans a title so it can be safely used in URL between / and not break the URL.
	 * 
	 * @param string $string
	 * @param array $allow Array of chars to allow that would normally be stripped from string.
	 * @param bool $trim If false, will not trim white-space from ends of the string.  Param added in version 6.0.0
	 * @return string
	 * @since Version 5.1.0
	 */
	public static function cleanUrlTitle ($string, $allow=array(), $trim = true)
	{
		//replace spaces with -
		//replace white space and _ with -
		$string = preg_replace('/[-\s_\n\r]+/','-', $string);
		
		//remove any HTML tags... < and > are checked below, but this will 
		//clear up the text of the tag, as well
		$string = strip_tags($string);
		
		//prepare vars in skip list to be inserted in regular expression.
		//First, convert to string if it's an array
		$allow = (is_array($allow))? $allow: array($allow);
		
		
		//NOTE: We've already removed any white space above.
		//SECOND NOTE: Removing * and ! as they are reserved in URLs according to
		//http://www.w3.org/Addressing/URL/4_URI_Recommentations.html
		$block = array (
			'/',
			'.',
			'&',
			'\'',
			'"',
			'?',
			'<',
			'>',
			'#',
			':',
			'\\',
			'%',
			'+',
			'*',
			'!',
		);
		
		//get rid of any that are same as what we want to allow
		$block = array_diff($block, $allow);
		$block = preg_quote(implode('',$block), '/');
		$search = "/[$block]+/";
		
		$replace = "";
		$string = preg_replace($search,$replace,$string);
		
		if ($trim) {
			//get rid of extra white space
			$string = preg_replace('/[-]+/','-',trim($string,'-'));
		}
		
		return $string;
	}
	
	/**
	 * Cleans the specified listing tag
	 * 
	 * @param string $tag
	 * @return string
	 * @since Version 5.1.0
	 */
	public static function cleanListingTag ($tag)
	{
		//badword replacement
		$tag = self::badword($tag);
		
		$tag = self::cleanUrlTitle(trim($tag));
		
		//lowercase all tags
		$tag = strtolower($tag);
		
		return trim($tag,'- ');
	}
}