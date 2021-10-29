<?php
//String.class.php
/**
 * Holds the geoString class, which is a swiss army knife for your mouth.
 * 
 * @package System
 * @since Version 3.1.0 (I think)
 */
/**************************************************************************
Geodesic Classifieds & Auctions Platform 18.02
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## ##    7.3beta5-3-gbc1dc54
## 
##################################


/**
 * Utility functions relating to languages and string manipulation.
 * 
 * @package System
 * @since Version 3.1.0 (I think)
 */
class geoString
{
	/**
	 * This is the regular expression used in the preg_match for the function
	 * isEmail().  This will ONLY be the expression, it will not contain the
	 * surrounding "/" chars that are needed by preg_* functions.  Also, the expr
	 * will NOT have any "capturing" sub-patterns.
	 * 
	 * If the e-mail regular expression needs to be used for other
	 * purposes, use this var when possible, so that if it needs to be updated,
	 * it only needs to be updated in one place.
	 * 
	 * Note:  This is used in multiple places, so if changing, be very careful and
	 * TEST!
	 * 
	 * Regex does this:
	 * Broken up into 2 parts: local part and domain (seperated by @):
	 * local part:
	 *  -can have any alpha-numeric, plus chars: ._+-
	 *  -the first char can only be alpha-numeric
	 * 
	 * domain:
	 *  -Can have alpha-numeric, plus chars: .- (note that _ is invalid for domains)
	 *  -the first char must be alpha-numeric, and multiple . or - are not allowed without
	 *   alpha-numeric chars between them, so no .. or .- or --
	 *  -the last part (the top level domain) must be between 2 and 6 chars, at the
	 *   time of this being made the shortest top-level are 2 letter country codes,
	 *   and the longest are .museum (6 chars) and .travel (6 chars)
	 * @param string
	 */
	const EMAIL_PREG_EXPR = "[a-zA-Z0-9]+[a-zA-Z0-9._+-]*@[a-zA-Z0-9]+(?:(?:\.|-)[a-zA-Z0-9]+)*\.[a-zA-Z]{2,6}";
	
	/**
	 * Converts a string from a given charset, to a given charset, using the "clean method"
	 * as set in the config file, or using utf8_encode() if able to.
	 * 
	 * @param string $string
	 * @param string $charset_from
	 * @param string $charset_to
	 * @since Version 6.0.0
	 */
	public static function convertCharset ($string, $charset_from = null, $charset_to = null)
	{
		//set defaults, assume converting from charsetFrom TO charsetClean
		if ($charset_to === null) {
			//get the charset to use for encoding.
			$charset_to = geoString::getCharset();
		}
		
		if ($charset_from === null) {
			$charset_from = geoString::getCharsetFrom();
		}
		
		if ($charset_to == $charset_from || !$charset_to || !$charset_from) {
			//nothing to convert
			return $string;
		}
		
		if ($charset_from == 'ISO-8859-1' && $charset_to == 'UTF-8') {
			//simple, use encode UTF-8
			$string = utf8_encode($string);
		} else if ($charset_from == 'UTF-8' && $charset_to == 'ISO-8859-1') {
			//Use utf8 decode
			$string = utf8_decode($string);
		} else if ((defined('CLEAN_METHOD') && CLEAN_METHOD == 'mb_convert_encoding') || (!defined('CLEAN_METHOD') && function_exists('mb_convert_encoding'))) {
			//use mb_convert_encoding
			$string = mb_convert_encoding($string, $charset_to, $charset_from);
		} else if (function_exists('iconv')) {
			//use iconv
			$string = iconv($charset_from, $charset_to, $string);
		} else {
			//hopefully one of those methods worked, if not then oops!
			trigger_error('ERROR STRING: Not able to convert string from '.$charset_from.' TO '.$charset_to.', none of normal methods seem to work on this server.');
		}
		
		return $string;
	}
	
