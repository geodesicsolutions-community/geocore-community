<?php
//Email.class.php
/**
 * Holds the geoEmail class.
 * 
 * @package System
 * @since Version 4.0.4
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
## ##    7.3beta5-46-gc123fc4
## 
##################################

/**
 * Class that sends out e-mails.
 * 
 * @package System
 * @since Version 4.0.0
 * @todo Flesh out this class, add ability to have e-mail queue that can be
 *  sent out every so often, etc.
 */
class geoEmail
{
	/**
	 * Internal queue of messages to be sent, this is either sent or saved to DB
	 * at page load end.
	 * @var array
	 */
	private $_queue = array();
	
	/**
	 * Singleton instance of geoEmail
	 * @var geoEmail
	 * @internal
	 */
	private static $_instance;
	
	//temporary, eventually this is set in admin
	const EMAIL_AT_ONCE = 50;
	//temporary, eventually this is set in admin.
	const FREQUENCY = 600; //60 * 10 - send EMAIL_AT_ONCE every 10 minutes
	
	/**
	 * Private instance to prevent creating new instance outside of getInstance
	 */
	private function __construct ()
	{
		
	}
	
	/**
	 * Gets an instance of the geoEmail class.
	 * @return geoEmail
	 */
	public static function getInstance ()
	{
		if (!isset(self::$_instance)) {
			$c = __class__;
			self::$_instance = new $c;
		}
		return self::$_instance;
	}
	
