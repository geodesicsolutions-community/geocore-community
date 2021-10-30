<?php
//API.class.php
/**
 * Holds the geoAPI class.
 * 
 * @package System
 * @since Version 4.0.0
 */



/**
 * The main system class for receiving and handling remote API calls, this acts
 * as a translation layer between the communication with the "outside" and each
 * API call.
 * 
 * Note that as of Version 6.0.0, this loads the API but leaves it up to a
 * "transport" to get params and send data back to the client.
 * 
 * @package System
 * @since Version 4.0.0
 */
class geoAPI
{
	/**
	 * Database object, can be used by api tasks by using $this->db
	 *
	 * @var DataAccess
	 */
	public $db;
	
	/**
	 * Session object, can be used by api tasks by using $this->session
	 *
	 * @var geoSession
	 */
	public $session;
	
	/**
	 * geoPC object, can be used by api tasks by using $this->product_configuration
	 *
	 * @var geoPC
	 */
	public $product_configuration;
	
	/**
	 * geoAddon object, can be used by cron tasks by using $this->addon
	 *
	 * @var geoAddon
	 */
	public $addon;
	
	/**
	 * An array of API calls mapped to the absolute file location used for that call.
	 * @var array
	 */
	private $_callbacks;
	
	/**
	 * The transport class
	 * @since Version 6.0.0
	 */
	private $_transports;
	
	/**
	 * The transport actually used for this page load
	 * @var iApiTransport
	 */
	private $_transport;
	
	/**
	 * Constructor, initializes all the class vars and loads the tasks.
	 *
	 * @return geoAPI
	 */
	public function __construct()
	{
		$this->db = DataAccess::getInstance();
		$this->session = geoSession::getInstance();
		$this->product_configuration = geoPC::getInstance();
		$this->addon = geoAddon::getInstance();
		$this->load();
	}
	
	/**
	 * Sort of the boot-strapper for the API, this serves the API call, including
	 * output back to the client.
	 * 
	 * @param string $force_transport If specified, will use force the use of
	 *   the specified transport (or die trying).
	 */
	public function serve ($force_transport = '')
	{
		//loads everything...
		$transport = 'xmlrpc';
		if (strlen($force_transport)) {
			$transport = $force_transport;
		} else if (isset($_GET['transport'])) {
			$transport = trim($_GET['transport']);
		}
		
		$this->setTransport($transport);
		$params = $this->_transport->getParams();
		$call = $this->_transport->getCall();
		
		$this->call($call, $params);
	}
	
	/**
	 * Set which transport to use for this page load.  Recommended not to change
	 * mid-stream or it could cause weird problems.  Attempting to use invalid
	 * transport results in script dieing.
	 * 
	 * @param string $transport
	 * @since Version 6.0.0
	 */
	public function setTransport ($transport)
	{
		$transport = strtolower($transport);
		$this->loadTransports();
		if (!isset($this->_transports[$transport])) {
			die ('Invalid transport.');
		}
		$this->_transport = new $this->_transports[$transport];
	}
	
	/**
	 * Loads all the transport layer implementations possible.  Mostly used internally,
	 * but could be used by some custom mod to force loading transports from specific
	 * folder.
	 * 
	 * @param string $dir
	 * @since Version 6.0.0
	 */
	public function loadTransports ($dir = '')
	{
		if (strlen($dir) == 0){
			if (is_array($this->_transports) && count($this->_transports) > 0){
				//already loaded
				return ;
			}
			//loading from base dir
			$dir = API_DIR.'_transports/';
				
			//load addon api's
			$api_addons = $this->addon->getApiAddons();
				
			foreach ($api_addons as $name){
				//go through each api and add it if it is valid.
				if (is_dir(ADDON_DIR.$name.'/api/_transports')) {
					$this->loadTransports(ADDON_DIR.$name.'/api/_transports/');
				}
			}
		}
		$dirname = $dir;
		//echo 'Adding dir: '.$dirname.'<br />';
		$files = array_diff(scandir($dir), array('..','.'));
		foreach ($files as $filename) {
			if (strpos($filename,'.php') !== false && file_exists($dirname.$filename)) {
				//echo '<strong>Adding: '.$methodname.'.'.str_replace('.php','',$filename).'</strong><br />';
				$this->addTransport(str_replace('.php','',$filename),$dirname.$filename);
			}
		}
	}
	