	/**
	 * The equivelent of htmlspecialchars(), used to prepare text for using
	 * in textarea or as part of an HTML tag, where the text needs to be
	 * encoded.
	 * 
	 * Used to ensure save encoding for charsets that might be messed up 
	 * by running normal htmlspecialchars() or htmlentities() on them.
	 *
	 * @param string $input The string that gets encoded.
	 * @param string $charset_clean The charset being used in the string.  If none
	 *  is given, then the site setting charset is used.
	 * @param int $quoteStyle The quote style, as used in htmlspecialchars()
	 *  if not set, set to value that is best for the charset used, usually ENT_COMPAT
	 * @param bool $entities If set to true, will use htmlentities instead of
	 *  htmlspecialchars.
	 * @param bool $addSlashes if set to true, will call addslashes on the string before cleaning it. (handy
	 *  for inserting inside of html tags inside javascript)
	 * @return String string with special chars HTML encoded.
	 */
	public static function specialChars ($input, $charset_clean = null, $quoteStyle = null, $entities = false, $addSlashes = false)
	{
		if (is_null($charset_clean)){
			//get the charset to use for encoding.
			$charset_clean = geoString::getCharset();
		}
		
		//get from and to charsets
		$charset_from = geoString::getCharsetFrom();
		$charset_to = geoString::getCharsetTo();
		
		//set quote style if needed for the charset
		//if quote style needs to be different for specific charset, this is where to do it.
		$quoteStyle = (is_null($quoteStyle))? ENT_QUOTES : $quoteStyle;
		
		//clean the input
		if ($addSlashes) {
			//add slashes BEFORE converting to html special chars
			$input = addslashes($input);
		}
		if ($charset_from) {
			//convert FROM $charset_from TO $charset_clean before the filtering.
			$input = self::convertCharset($input, $charset_from, $charset_clean);
		}
		$input = (($entities===false)? htmlspecialchars($input, $quoteStyle, $charset_clean) : htmlentities($input, $quoteStyle, $charset_clean));
		if ($charset_to) {
			//Convert FROM $charset_clean TO $charset_to AFTER cleaning
			$input = self::convertCharset($input, $charset_clean, $charset_to);
		}
		//return encoded string.
		return $input;
	}
	
	/**
	 * The equivelent of html_entity_decode(), used to prepare text for using
	 * in textarea or as part of an HTML tag, where the text needs to be
	 * encoded.
	 * 
	 * Used to ensure save encoding for charsets that might be messed up 
	 * by running normal htmlspecialchars() on them.
	 *
	 * ONLY CALL STATICALLY (geoString::specialCharsDecode())
	 *
	 * @param String $input The string that gets encoded.
	 * @param (Optional) String $charset The charset being used in the string.  If none
	 *  is given, then the site setting charset is used.
	 * @param (Optional) Int $quoteStyle The quote style, as used in htmlspecialchars()
	 *  if not set, set to value that is best for the charset used, usually ENT_COMPAT
	 * @param (Optional) boolean $limitedEntities If set to true, will only decode those
	 *  chars that are encoded using specialChars() (or htmlspecialchars()).  Setting to true
	 *  requires more testing in alternate charsets, it is not known if it is charset friendly.
	 * @return String string with special chars HTML encoded.
	 */
	public static function specialCharsDecode ($input, $charset_clean = null, $quoteStyle = null, $limitedEntities = false)
	{
		if (is_null($charset_clean)){
			//get the charset to use for encoding.
			$charset_clean = geoString::getCharset();
		}
		
		//get from and to charsets
		$charset_from = geoString::getCharsetFrom();
		$charset_to = geoString::getCharsetTo();
		
		//set quote style if needed for the charset
		//if quote style needs to be different for specific charset, this is where to do it.
		$quoteStyle = (is_null($quoteStyle))? ENT_QUOTES: $quoteStyle;

		//return encoded string.
		if ($limitedEntities) {
			return htmlspecialchars_decode($input, $quoteStyle);
		} else {
			if ($charset_from) {
				//convert FROM $charset_from TO $charset_clean before the filtering.
				$input = self::convertCharset($input, $charset_from, $charset_clean);
			}
			$input = html_entity_decode($input, $quoteStyle, $charset_clean);
			if ($charset_to) {
				//Convert FROM $charset_clean TO $charset_to AFTER cleaning
				$input = self::convertCharset($input, $charset_clean, $charset_to);
			}
			return $input;
		}
	}
	
