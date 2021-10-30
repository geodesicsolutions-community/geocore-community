<?php
// Utility.class.php
/**
 * Holds the geoUtil class, which has various light-weight utility methods. As
 * a group of a certain type of utility start to gather, they will be broken
 * off into their own class.
 * 
 * @package System
 */



/**
 * Misc utility functions
 * 
 * @package System
 */
class geoUtil {
	/**
	 * Used to include a needed PEAR library file.  Built so that the needed
	 * PEAR libraries can be uploaded to the sub-folder classes/SOAP/ if SOAP
	 * is not installed on the server.
	 * 
	 * An example call would be:
	 * geoUtil::includePEAR('/SOAP/Client.php')
	 * 
	 * @param string $filename
	 * @param bool $add_include_path If set to true, will add the classes/PEAR directory
	 *  in the include path when it is done.
	 * @param bool $require
	 */
	public static function includePEAR($filename, $add_include_path = false, $require = false){
		$path = CLASSES_DIR.'PEAR';
		if (file_exists($path . '/' . $filename)){
			//add classes/PEAR to include path
			$current_include_path = get_include_path();
			if (strpos($current_include_path, $path) === false){
				set_include_path($current_include_path . PATH_SEPARATOR . $path);
			} else {
				unset($current_include_path);
			}
		}
		if ($require) {
			$result = require_once($filename);
		} else {
			$result = include_once($filename);
		}
		
		if (isset($current_include_path) && !$add_include_path){
			//restore include path
			set_include_path($current_include_path);
		}
		return $result;
	}
	
	/**
	 * Returns the current time, adjusted 
	 * for the time shift as set in the admin.
	 *
	 * @param int $timestamp If specified, will take given timestamp and shift
	 *  according to time shift set in admin.
	 * @return int
	 */
	public static function time($timestamp = null){
		if ($timestamp === null && defined('GEO_SHIFTED_TIME')){
			return GEO_SHIFTED_TIME;
		}
		$timeS = ($timestamp === null)? time(): intval($timestamp);
		$db = DataAccess::getInstance();
		$time = $timeS + (3600 * $db->get_site_setting('time_shift'));
		if ($timestamp === null) {
			define ('GEO_SHIFTED_TIME',$time);
		}
		return $time;
	}
	
	/**
	 * Compares current IP to ALLOWED_IPS_WHEN_SITE_DISABLED which should be
	 * a string of IPs separated by commas.  If current IP is in this list then
	 * returns true, else returns false.  If the list contains partial IPs, then
	 * only that part which is in the list is compared
	 * This function works only in conjunction with the site on/off switch.
	 *
	 * @return bool true=ip in list|false=ip not in list
	 */
	public static function isAllowedIp()
	{
		$allowed_ips=DataAccess::getInstance()->get_site_setting('allowed_ips_when_site_disabled');
		if (!$allowed_ips) {
			return false;
		}
		
		$thisIP = self::getRemoteIp();
		$authorized_ips = explode(',',$allowed_ips);
		foreach ($authorized_ips as $authIP) {
			//return the moment we find a good ip, else continue checking ips
			$authIP = trim(preg_quote($authIP));
			if (strlen($authIP) && preg_match("/^".$authIP."/",$thisIP)) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Gets the remote IP by trying out all the normal methods.  Mainly used by isAllowedIp but it could
	 * be usefull for other places that need the remote IP for whatever reason.
	 * 
	 * @return string the IP or the text "unknown" if it could not be detected because PHP did not report it.  (this can
	 *  happen for instance, when running in CLI mode.
	 */
	public static function getRemoteIp()
	{
		if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
			return getenv("HTTP_CLIENT_IP");
	
		if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
			return getenv("HTTP_X_FORWARDED_FOR");
	
		if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
			return getenv("REMOTE_ADDR");
	
		if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
			return $_SERVER['REMOTE_ADDR'];
	
		return "unknown";
	}
}