	/**
	 * Loads all the remote API tasks found in the given directory.
	 *
	 * @param string $dir The absolute directory to be looking in.
	 * @param string $methodname The remote API call name, usually just leave this
	 *  at default to load all.
	 */
	public function load ($dir = '', $methodname = 'core')
	{
		if (strlen($dir) == 0){
			if (is_array($this->_callbacks) && count($this->_callbacks) > 0){
				//already loaded
				return ;
			}
			//load transports
			$this->loadTransports();
			//loading from base dir
			$dir = API_DIR;
			
			//load addon api's
			$api_addons = $this->addon->getApiAddons();
			
			foreach ($api_addons as $name){
				//go through each api and add it if it is valid.
				if (is_dir(ADDON_DIR.$name.'/api')) {
					$this->load(ADDON_DIR.$name.'/api/','addon.'.$name);
				}
			}
		}
		if (strlen($methodname) == 0){
			$methodname = 'core'; //method should start with the type.
		}
		$dirname = $dir;
		//echo 'Adding dir: '.$dirname.'<br />';
		$files = array_diff(scandir($dir), array('..','.','_samples','_transports'));
		foreach ($files as $filename) {
			if (in_array(substr($filename, 0, 1), array('_','.'))) {
				//starts with _ or . so skip it
				continue;
			}
			if (is_dir($dirname.$filename)) {
				//directory, so recursively load
				//echo 'loading sub-dir: '.$dirname.$filename.'<br />';
				
				$this->load($dirname.$filename.'/',$methodname.'.'.$filename);
			} else if (strpos($filename,'.php') !== false && file_exists($dirname.$filename)) {
				//echo '<strong>Adding: '.$methodname.'.'.str_replace('.php','',$filename).'</strong><br />';
				$this->addCallback($methodname.'.'.str_replace('.php','',$filename),$dirname.$filename);
			}
		}
	}
	
	/**
	 * Adds a callback (AKA a remote API call) that could be called.
	 * 
	 * Normally this is not called directly.
	 * 
	 * @param string $methodname
	 * @param string $filename
	 */
	public function addCallback($methodname, $filename)
	{
		if (file_exists($filename)){
			$this->_callbacks[strtolower($methodname)] = $filename;
		}
	}
	
	/**
	 * Gets the array of valid callbacks.
	 *
	 * @return array
	 */
	public function getCallBacks()
	{
		$this->load();
		return $this->_callbacks;
	}
	
	/**
	 * Whether or not the specified callback (aka methodname aka api call) exists
	 * @param string $callback
	 * @return bool
	 * @since Version 6.0.0
	 */
	public function hasCallback ($callback)
	{
		$this->load();
		$callback = strtolower($callback);
		return (isset($this->_callbacks[$callback]));
	}
	
	/**
	 * Add a new transport to the possible transports that can be used
	 * 
	 * @param string $transportName
	 * @param string $filename
	 * @since Version 6.0.0
	 */
	public function addTransport ($transportName, $filename)
	{
		$transportName = strtolower($transportName);
		if (isset($this->_transports[$transportName])) {
			//already added, don't proceed... if any name collisions, the one that
			//loads first is one that will be used.
			return;
		}
		if (file_exists($filename)) {
			require_once($filename);
			$c = $transportName.'Transport';
			if (class_exists($c, false) && in_array('iApiTransport', class_implements($c, false))) {
				//make sure the class exists, and implements the required interface iApiTransport
				$this->_transports[$transportName] = $c;
			}
		}
	}
	
	/**
	 * Gets an array of transports currently possible.
	 * @return array
	 * @since Version 6.0.0
	 */
	public function getTransports ()
	{
		$this->loadTransports();
		return $this->_transports;
	}
	
	/**
	 * Whether or not the specified transport exists and is usable or not.
	 * @param string $transport
	 * @return bool
	 * @since Version 6.0.0
	 */
	public function hasTransport ($transport)
	{
		$this->loadTransports();
		$transport = strtolower($transport);
		return (isset($this->_transports[$transport]));
	}
	
	/**
	 * What transport type is being used for this page load, or empty string
	 * if it hasn't figured that out yet.
	 * @return string
	 * @since Version 6.0.0
	 */
	public function currentTransportType ()
	{
		if (!$this->_transport) {
			return '';
		}
		return $this->_transport->getType();
	}
	