	/**
	 * Use this function to prepare strings to be inserted into the DB.
	 * If this is used to insert a string into the DB, then when retrieving
	 * the data, the sister function geoString::fromDB() must be used
	 * to un-do what this one does.
	 * 
	 * Note that this is done NOT for sql injection prevention, since
	 * the ADODb libraries already provide ways to prevent sql injection,
	 * the primary reason is to ensure the best compatibility for charsets.
	 *
	 * @param string $string
	 * @return string String that has been prepared to be inserted into the DB
	 */
	public static function toDB ($string)
	{
		return urlencode($string);
	}
	
	/**
	 * Un-does what the toDB function does.  Use this when retrieving data that
	 * has been processed using toDB prior to being inserted into the database.
	 *
	 * @param string $string
	 * @return string
	 */
	public static function fromDB ($string)
	{
		return urldecode($string);
	}
	
	/**
	 * Gets the charset for the given language_id.
	 * (Charset per language not implemented yet! For
	 * now, charset is site-wide, set in config.php) 
	 *
	 * @param int $language_id Language id to get
	 *  charset for.  Defaults to language used for main page.
	 *  (Oops! Language ID not really implemented yet, charset is site-wide)
	 * @return String The current charset, used for cleaning
	 *  strings, see the PHP function htmlspecialchars()
	 * @todo Add ability to set charset per language ID.
	 */
	public static function getCharset ($language_id=0)
	{
		$charset = (defined('CHARSET_CLEAN'))? CHARSET_CLEAN: 'ISO-8859-1'; //default to iso-8859-1
		//charset per language not implemented yet.
		return $charset;
	}
	
	/**
	 * Gets the charset from for the given language_id.
	 * (Charset per language not implemented yet! For
	 * now, charset from is site-wide, set in config.php) 
	 *
	 * @param int $language_id Language id to get
	 *  charset from for.  Defaults to language used for main page.
	 *  (not implemented yet, charset_from is site-wide)
	 * @return string|bool The current charset from setting, or false
	 *  if there is none. Used to
	 *  convert FROM charset before cleaning a string, see PHP function 
	 *  mb_convert_encoding()
	 */
	public static function getCharsetFrom ($language_id=0)
	{
		$charset_from = (defined('CHARSET_FROM'))? CHARSET_FROM: false;
		//charset per language not implemented yet.
		return $charset_from;
	}
	
	/**
	 * Gets the charset to for the given language_id.
	 * (Charset per language not implemented yet! For
	 * now, charset to is site-wide, set in config.php) 
	 *
	 * @param (Optional) Int $language_id Language id to get
	 *  charset to for.  Defaults to language used for main page.
	 *  (not implemented yet, charset_from is site-wide)
	 * @return Mixed The current charset to setting, or false
	 *  if there is none. Used to
	 *  convert TO charset after cleaning a string, see PHP function 
	 *  mb_convert_encoding()
	 */
	public static function getCharsetTo ($language_id=0)
	
	{
		$charset_to = (defined('CHARSET_TO'))? CHARSET_TO: false;
		//charset per language not implemented yet.
		return $charset_to;
	}
	
	/**
	 * Displays the price with pre and post currency, and formats the number according to number type.
	 * 
	 * @param float $price If this is not a float, things mess up
	 * @param string $pre String displayed before price.  If specified, the
	 *   value will be put through fromDB.  If left at default, will use site wide precurrency.
	 * @param string $post String displayed after price.  If specified, the
	 *   value will be put through fromDB.  If left at default, will use site wide post currency.
	 * @param string $price_type The type of price, either it is a "cart" price or a "listing" price,
	 *   if this value is specified and the price is 0, the value could be replaced with string as
	 *   specified in admin panel.  This parameter added in Version 6.0.0
	 * @return string
	 */
	public static function displayPrice ($price, $pre=false, $post=false, $price_type = null)
	{
		$tpl = new geoTemplate('system','classes');
		$db = DataAccess::getInstance();
		
		if ($price == 0) {
			if ($price_type==='cart' && $db->get_site_setting('cart_replace_zero_cost')) {
				$txt = $db->get_text(true, 10202);
				$tpl->assign(array('replace'=>true, 'replaceTxt' => $txt[500995]));
			} else if ($price_type==='listing' && $db->get_site_setting('listing_replace_zero_cost')) {
				$txt = $db->get_text(true, 59);
				$tpl->assign(array('replace'=>true, 'replaceTxt' => $txt[500996]));
			} else if(!$pre && $post) {
				//show only postcurrency value
				$tpl->assign('onlyPost', true);
			}
		}
		
		if ($pre === false || $post === false) {
			//set pre and/or post from site-wide defaults
			$pre = ($pre === false)? $db->get_site_setting('precurrency') : self::fromDB($pre);
			$post = ($post === false)? $db->get_site_setting('postcurrency') : self::fromDB($post);
		} else {
			//if being passed in, it will need to be fromDB'd
			$pre = self::fromDB($pre);
			$post = self::fromDB($post);
		}
		
		if($db->get_site_setting('hide_postcurrency')) {
			//show no postcurrencies on this site
			$post = false;
		}
		
		$tpl_vars = array (
			'pre'=>$pre,
			'post'=>$post,
			'number'=>geoNumber::format($price),
		);
		
	  	$tpl->assign($tpl_vars);
	  	return $tpl->fetch('String/displayPrice.tpl');
	}
	
