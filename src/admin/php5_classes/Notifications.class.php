<?php
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## 
##    16.02.1-8-ge7a5654
## 
##################################
/**
 * This class can be used to store and display global notifications
 * How it works:
 * 1. "Checks" are added to the Notification class using 
 *	Notifications::addCheck()
 * 2. The Notifications::getNotifications() function is called and to 
 * 	check for notifications. An array of notifications is returned, or false if 
 * 	there are none
 */

class Notifications {
	
	private static $notifications = array();
	private static $_default_notified = false;
	
	/**
	 * adds a security alert
	 *
	 * @param string $alert
	 * @param array $children is optional
	 */
	public static function addSecurityAlert($alert,$children=array())
	{
		if (DataAccess::getInstance()->get_site_setting('developer_supress_notify')) {
			return;
		}
		if(!empty($children)) {
			$i = 0;
			foreach($children as $var1 =>$var2) {
				$i++;
				$child[$i] = "<div style='text-indent: -10px; padding-left: 10px; margin-left: 3px; margin-top: 3px;'>";
				
				if(!is_numeric($var1)) {
					$child[$i] .= $var1." --";
				}

				$child[$i] .=	" <em>$var2</em>
					</div>";
			}
			$optional_reasons = implode($child);
		}
		if($optional_reasons) {
			$alert .= "<div>$optional_reasons</div>";
		}
		self::add("<strong style='color: red;'>Security Alert:</strong> $alert ");
	}
	
	public static function addNoticeAlert($notice,$children=array())
	{
		if (DataAccess::getInstance()->get_site_setting('developer_supress_notify')) {
			return;
		}
		if(!empty($children)) {
			foreach($children as $var1 =>$var2) {
				$child_var = "<strong>$var1</strong> --";
				$child_var .=	" <em>$var2</em>";
				
				$child[] = "<div style='text-indent: -10px; padding-left: 10px; margin-left: 3px; margin-top: 3px;'>$child_var</div>";
			}
			$optional_reasons = implode($child);
		}
		if($optional_reasons) {
			$notice .= "<div>$optional_reasons</div>";
		}
		self::add("<strong style='color: #FF8000;'>Notice Alert:</strong> $notice ");
	}
	
	public static function add ($n)
	{
		if (DataAccess::getInstance()->get_site_setting('developer_supress_notify')) {
			return;
		}
		if(empty($n)) {
			return true;
		}
		
		if(is_array($n) && !empty($n)) {
			foreach($n as $nChild) {
				if(!is_array($nChild)) {
					self::add($nChild);
				}
			}
		}
		self::$notifications[] = $n;
	}
	
	public static function getArray ()
	{
		return self::$notifications;
	}
	
	/**
	 * Check these on every page load
	 *
	 */
	public static function defaultChecks ()
	{
		//checks if default checks has already ran. 
		if(self::$_default_notified) {
			return true;
		}
		
		if(defined('DEMO_MODE')) {
			Notifications::add("<span style='color: red'><strong>NOTICE:</strong> The forms in this demo will not submit.</span>");
		}
		
		Notifications::add(Admin_site::securityCheck());
		
	//	Notifications::addCheck(array('Admin_site', 'securityCheck'));
		
		//Notifications::addCheck(array('Admin_template_management','checkTemplatesAndNotify'));
		
		if(geoMaster::is('auctions')) {
			Notifications::addCheck(array('Ad_configuration', 'incrementExists'));
		}
		
		if ( file_exists( GEO_BASE_DIR . 'xss_filter_inputs.php' ) ) {
			Notifications::add('<strong>xss_filter_inputs.php</strong> still exists in your root directory. This file is no longer needed, so it is safe to remove.'); 
		}
		
		if ( is_dir( GEO_BASE_DIR . 'scopbin' ) ) {
			Notifications::add('The <strong>scopbin</strong> directory still exists in your root directory. This is no longer needed, so it is safe to remove.');
		}
		
		if(geoPC::geoturbo_status() && DataAccess::getInstance()->get_site_setting('gt_license_notify') == 1) {
			Notifications::add('<strong style="color: red;">ATTENTION:</strong> Your license has been suspended for non-payment of hosting fees. Contact <a href="mailto:sales@geodesicsolutions.com">Geodesic Solutions</a> to re-activate.');
		}
	
		self::$_default_notified = true;
	}
	
	
	
	
	/**
	 * Loop through all "checks" and return an array of all notifications
	 *
	 */
	public static function getNotifications() {
		$callbacks = Notifications::_checks();
		
		$notifications = array();
		foreach($callbacks as $check) {
			$notification = call_user_func($check);
			if(is_array($notification)) {
				$notifications = array_merge($notifications, $notification);
			} else if(strlen(trim($notification))) {
				$notifications[] = $notification;
			}
		}
		if (count(self::$notifications)) {
			$notifications = array_merge($notifications, self::$notifications);
		}
		if(count($notifications))
			return $notifications;
		else
			return null;
	}

	public static function getNotificationsAsHTML ()
	{
		// Get the DB object
		$db = true;
		include GEO_BASE_DIR.'get_common_vars.php';
		
		$notifications = Notifications::getNotifications();
		if(!(is_array($notifications) && count($notifications))) {
			$notifications = '';
		} else {
			ob_start();
			if($db->get_site_setting("developer_supress_notify") != 1)
				include 'templates/notification_box.tpl.php';
			$notifications = ob_get_contents();
			ob_end_clean();
		}
		return $notifications;
	}
	
	/**
	 * Specify a function to create a notification message if one is needed.
	 * Because notifications will probably be displayed on every page of the
	 * admin, class methods added here should be static (called without having to
	 * instantiate an object).
	 * @param mixed $callback Callback in the form of a string (for a global 
	 * 	function) or a array -- in the form of array('Class', 'method') -- for 
	 * 	static methods.
	 */
	public static function addCheck ($callback)
	{
		if (DataAccess::getInstance()->get_site_setting('developer_supress_notify')) {
			return;
		}
		if(@is_callable($callback)) {
			Notifications::_checks($callback);
			return true;
		} else {
			trigger_error("ERROR NOTIFICATIONS: Callback function or method
				is invalid. Callback: ".print_r($callback, true));
			return false;
		}
	}
	private static $checks;
	/**
	 * Used to store callback functions 
	 *
	 * @param callback $check
	 * @return array
	 */
	public static function _checks($check = null)
	{
		if (!is_array(self::$checks)){
			self::$checks = array();
		}
		if(null != $check) {
			self::$checks[] = $check;
		}
		return self::$checks;
	}
}