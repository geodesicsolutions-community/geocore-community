<?php 
//UserRating.class.php
/**
 * Holds the geoUserRating class.
 *
 * @package System
 * @since Version 7.4.0
 */
##########GIT Build Data##########
##
## File Changed In GIT Commit:
##
##    7.5.3-36-gea36ae7
##
##################################
/**
 * Store, retrieve, and display inter-user ratings
 *
 * @package System
 * @since Version 7.4.0
 */
class geoUserRating
{
	/**
	 * For clarity when a "get rating" function returns that a user hasn't been rated yet
	 * @var int
	 */
	const NOT_RATED = 0;
	
	/**
	 * Display a user's rating. Show the overall rating unless the current user has made a rating of his own
	 * @param int $about a User ID#
	 * @param int $from If non-zero, force showing the user-specifc rating from this user
	 */
	public static function render($about, $from=0) 
	{
		if(!$about) {
			//bad request -- cannot proceed
			return false;
		}
		if(!$from) {
			//didn't specify an override, so check the current user for a rating
			$from = geoSession::getInstance()->getUserId();
			if($from == $about) {
				//user is viewing his own rating. pretend there is no from user so that it shows just the average
				$from = 0;
			}
		}
		$tpl_vars = array();
		if(!$from) {
			//if we STILL don't have $from, then the current user isn't logged in.
			//show the average rating, but don't allow creating a rating
			$tpl_vars['average_rating'] = self::getAverageRating($about);
			$tpl_vars['is_anon'] = true;
		} else {
			$individualRating = self::getIndividualRating($about, $from);
			if($individualRating == self::NOT_RATED) {
				$tpl_vars['average_rating'] = self::getAverageRating($about);
			} else {
				$tpl_vars['individual_rating'] = $individualRating;
			}
		}
		$tpl_vars['id'] = ($from) ? $from : 0; //script id for this rating...rater user id if applicable
		$tpl_vars['about'] = $about;
		if(defined('IN_ADMIN')) {
			//admin may show ratings from all users, but shouldn't adjust any of them. gimp in this functionality by pretending admin is anonymous
			$tpl_vars['is_anon'] = $tpl_vars['in_admin'] = true;
		}
		$tpl = new geoTemplate('system','classes');
		$tpl->assign($tpl_vars);
		return $tpl->fetch('UserRating/display_rating.tpl');
	}
	
	/**
	 * Computes the total average rating for a given user (arithmetic mean of all individual ratings about this user)
	 * @param int $about a User ID#
	 * @return float Average rating, clamped to nearest half-star
	 */
	public static function getAverageRating($about)
	{
		if(!$about) {
			//bad request -- cannot proceed
			return false;
		}
		$db = DataAccess::getInstance();
		$average = $db->GetOne("SELECT `average` FROM ".geoTables::user_ratings_averages." WHERE `about` = ?", array($about));

		//rating as held in the db is a averge of all ratings rounded to the nearest .01
		//for ease of display, clamp the rating to the nearest half-star
		
		if(!$average) {
			return self::NOT_RATED;
		} elseif($average < 1.25) {
			return 1;
		} elseif($average < 1.75) {
			return 1.5;
		} elseif($average < 2.25) {
			return 2;
		} elseif($average < 2.75) {
			return 2.5;
		} elseif($average < 3.25) {
			return 3;
		} elseif($average < 3.75) {
			return 3.5;
		} elseif($average < 4.25) {
			return 4;
		} elseif($average < 4.75) {
			return 4.5;
		} else {
			return 5;
		}
	}
	