	/**
	 * validates whether string passed is a valid e-mail address or not.  Validates 
	 * syntax only, using geoString::EMAIL_PREG_EXPR
	 *
	 * @param string $email
	 * @return bool
	 * @since Version 4.0.0 or so
	 */
	public static function isEmail ($email)
	{
		//Note: does NOT use eregi as that is not binary safe, so null chars can be inserted (for instance) and it still matches
		return ((preg_match('/^'.self::EMAIL_PREG_EXPR.'$/', $email))? true: false);
	}
	
	/**
	 * determines whether the domain of a given email address is blocked from registration 
	 *
	 * @param string $email
	 * @return bool
	 * @since Version 6.0.4
	 */
	public static function emailDomainCanRegister($email)
	{
		if(!(geoPC::is_ent()||geoPC::is_premier())) {
			//must be at least premier to block email domains
			return true;
		}
		$db = DataAccess::getInstance();
		$user_domain = explode('@',$email);
		$domain = $user_domain[1];
			
		$sql = "SELECT * FROM ".geoTables::block_email_domains." WHERE `domain` = ?";
		$result = $db->Execute($sql, array($domain));
		$foundEmailDomainInDB = ($result && $result->RecordCount() > 0) ? true : false;
			
		$email_restriction = $db->get_site_setting("email_restriction");
		if( ($foundEmailDomainInDB && $email_restriction == "blocked") || (!$foundEmailDomainInDB && $email_restriction == "allowed") ) {
			return false;
		} else {
			return true;
		}
	}
	
	
	/**
	 * Whether or not the provided string could be considered an int, works a little
	 * better than is_numeric since that would return true for something like
	 * 'abc' because of hex.
	 * 
	 * @param string $string
	 * @return bool
	 * @since Version 5.2.0
	 */
	public static function isInt ($string)
	{
		return (is_numeric($string) && ((int)$string==$string));
	}
	
	/**
	 * Makes sure the given file path does not have any invalid characters in it
	 * that would not work to use with a filename.
	 * 
	 * @param string $filePath
	 * @param bool $allowSingleQuote If set to true, will "allow" single quotes (defaults to false)
	 * @return bool
	 * @since Version 5.0.2
	 */
	public static function isFilePath ($filePath, $allowSingleQuote = false)
	{
		//check for any "forbidden" characters that would not be allowed in filenames
		$forbidden = array (
			'<',
			'>',
			':',
			'"',
			'|',
			'?',
			'*',
		);
		if (!$allowSingleQuote) {
			//also don't allow single quote
			$forbidden [] = "'";
		}
		if (geoFile::isWindows()) {
			//take off the beginning c:/ so it doesn't cause error
			$filePath = preg_replace('|^[a-zA-Z]{1}\:/|','',$filePath);
		}
		return (strlen($filePath) === strlen(str_replace($forbidden, '', $filePath)));
	}
	
