<?php
//User.class.php
/**
 * This holds the geoUser class.
 * 
 * @package System
 * @since Version 4.0.0
 */


/**
 * An object representative of a certain user in the system.
 * 
 * Uses the "magic methods" __get and __set to allow accessing user's parameters
 * by $user->field and set by $user->field = $info.  When getting or setting
 * fields in this way, no encoding or decoding is done automatically, so certain
 * fields will need to be encoded or decoded.
 *   
 * @package System
 * @since Version 4.0.0
 */
class geoUser
{
	/**
	 * The array of user data, formatted as it would be in the DB.  Currently
	 * no encoding/decoding is done.
	 * 
	 * @var array
	 */
	private $_userData = array();
	
	/**
	 * Array of user objects that have already been retrieved this page load.
	 * @var array Array of geoUser objects.
	 */
	private static $_users = array();
	
	
	/**
	 * Convienience method, gets the user name for the specified userId.
	 * 
	 * Using this is the about the same as calling geoUser::getUser($id)->username.
	 * 
	 * @param int $user_id
	 * @return string The username for the user id specified, or empty string
	 *  if user ID not valid or not found.
	 */
	public static function userName ($user_id)
	{
		$user_id = intval($user_id);
		if(!$user_id) return '';
		$user = self::getUser($user_id);
		if (is_object($user)) {
			return $user->username;
		}
		return '';
	}
	
	/**
	 * Convienience method, gets the userId for the specified username, or 0 if the 
	 * username could not be found or is invalid.  Note that using this is no more
	 * efficient than calling geoUser::getUser($username)->id
	 *
	 * @param string $username
	 * @return int The user's ID or 0 if not found.
	 */
	public static function getUserId($username)
	{
		if(!$username) return 0;
		$user = self::getUser($username);
		if (is_object($user)) {
			return $user->id;
		}
		//could not get user based on username.
		return 0;
	}
	
	/**
	 * Standard way to get a user object, either by username or user id.
	 *
	 * @param int|string $user Either the user ID, or the username.
	 * @return geoUser|null Will return null if user could not be found.
	 */
	public static function getUser ($user)
	{
		//make sure it is either a number or a string, not something weird like
		//an array or something.
		if (is_numeric($user)) {
			$user = (int)$user;
		} else {
			$user = ''.trim($user);
		}
		
		if (!$user) {
			//invalid
			return null;
		}
		
		if (!isset(self::$_users[$user])) {
			if (is_numeric($user)) {
				$where = "ud.`id` = ?";
			} else {
				$where = "ud.`username` = ?";
			}
			//get the listing and info
			$db = DataAccess::getInstance();
			
			$sql = "SELECT ud.*, ug.*, l.status FROM ".geoTables::userdata_table." as ud, ".geoTables::logins_table." as l, ".geoTables::user_groups_price_plans_table." as ug WHERE $where
			AND l.id=ud.id AND ud.id = ug.id LIMIT 1";
			$data = $db->GetRow($sql,array($user));
			if (empty($data)) {
				self::$_users[$user] = null;
				return null;
			}
			
			//Also set the bitmask
			$bitmask = (geoPC::is_ent())? $db->GetOne("SELECT `restrictions_bitmask` FROM ".geoTables::groups_table." WHERE `group_id`=?", array($data['group_id'])) : 1+2+4+8+16+32;
			$data['restrictions_bitmask'] = (int)$bitmask;
			
			//load city/state/country by new Regions method (so that any places that use e.g. $user->state can still work
			$levels = geoRegion::getLevelsForOverrides();
			$regions = geoRegion::getRegionsForUser($data['id']);
			if($levels['city']) {
				$data['city'] = geoRegion::getNameForRegion($regions[$levels['city']]);
			}
			$data['state'] = geoRegion::getNameForRegion($regions[$levels['state']]);
			$data['country'] = geoRegion::getNameForRegion($regions[$levels['country']]);
			
			$userObj = new geoUser($data);
			
			self::$_users[$data['id']] = self::$_users[$data['username']] = $userObj;
		}
		
		return self::$_users[$user];
	}
	
	
	
