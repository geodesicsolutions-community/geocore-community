<?php

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
	
	/**
	 * Loop through all "checks" and return an array of all notifications
	 *
	 */
	function getNotifications() {
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
		if(count($notification))
			return $notifications;
		else
			return null;
	}

	function getNotificationsAsHTML() {
		// Get the DB object
		$db = true;
		include '../get_common_vars.php';
		
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
	function addCheck($callback) {
		if(is_callable($callback)) {
			Notifications::_checks($callback);
			return true;
		} else {
			trigger_error("ERROR NOTIFICATIONS: Callback function or method
				is invalid. Callback: ".print_r($callback, true));
			return false;
		}
	}
	
	/**
	 * Used to store callback functions 
	 *
	 * @param callback $check
	 * @return array
	 */
	function _checks($check = null) {
		static $checks = array();
		if(null != $check) {
			$checks[] = $check;
		}
		return $checks;
	}
}