	/**
	 * Breaks up "words" by the maxLength specified.  It is multi-byte safe as
	 * of version 6.0.0
	 *
	 * @param string $str String to break up the words in
	 * @param int $maxLength
	 * @param string $breakChar What will be used to split up the long words
	 * @return string
	 * @since Version 4.0.0 or so
	 */
	public static function breakLongWords ($str, $maxLength, $breakChar = "\n")
	{
		$maxLength = (int)$maxLength;
		if(!$maxLength) {
			//no max length -- nothing to do here
			return $str;
		}
		$breakChar = geoString::specialChars($breakChar);
		
		$search = "/([^-\s\t]{{$maxLength}})/";
		$replace = "$1{$breakChar}";
		
		return preg_replace($search,$replace,$str);
	}
	/**
	 * Map of accents, populated and used internally for replacing accents
	 * @var array
	 * @internal
	 */
	private static $_accentMap;
	
	/**
	 * Multibyte-safe version of substr() -- performs exactly like PHP's substr(), but automatically calls in CHARSET_CLEAN
	 * @param string $string
	 * @param int $start
	 * @param int|false $length Length of string, or false/empty for rest of string
	 * @param string $charset Will pull charset from config.php if not specified
	 * @return string
	 */
	public static function substr ($string, $start, $length=false, $charset=CHARSET_CLEAN)
	{
		if(!is_callable('mb_substr')) {
			//this server doesn't have mb_substr() for some odd reason...fall back on the normal version
			return substr($string, $start, (($length===false)?strlen($string):$length));
		}
		if($length===false) $length = mb_strlen($string);
		return mb_substr($string,$start,$length,$charset);
	}
	
	/**
	 * Replaces all accented characters with their non-accented equivelent.
	 * 
	 * ONLY Works reliably with UTF-8 charsets!  This will attempt to account
	 * for non-utf8 charset, but may not always work with such a charset.
	 * 
	 * Known side effect: if there are any multi-byte chars that are not converted
	 * by this function, they will be changed to ?.  This is un-avoidable, the only
	 * fix is to add the char to also be converted.
	 * 
	 * This is not designed to (or should it) work with "non-latin" charsets such as
	 * farsi, where the characters do not "translate" cleanly into english alphabet.
	 * This function should not be used on such charsets.
	 * 
	 * @param $string string to be filtered
	 * @return string
	 * @since Version 4.0.0
	 */
	public static function removeAccents ($string)
	{
		if (!self::$_accentMap) {
			//Strings to use for from/to in conversion. Once they
			//are decoded for use in this function, save them
			//in a static var so we don't have to keep decoding the
			//same string over and over.
			
			//data moved to the file StringAccents.class.php to keep this file un-cluttered.
			self::$_accentMap = geoStringData::getAccentMap();
		}
		//figure out whether we need to convert from and to UTF8 for string
		$isUtf8 = self::isUtf8($string);
		
		$string = ($isUtf8)? $string : utf8_encode($string);
		
		$search = array_keys(self::$_accentMap);
		$replace = array_values (self::$_accentMap);

		$string = str_replace($search, $replace, $string);
		
		$string = ($isUtf8)? $string : utf8_decode($string);
		
		return $string;
	}
	
	/**
	 * Determines if a string uses UTF8 encoding or not.  Note that there is no
	 * way to determine what the charset is, but you can tell if it is UTF8 or not
	 * (according to W3C I18N articles found on w3c.org)
	 * 
	 * Note that the regular expresion to determine if UTF-8 was addapted from:
	 * http://www.w3.org/International/questions/qa-forms-utf-8.en.php
	 * 
	 * @param string $string
	 * @return bool
	 * @since 4.0.0
	 */
	public static function isUtf8 ($string)
	{
		//regex modified from http://www.w3.org/International/questions/qa-forms-utf-8.en.php
		return preg_match('%^(?:
			  [\x09\x0A\x0D\x20-\x7E]			# ASCII
			| [\xC2-\xDF][\x80-\xBF]			# non-overlong 2-byte
			|  \xE0[\xA0-\xBF][\x80-\xBF]		# excluding overlongs
			| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}	# straight 3-byte
			|  \xED[\x80-\x9F][\x80-\xBF]		# excluding surrogates
			|  \xF0[\x90-\xBF][\x80-\xBF]{2}	# planes 1-3
			| [\xF1-\xF3][\x80-\xBF]{3}			# planes 4-15
			|  \xF4[\x80-\x8F][\x80-\xBF]{2}	# plane 16
		)*$%xs', $string); 
	}
}