	/**
	 * Runs the specified remote api task, if that task is valid.
	 * 
	 * Usually just used internally.
	 *
	 * @param string $methodname
	 * @param mixed $args
	 */
	public function call ($methodname, $args)
	{
		if (!defined('IN_GEO_API')){
			define('IN_GEO_API',1);
		}
		$methodname = strtolower($methodname);
		if (count($args) == 1 && is_array($args[0])){
			$args = $args[0];
		}
		//make sure the security key matches
		if (isset($args['api_key'])){
			//Key passed using associative array
			$passed_key = $args['api_key'];
			unset($args['api_key']);
		} else {
			//not passed using associative array, see if it is the first item on the array
			$passed_key = array_shift($args);
		}
		
		$site_key = $this->getKeyFor();
		$call_key = $this->getKeyFor($methodname);
		if ($site_key != $passed_key && $call_key != $passed_key){
			//does not match main site key, or key specifically for this
			//api call, output error and use 5 second delay to make brute-force attempts take longer
			return $this->failure('Doh! Server error. API key [\'site_key\'] not valid, or not sent.', -32601, 5);
		}
		
		//only return the error of the requested method does not exist if the API site key matches up first.
		if (!$this->hasCallback($methodname)) {
			return $this->failure('Doh! Server error. Requested method '.$methodname.' does not exist.', -32601);
		}
		
		
		$method = $this->_callbacks[$methodname];
		// Perform the callback and send the response
		if (is_array($args) && count($args) == 1 && isset($args[0])) {
			// If only one paramater just send that instead of the whole array
			$args = $args[0];
		}
		
		//file should return result.  result can be any php value
		ob_start();
		$result = require ($method);
		//do nothing with the output.  only consider the return value.
		ob_end_clean();
		$this->success($result);
	}
	
	/**
	 * Can be used by API calls to throw a failure.  Can add an artifitial delay
	 * to the response for "security" if desired (for instance, if wrong password
	 * or similar, something that might attempt to be brute-forced) by specifying
	 * delay in seconds.
	 * 
	 * Note that this causes the error to be sent to the client, and depending
	 * on the transport, may exit.  If you want to guarantee continuation, instead
	 * store the error as part of the args passed back.
	 * 
	 * @param string $message Message for error
	 * @param int $errno Random number to identify specific API problem internally
	 * @param int $delay Delay in seconds, if any (help slow down brute-force)
	 * @since Version 6.0.0
	 */
	public function failure ($message='API Request Failed', $errno = 1000, $delay=0)
	{
		$this->_transport->outputError($errno, $message, $delay);
		if ($this->_transport->exitAfterOutput()) {
			require GEO_BASE_DIR . 'app_bottom.php';
			exit;
		}
		//return false to indicate failure, if on transport that does not exit right away
		return false;
	}
	
	/**
	 * Sends the data using the active transport and depending on the transport,
	 * may exit the script.
	 * 
	 * @param mixed $data The data to return to the client.
	 * @since Version 6.0.0
	 */
	public function success ($data)
	{
		$this->_transport->outputSuccess($data);
		if ($this->_transport->exitAfterOutput()) {
			require GEO_BASE_DIR . 'app_bottom.php';
			exit;
		}
		//return true to indicate success if on a transport that does not exit right away
		return true;
	}
	
	/**
	 * Gets the site key for the given api call.
	 * 
	 * If no call is given, returns key that works with all api calls.
	 *
	 * Note: API system does needed checks, you should not need to do any checks
	 * within any api call to make sure the key is valid.
	 *
	 * @param string $apiCall
	 * @return string The key for the given remote API call.
	 */
	public function getKeyFor ($apiCall='')
	{
		$site_key = $this->db->get_site_setting('api_key');
		if (strlen($site_key)==0){
			//no key yet!
			$site_key = $this->resetKey();
		}
		
		//site key is combination of stored site key, and api call, hashed
		$site_key = sha1($site_key.':_:'.strtolower($apiCall));
		
		return $site_key;
	}
	
	/**
	 * Resets the remote API security key.
	 *
	 * @param string $newKey if blank, a random key is generated.
	 */
	public function resetKey($newKey = ''){
		if (strlen(trim($newKey)) == 0){
			//generate random key
			$to = rand(30,45); //num chars is random, between 30 and 45		
			// define possible characters
			$possible = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-."; 
			for ($i = 0; $i < $to; $i++){
				$newKey .= substr($possible, mt_rand(0, strlen($possible)-1), 1);
			}
		}
		$this->db->set_site_setting('api_key',$newKey);
		return $newKey;
	}
	