	/**
	 * Returns an associative array representation of the user's data, great
	 * for using in templates and the like where objects are not ideal.
	 *
	 * @return array
	 */
	public function toArray ()
	{
		return $this->_userData;
	}
	
	/**
	 * Gets data for specified user id or username.
	 * 
	 * This is about the same as calling geoUser::getUser($user_id)->$setting if
	 * $setting is set, or geoUser::getUser($user_id)->toArray() if $setting is
	 * not set.
	 *
	 * @param string|int $user_id Either the user id, or the username.
	 * @param string $setting A specific setting to get for the user.  If not
	 *  set, will return an array of all the user's details.
	 * @return mixed The setting (or settings), or null if user not valid or
	 *  something went wrong.
	 */
	public static function getData ($user_id=0,$setting=null)
	{
		if (!$user_id) {
			return false;
		}
		$user = self::getUser($user_id);
		if (!is_object($user)) {
			return null;
		}
		if ($setting === null) {
			return $user->toArray();
		}
		return $user->$setting;
	}
	
	/**
	 * Gets whether or not the given user is verified or not, without creating
	 * extra overhead of getting "full" user info for every single user checked.
	 * 
	 * Note that this DOES check to make sure verify_accounts is turned on first.
	 * 
	 * @param int $user_id
	 * @since Version 6.0.0
	 */
	public static function isVerified ($user_id)
	{
		$user_id = (int)$user_id;
		if ($user_id <= 1) {
			//not good user
			return false;
		}
		$db = DataAccess::getInstance();
		if (!$db->get_site_setting('verify_accounts')) {
			//verify accounts turned off
			return false;
		}
		if (isset(self::$_users[$user_id])) {
			//already have data for that user
			return self::$_users[$user_id]->verified=='yes';
		}
		//do NOT set up user for each one, only use the data if already retrieved,
		//don't want to add a ton of overhead...  so just do simply query
		return ('yes'==$db->GetOne("SELECT `verified` FROM ".geoTables::userdata_table." WHERE `id`=?", array($user_id)));
	}
	
	/**
	 * Gets the salutation, for use in e-mails sent to this user.
	 *
	 * @return string The salutation (including 2 newlines) to use in an e-mail to this user.
	 */
	public function getSalutation ()
	{
		switch (DataAccess::getInstance()->get_site_setting('email_salutation_type')) {
			case 2:
				//display firstname
				return "$this->firstname";
				break;
				
			case 3:
				//display firstname and lastname
				return "$this->firstname $this->lastname";
				break;
				
			case 4:
				//display lastname and firstname
				return "$this->lastname $this->firstname";
				break;
				
			case 5:
				//display email address
				return "$this->email";
				break;
				
			case 6:
				//display firstname lastname (username)
				return "$this->firstname $this->lastname ({$this->username})";
				break;
				
			case 1:
				//Break omitted on purpose
			default:
				//display username
				return "$this->username";
				break;
		}
	}
	
	/**
	 * Do not create a new user object, use geoUser::getUser().
	 *
	 * @param array $userData Array of user's data (passed from self::getUser() when
	 *  initializing the user)
	 */
	private function __construct($userData)
	{
		$this->_userData = $userData;
	}
	
	/**
	 * Used to get a specific user detail for the user, by
	 * using $user_obj->user_detail.
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		$name = strtolower($name);
		if (isset($this->_userData[$name])){
			return $this->_userData[$name];
		}
		return null;
	}
	
	/**
	 * Will update any value set in userdata.  Does NOT update logins table or geoRegions (city/state/country).
	 *
	 * @param string $name
	 * @param string $value
	 * @return bool
	 */
	public function __set($name,$value)
	{
		$this->_userData[$name] = $value;
		
		$db = DataAccess::getInstance();
		
		$sql = "UPDATE ".geoTables::userdata_table." SET `$name`=?  WHERE `id`=? LIMIT 1";
		$result = $db->Execute($sql, array($value,$this->_userData['id']));
		if($result) {
			return true;
		}

		return false;
	}
}