	/**
	 * Pull all ratings about a user into a single average. Also sends notification emails if applicable
	 * @param int $about a User ID#
	 * @return float new average
	 */
	public static function computeAverageRating($about)
	{
		$db = DataAccess::getInstance();
		$stats = $db->GetRow("SELECT AVG(`rating`) as average, COUNT(`rating`) as count FROM ".geoTables::user_ratings." WHERE `about` = ? GROUP BY `about`", array($about));
		//round to nearest .01 and save to averages table
		$average = round($stats['average'], 2);
		
		if($stats['count'] >= 10) { //only do email notifications once a user has reached at least 10 ratings
			$lowRatingThreshold = $db->get_site_setting('user_rating_low_threshold');
			$notifyUser = $db->get_site_setting('user_rating_low_notify_user');
			$notifyAdmin = $db->get_site_setting('user_rating_low_notify_admin');
			
			$notified = $db->GetOne("SELECT `notified` FROM ".geoTables::user_ratings_averages." WHERE `about` = ?", array($about));
			$notified = ($notified)?1:0;
			
			//we also want to only send a notification once for a user being under the threshold (so he doesn't get spammed with each new rating)
			//then when the user climbs back above the threshold, we'll reset the notification flag so he can be notified again should his average drop again
			
			if($average < $lowRatingThreshold && ($notifyUser || $notifyAdmin) && $notified == 0) {
				$tpl_vars = array();
				$tpl_vars['average'] = $average;
				$tpl_vars['threshold'] = $lowRatingThreshold;
				$user = geoUser::getUser($about);
				if(!$user) {
					//something's very wrong...
					return false;
				}
				$tpl_vars['user'] = $user->toArray();
				
				if($notifyUser) {
					//notify the user of his low rating
					$tpl = new geoTemplate('system','emails');
					$msgs = $db->get_text(true,1);
					$tpl_vars['introduction'] = $msgs[502281];
					$tpl_vars['salutation'] = $user->getSalutation();
					$tpl_vars['messageBody'] = $msgs[502282];
					$tpl_vars['average_label'] = $msgs[502283];
					$tpl_vars['threshold_label'] = $msgs[502284];
					$tpl->assign($tpl_vars);
					$body = $tpl->fetch('user_ratings/notify_user_low_average.tpl');
					$to = $user->email;
					$subject = $msgs[502285];
					geoEmail::sendMail($to, $subject, $body,0,0,0,'text/html');
				}
				if($notifyAdmin) {
					//notify the admin of the user's low rating
					$subject = 'Low User Rating Average Notification [Admin]';
					$tpl->assign($tpl_vars);
					$body = $tpl->fetch('user_ratings/notify_admin_low_average.tpl');
					$to = $db->get_site_setting('site_email');
					geoEmail::sendMail($to, $subject, $body,0,0,0,'text/html');
				}
				$notified = 1;
			} elseif($notified == 1 && $average >= $lowRatingThreshold) {
				//have previously notified this user, but now his average is above the threshold.
				//reset the notification flag so that he can be notified again if it drops later
				$notified = 0;
			}
		} else {
			$notified = 0;
		}

		$result = $db->Execute("REPLACE INTO ".geoTables::user_ratings_averages." (`about`,`average`,`notified`) VALUES (?,?,?)",array($about,$average,$notified));
		return $result ? $average : false;
	}
	
	/**
	 * Get an individual rating about one user, from another, if it exists
	 * @param int $about a User ID#
	 * @param int $from a User ID#
	 * @return int 
	 */
	public static function getIndividualRating($about, $from)
	{
		if(!$about || !$from) {
			//bad request -- cannot proceed
			return false;
		}
		$db = DataAccess::getInstance();
		
		$rating = $db->GetOne("SELECT `rating` FROM ".geoTables::user_ratings." WHERE `about` = ? AND `from` = ?", array($about, $from));
		
		if(!$rating) {
			return self::NOT_RATED;
		}
		
		return $rating;
	}
	
	/**
	 * Used to save one user's rating of another to the db
	 * @param int $about a User ID#
	 * @param int $from a User ID#
	 * @param int $rating the user rating, 1-5 inclusive
	 * @return bool success
	 */
	public static function setIndividualRating($about, $from, $rating)
	{
		$rating = (int)$rating;
		if($rating < 1 || $rating > 5) {
			trigger_error('ERROR USER_RATING: bad rating given');
			return false;
		}
		$db = DataAccess::getInstance();
		$result = $db->Execute("REPLACE INTO ".geoTables::user_ratings." (`about`,`from`,`rating`) VALUES (?,?,?)", array($about,$from,$rating));
		
		//for testing only: instantly update average (will eventually do this from a cron)
		self::computeAverageRating($about);
		
		return (bool)$result;
	}
}