	/**
	 * Verifies that the given user token is valid.
	 *
	 * @param string $username
	 * @param string $token
	 * @return boolean
	 */
	public function checkUserToken ($username, $token)
	{
		if (strlen(trim($token)) != 40 || strlen(trim($username)) == 0){
			//token is incorrect
			return false;
		}
		//check the token
		$sql = 'SELECT `api_token` FROM `geodesic_logins` WHERE `username` = ? AND `id` != 1';
		$result = $this->db->Execute($sql, array($username));
		if (!$result || $result->RecordCount() != 1){
			return false;
		}
		$row = $result->FetchRow();
		if ($row['api_token'] === $token){
			return true;
		}
		if (strlen(trim($row['api_token'])) == 0){
			//no user token set, reset token
			$this->resetUserToken($username);
		}
		return false;
	}
	
	/**
	 * Resets a user's remote api token (or sets it for the first time)
	 *
	 * @param string $username
	 * @return Mixed The new api token if successful, or false on failure.
	 */
	public function resetUserToken ($username)
	{
		if (strlen(trim($username)) == 0) {
			//token is incorrect
			return false;
		}
		
		$sql = 'SELECT `id` FROM `geodesic_logins` WHERE `username` = ? AND `id` != 1';
		$result = $this->db->Execute($sql, array($username));
		if (!$result || $result->RecordCount() != 1){
			return false;
		}
		$row = $result->FetchRow();
		$id = $row['id'];
		$key = '';
		do {
			//generate random key
			$key = sha1($username.'abc1'.rand().'23 salt');
			
			$sql = 'SELECT `api_token` FROM `geodesic_logins` WHERE `api_token` = ?';
			$result = $this->db->Execute($sql, array($key));
		} while ($result && $result->RecordCount() > 0);
		
		$sql = 'UPDATE `geodesic_logins` SET `api_token` = ? WHERE `id` = ? LIMIT 1';
		$result = $this->db->Execute($sql, array($key, $id));
		if (!$result) {
			return false;
		}
		return $key;
	}
	
	/**
	 * Sort of an alias of {@see geoAPI::failure()}, kept around for backwards-compatibility
	 * on API calls
	 * 
	 * @param string $error_msg
	 * @param int $delay_time The amount of time before showing the error
	 *  message (for security reasons, can artificially increase the amount of
	 *  time it takes, to make it take a lot longer to attempt brute-force)
	 */
	public function return_error_with_delay ($error_msg, $delay_time = 5)
	{
		$this->failure($error_msg, 1000, $delay_time);
	}
	
	/**
	 * Utility function that returns the current time, adjusted 
	 * for the time shift as set in the admin.
	 *
	 * @return int
	 */
	public function time()
	{
		return geoUtil::time();
	}
}

/**
 * Interface for API Transports, a transport must implement this interface
 * for the system to use it.
 * 
 * @package System
 * @since Version 6.0.0
 */
interface iApiTransport
{
	/**
	 * Get array of parameters based on whatever the transport uses for passing
	 * parameters in.
	 * 
	 * @return array
	 */
	public function getParams ();
	
	/**
	 * Get the requested API call name, somthing like core.misc.echo
	 * 
	 * @return string
	 */
	public function getCall ();
	
	/**
	 * Output given data to the client.  Should be able to handle arrays, string,
	 * numbers, etc.  This is used to send the data returned by the API call back to
	 * the client, in other words it is called once and should result in the
	 * info being sent back to the client in full.
	 * 
	 * @param mixed $data
	 * @return bool Should return true once output is sent to client.
	 */
	public function outputSuccess ($data);
	
	/**
	 * Output an error to the client.  Responsible for adding a delay if $addDelay
	 * is not 0, and applicable for transport.  This is used by API call to return
	 * an error back to the client, this is the "final" output back to the client.
	 * 
	 * @param int $errno Just a number used to help quickly identify an error
	 * @param string $errmsg Error message to return back
	 * @param int $addDelay Delay time in seconds, used to slow down brute force attempts
	 * @return bool Should return false once output sent to client.
	 */
	public function outputError ($errno, $errmsg, $addDelay);
	
	/**
	 * The type of transport, should be the filename minus the .php and in all
	 * lowercase.  Note that the filename shoudl be all lowercase anyways.
	 * 
	 * @return string;
	 */
	public function getType ();
	
	/**
	 * Whether or not script should exit after output success/failure is called.
	 * This is here to allow "local" transports to prevent the rest of the script
	 * from stopping.
	 * 
	 * Any "remote" transport should return true.
	 * 
	 * @return bool
	 */
	public function exitAfterOutput ();
}