	/**
	 * Saves teh current e-mail queue to the DB if needed.
	 */
	public function saveQueue ()
	{
		$db = DataAccess::getInstance();
		/**
		 * email_id - int(11)
		 * to_array - varchar(255)
		 * subject - varchar(128)
		 * content - text
		 * from_array - varchar(255)
		 * replyto_array - varchar(255)
		 * content_type - varchar(64)
		 * status - enum(sent, not_sent, error)
		 * sent - int(11)
		 */
		$sql = $db->Prepare("INSERT INTO `geodesic_email_queue` SET 
			`to_array` = ?,
			`subject` = ?,
			`content` = ?,
			`from_array` = ?,
			`replyto_array` = ?,
			`content_type` = ?,
			`status` = 'not_sent',
			`sent` = 0");
		foreach ($this->_queue as $email) {
			$to = '';
			if ($email['to']) {
				$to = (is_array($email['to']))? $email['to']: array($email['to']);
				$to = geoString::toDB(serialize($to));
			}
			$from = '';
			if ($email['from']) {
				$from = (is_array($email['from']))? $email['from']: array($email['from']);
				$from = geoString::toDB(serialize($from));
			}
			$replyto = '';
			if ($email['replyto']) {
				$replyto = (is_array($email['replyto']))? $email['replyto']: array($email['replyto']);
				$replyto = geoString::toDB(serialize($replyto));
			}
			$type = 'text/plain';
			if (isset($email['type']) && strlen($email['type'])) {
				$type = $email['type'];
			}
			$query_data = array (
				$to, geoString::toDB($email['subject']), geoString::toDB($email['content']), $from, $replyto, $type
			);
			$db->Execute ($sql, $query_data);
		}
		//queued everything up so clear it
		$this->_queue = array();
	}
	
	/**
	 * This is a TEMPORARY function.  Using this will add an e-mail to a queue
	 * to be sent at a later time.  Eventually ALL e-mails will be sent this way,
	 * when this happens, this function will go away, it is recommended to use 
	 * geoMail::sendMail() function instead.
	 * 
	 * @param string|array $to
	 * @param string $subject
	 * @param string $content
	 * @param string $from
	 * @param string|array $replyTo
	 * @param string $charset
	 * @param string $type
	 */
	public function addQueue ($to,$subject,$content,$from=0,$replyTo=0,$charset=0,$type=0)
	{
		$email = array ( 'to' => $to,
						'subject' => $subject,
						'content' => $content,
						'from' => $from,
						'replyto' => $replyTo,
						'charset' => $charset,
						'type' => $type
						);
		$this->_queue[] = $email;
	}
	
	/**
	 * Generic mailer function.
	 * Just an easier way to send e-mails, this uses {@link geoAddon::triggerUpdate()}
	 * using the core event email.
	 * 
	 * For now, all this does is call $addon->triggerUpdate('email',array('to'=>$to, 
	 * 'subject' => $subject, 'content' => $content, 'from' => $from, 'replyto' => $replyTo,
	 * 'charset' => $charset, 'type' => $type));
	 * 
	 * But EVENTUALLY (hopefully soon) this will do all the work of sending the e-mail.
	 * 
	 * This is the new prefered way to send an e-mail.
	 *
	 * @param string $to
	 * @param string $subject
	 * @param string $content
	 * @param (optional)string $from pass zero for site default
	 * @param (optional)string $replyTo pass zero for site default
	 * @param (optional)string $charset pass zero for site default
	 * @param (optional)string $type HTML, plain text, etc, use zero for site default
	 */
	public static function sendMail ($to,$subject,$content,$from=0,$replyTo=0,$charset=0,$type=0) {
		//let the addon do any input checking.
		//TODO: Move default e-mail functionality into this class, and
		// make the addon call to over-write the built-in functionality.
		$email = array ( 'to' => $to,
						'subject' => $subject,
						'content' => $content,
						'from' => $from,
						'replyto' => $replyTo,
						'charset' => $charset,
						'type' => $type
						);
		
		//Allow the contents to be filtered
		$email = geoAddon::triggerDisplay('filter_email', $email, geoAddon::FILTER);
		
		geoAddon::triggerUpdate('email',$email);
		return true;
	}
	
	/**
	 * Is used by cron task, this should usually not be called directly.
	 * 
	 * @param geoCron $cron The geoCron class.
	 * @return bool True on success, false on failure.
	 */
	public function cron ($cron)
	{
		$db = DataAccess::getInstance();
		
		//first figure out how many e-mails have been sent in the last X amount of time
		$cutoff = geoUtil::time() - self::FREQUENCY;
		$count = $db->GetRow("SELECT COUNT(`email_id`) as count FROM `geodesic_email_queue` WHERE `sent` > $cutoff");
		$count = (isset($count['count']))? $count['count']: 0;
		if ($count >= self::EMAIL_AT_ONCE) {
			$cron->log('Already over limit of '.self::EMAIL_AT_ONCE.' every '.self::FREQUENCY.' seconds.  current count: '.$count,__file__.' - '.__line__);
			return true;
		}
		$all = $db->Execute("SELECT * FROM `geodesic_email_queue` WHERE `status`='not_sent'");
		if (!$all) {
			$cron->log('No e-mails to send, nothing to do.',__file__.' - '.__line__);
			return true;
		}
		
		$cron->log('Found '.$all->RecordCount().' e-mails that still need to be sent, but only sending up to '.self::EMAIL_AT_ONCE.' every '.self::FREQUENCY.' seconds.  current count: '.$count,__file__.' - '.__line__);
		foreach ($all as $row) {
			if ($count >= self::EMAIL_AT_ONCE) {
				break;
			}
			$email_id = $row['email_id'];
			$to = unserialize(geoString::fromDB($row['to_array']));
			$subject = geoString::fromDB($row['subject']);
			$content = geoString::fromDB($row['content']);
			$from = unserialize(geoString::fromDB($row['from_array']));
			if (!$from) $from = 0;
			$replyto = unserialize(geoString::fromDB($row['replyto_array']));
			if (!$replyto) $replyto = 0;
			$type = $row['content_type'];
			self::sendMail($to, $subject, $content, $from, $replyto, 0, $type);
			$db->Execute("UPDATE `geodesic_email_queue` 
			SET `sent`=".geoUtil::time()." , `status`='sent' WHERE `email_id` = $email_id LIMIT 1");
			$count++;
		}
		$cron->log('Finished sending all e-mails.',__file__.' - '.__line__);
		return true;
	}
}