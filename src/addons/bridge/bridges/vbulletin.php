<?php
//addons/bridge/bridges/vbulletin.php
/**************************************************************************
Addon Created by Geodesic Solutions, LLC
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## ##    16.09.0-96-gf3bd8a1
## 
##################################

# Bridge Installation Type - VBulletin

require_once ADDON_DIR.'bridge/util.php';

class bridge_vbulletin extends addon_bridge_util {
	//What is shown in the admin for the bridge type
	var $name = 'vBulletin';


	var $settings = array(
		'config_path' => 'input',
		'license_key' => 'input'
	);
	var $setting_desc = array (
		'config_path' => array (
				'name' => 'Server path to config.php',
				'desc' => 'Absolute path to the vBulletin config.php file. Example would be <em>/absolute/path/to/vbulletin/config.php</em>.'
			),
		'license_key' => array (
				'name' => 'vBulletin License',
				'desc' => 'The license key you were given when you bought your vBulletin license.<br />The key can be found at the top of one of your vBulletin files.'
			)

	);
	var $db; //connection to bridge db
	var $user_info = array(); //user info for logging in and stuff
	var $old_user_data = array(); //used for updating user
	var $install_settings; //used to store the bridge's settings...
	var $vboptions = array();//vBulletin data
	var $config; //used as dummy to be able to use vbulletin's functions directly

	var $vbconfig = array();

	var $debug = false; //to prevent errors when old code outputs debug stuff...

	function bridge_vbulletin(){
		$this->setting_desc['config_path']['desc'] .=  '
			<br />Detected Path to this Installation\'s Root: <span class="color-primary-two">'.GEO_BASE_DIR.'</span>';
	}
	function getDescription(){
		$description = '
		<div class="page_note">This bridge allows for users to be shared <strong>locally</strong> between <em>Geodesic 3.1</em> and <strong><em>vBulletin 3.6.8</em></strong>.</div>

		<div class="page_note">The bridge may be compatible with other versions of vBulletin, but it has only been tested on the version(s) listed above.  If you have a previous
		version of vBulletin, we recommend you update to the latest stable release.</div>
		<div class="page_note"><strong>Caveats/Important Notes:</strong><br />
		<ul>
			<li>
				The vBulletin installation must reside on the <strong>same domain name</strong> for login and logout to work properly between installations.
			</li>
			<li>Since this is a <em>local bridge</em>, the vBulletin installation must be on the <strong>same server as the Geo installation.</strong>
			</li>
			<li>To ensure best compatibility, make sure vBulletin is updated to one of the versions listed above, as those are the versions we
				have tested with.
			</li>
			<li>Currently, this is a one-way bridge, meaning user creation and user detail changes made in the Geo installation will reflect in vBulletin, but not the other way around.
				As such, we recommend that you force users to register or edit user details in the Geo installation, by <strong>turning off registration, and ability to change the username, passwords, or e-mail address within vBulletin.</strong>
			</li>
			<li>There is currently no user import or export for this bridge.  That means that existing users in vBulletin will need to be manually created in the Geo
				installation, and visa-versa.
			</li>
			<li>When updating user information within the Geo installation, only the password and e-mail are updated in vBulletin.  The username is not changed, this must be changed
				manually within the vBulletin admin tool.
			</li>
		</ul></div>';
		return $description;
	}

	/**
	 * This function is called automatically during initialization of the bridge addon.  It should
	 * be used to store settings passed, to a local var or something.
	 *
	 * @param array $settings Associative array, like array (setting_name => setting_value)
	 */
	function setSettings($settings){
		//You can use any method to keep track of settings during this session,
		//below is an example of re-using the member variable "$settings" to store
		//the settings, but you can just as easily store them in a var named config or
		//whatever.
		if (!is_array($settings)){
			return false; //oops, settings are no good
		}
		foreach ($settings as $key => $value){
			//set each setting, checking to make sure its all good first..
			if (isset($this->settings[$key])){
				//valid setting, so set that setting.
				$this->settings[$key] = $value;
			}
		}
	}
	//Following functions are not implemented (yet):
	// importUsers()
	// exportUsers()
	// importUserCount()
	// exportUserCount()

	function getEncryptedPassword($password, $salt)
	{
		$this->vb_init();
		global $vbulletin, $stylevar, $vbphrase;

		$include = "/includes/init.php";
		if(!is_file($this->vbconfig['parent_dir'].$include)) {
			$this->log("Vbulletin bridge error:  config file not set, please check settings", true);
		}
		if(!require_once(DIR.$include)) {
			$this->log("Vbulletin bridge error: failed to include $include",true);
		}

		$include = "/includes/class_dm.php";
		if(!require_once(DIR.$include)) {
			$this->log("Vbulletin bridge error: failed to include $include",true);
		}

		$include = "/includes/class_dm_user.php";
		if(!require_once(DIR.$include)) {
			$this->log("Vbulletin bridge error: failed to include $include",true);
		}
		if (!isset($_GLOBALS['vbulletin'])){
			$GLOBALS['vbulletin'] =& $vbulletin;
		}

		$userdm = new vB_DataManager_User($vbulletin, ERRTYPE_ARRAY);
		if($userdm && is_object($userdm)) {
			$encrypted_password = $userdm->hash_password($password, $salt);
		} else {
			//couldn't get the data manager object from VB, so try to do it manually.
			$encrypted_password = md5(md5($this->user_info["password"]).$bbuserinfo->SALT);
		}

		return $encrypted_password;
	}

	/**
	 * Optional, function to log a user in.  Optional because logging in a user may not be
	 * possible in some circumstances.
	 *
	 * @param array $vars Associative array containing info about user logging in
	 */
	function session_login($vars) {
		if (!$this->connect()){
			return false;
		}
		$this->user_info = $vars;
		include ($this->settings['config_path']);
		$old_version = false;
		if (isset($config['Database']['tableprefix']))
		{
			//this changed in some version...started at least in 3.5.2
			$tableprefix = $config['Database']['tableprefix'];
			$old_version = true;
		}
		if (isset($config['Misc']['cookieprefix'])){
			$cookieprefix = $config['Misc']['cookieprefix'];
			$old_version = true;
		}

		define('VB_API',false);

		//$this->get_vBulletin_vboptions($tableprefix);
		$current_time = time();
		if ($this->get_vBulletin_vboptions($tableprefix))
		{
			$sql = "SELECT userid, usergroupid, membergroupids, username, password, salt FROM " . $tableprefix . "user
				WHERE username = '" . addslashes($this->vbulletin_htmlspecialchars_uni($this->user_info["username"])) . "'";
			$userdata_result = $this->db->Execute($sql);

			if (!$userdata_result)
			{
				$this->log( "ERROR - LOGGING INTO VBULLETIN INSTALLATION\nGETTING USERDATA USING THE USERNAME\n\n
					USING THE FOLLOWING USERNAME\n\n".$this->user_info["username"]."\n\nERROR RETURNED: ".$this->db->ErrorMsg(), true);
				$this->cleanUp();
				return false;
			} elseif ($userdata_result->RecordCount() == 1)	{
				//check password
				$bbuserinfo = $userdata_result->FetchNextObject();

				$encrypted_password = $this->getEncryptedPassword($this->user_info['password'], $bbuserinfo->SALT);
				$this->log( 'Checking password.  '.$bbuserinfo->SALT." is the salt\n"
				. "vbulletin password: ".$bbuserinfo->PASSWORD." - "
					.(($bbuserinfo->PASSWORD != $encrypted_password)?
						"did not match":"DID match").
					 " - entered password: ".$this->user_info["password"]." - ".$encrypted_password."\n");
				if ($bbuserinfo->PASSWORD != $encrypted_password)
				{
					//password does not match
					$this->log('Error: Passwords did not match when logging user in (User: '.$this->user_info['username'].'), in VBulletin, even though password matched with Geo software.

					This user cannot be "synced" as long as the password in vBulletin does not match the one in the Geo software.  Have the user change their Geo password to match vBulletin password.

					Make sure you have disabled the ability to change the password inside of vBulletin, to prevent this from happening in the future.

					', true);
					$this->cleanUp();
					return false;
				}
				//password matches
				//$this->log( "Password matched<Br>\n");
				$this->vbsetcookie('userid', $bbuserinfo->USERID ,1,$cookieprefix);
				//$cookie_password = md5($bbuserinfo->PASSWORD.$this->settings["license_key"]);//old way?
				$cookie_password = md5($this->user_info['password'].$this->settings['license_key']);
				$this->vbsetcookie('password', $cookie_password,1,$cookieprefix);
				if ($this->debug)
				{
					echo $cookie_password." is the password encrypted with vbulletion license key -  ".$this->settings["license_key"]."<br>\n";
				}

				if ($_ENV['REQUEST_URI'] OR $_SERVER['REQUEST_URI'])
				{
					$scriptpath = $_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : $_ENV['REQUEST_URI'];
				}
				else
				{
					if ($_ENV['PATH_INFO'] OR $_SERVER['PATH_INFO'])
					{
						$scriptpath = ($_SERVER['PATH_INFO'])? $_SERVER['PATH_INFO']: $_ENV['PATH_INFO'];
					}
					else if ($_ENV['REDIRECT_URL'] OR $_SERVER['REDIRECT_URL'])
					{
						$scriptpath = ($_SERVER['REDIRECT_URL'])? $_SERVER['REDIRECT_URL']: $_ENV['REDIRECT_URL'];
					}
					else
					{
						$scriptpath = ($_SERVER['PHP_SELF'])? $_SERVER['PHP_SELF'] : $_ENV['PHP_SELF'];
					}

					if ($_ENV['QUERY_STRING'] OR $_SERVER['QUERY_STRING'])
					{
						$scriptpath .= '?' . (($_SERVER['QUERY_STRING'])? $_SERVER['QUERY_STRING'] : $_ENV['QUERY_STRING']);
					}
				}

				if ($_SERVER['HTTP_CLIENT_IP'])
				{
					define('ALT_IP', $_SERVER['HTTP_CLIENT_IP']);
				}
				else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches))
				{
					// make sure we dont pick up an internal IP defined by RFC1918
					foreach ($matches[0] AS $ip)
					{
						if (!preg_match("#^(10|172\.16|192\.168)\.#", $ip))
						{
							define('ALT_IP', $ip);
							break;
						}
					}
				}
				else if ($_SERVER['HTTP_FROM'])
				{
					define('ALT_IP', $_SERVER['HTTP_FROM']);
				}
				else
				{
					define('ALT_IP', $_SERVER['REMOTE_ADDR']);
				}

				$scriptpath = preg_replace('/(s|sessionhash)=[a-z0-9]{32}?&?/', '', $scriptpath);

				$seed = (double) microtime() * 1000000;
				mt_srand($seed);
				$random_seed = mt_rand(1, 1000000);

				$length = 1; //default length for ip substr?

				//md5($_SERVER['HTTP_USER_AGENT'] . $this->fetch_substr_ip($registry->alt_ip))
				//implode('.', array_slice(explode('.', $ip), 0, 4 - $length));
				$session_idhash = md5($_SERVER['HTTP_USER_AGENT'] . implode('.', array_slice(explode('.', ALT_IP), 0, 4 - $length)));
				$session_host = substr($_SERVER['REMOTE_ADDR'], 0, 15);

				$session['sessionhash'] = md5($current_time . $scriptpath . $session_idhash . $session_host . $random_seed);
				$session['dbsessionhash'] = $session['sessionhash'];

				$sql = "DELETE FROM " . $tableprefix . "session WHERE sessionhash = '" . addslashes($session['dbsessionhash']) . "'";
				$delete_session_result = $this->db->Execute($sql);
				if (!$delete_session_result)
				{
					$this->log("DB ERROR - LOGGING INTO VBULLETIN INSTALLATION\n
						USING THE FOLLOWING USERNAME\n\n".$this->user_info["username"]."\nAND
						DELETING OLD SESSIONS\n\n".$this->db->ErrorMsg(), true);
					$this->cleanUp();
					return false;
				}
				$this->log('sessionhost = '.$session_host);
				$sql = "INSERT INTO " . $tableprefix . "session
						(sessionhash, userid, host, idhash, lastactivity, styleid, loggedin, bypass, useragent, location)
					VALUES
						('" . addslashes($session['sessionhash']) . "', " . intval($bbuserinfo->USERID) . ", '" . addslashes($session_host) . "', '" . addslashes($session_idhash) . "', " . $current_time . ", ".$this->vboptions["styleid"].", 1, 0, '" . addslashes($_SERVER["HTTP_USER_AGENT"]) . "', '/products/user_forum/vbulletin/')";
				$insert_session_result = $this->db->Execute($sql);
				if (!$insert_session_result)
				{
					$this->log("ERROR - LOGGING INTO VBULLETIN INSTALLATION\n
						USING THE FOLLOWING USERNAME\n\n".$this->user_info["username"]."\nAND
						INSERTING NEW SESSIONS\n\n".$this->db->ErrorMsg(), true);
				}
				$this->vbsetcookie('sessionhash', $session['sessionhash'], 0,$cookieprefix);
				//set the lastvisit and lastactivity
				$this->vbsetcookie('lastvisit',time(),0,$cookieprefix);
				$this->vbsetcookie('lastactivity','0',0,$cookieprefix);
			}
			else
			{
				// invalid username entered
				$this->log('Error: could not log user in, user not found in bridge installation, username: '.$this->user_info["username"], true);
				$this->cleanUp();
				return false;
			}
		}
		else
		{
			//can't get vboptions data
			//things went bad
			$this->log("DB ERROR - GETTING VBULLTIN INSTALLATION INFORMATION TO COMPLETE LOGIN\n
				".$this->db->ErrorMsg(), true);
			$this->cleanUp();
			return false;
		}
		$this->cleanUp();
		return true;
	}

	/**
	 * Optional, function to log a particular user out of the system.
	 *
	 * @param array $user_info
	 */
	function session_logout($user_info) {
		if (!$this->connect()){
			return true;
		}
		//do all vbulletin login procedures at once here
		// clear all cookies beginning with COOKIE_PREFIX
		include ($this->settings['config_path']);

		if (isset($config['Database']['tableprefix']))
		{
			//this changed in some version...started at least in 3.5.2
			$tableprefix = $config['Database']['tableprefix'];
		}

		if (isset($config['Misc']['cookieprefix'])){
			$cookieprefix = $config['Misc']['cookieprefix'];
		}

		if (isset($config['Misc']['cookieprefix']))
		{
			//this changed in some version...started at least in 3.5.2
			$cookieprefix  = $config['Misc']['cookieprefix'];
		}

		$cookieprefix = $this->getCookiePrefix($cookieprefix);

		$this->get_vBulletin_vboptions($tableprefix);
		$prefix_length = strlen($cookieprefix);
		foreach ($_COOKIE AS $key => $val)
		{
			//if ($this->debug) echo $key." is ".$val." of _COOKIE trying to remove<bR>\n";
			$index = strpos($key, $cookieprefix);
			if ($index == 0 AND $index !== false)
			{
				$key = substr($key, $prefix_length);
				if (trim($key) == '')
				{
					continue;
				}
				$this->vbsetcookie($key, '', 1,$cookieprefix);
			}
		}
		$cookie_name = $cookieprefix."userid";
		//if ($this->debug) echo $cookie_name." is cookie_name<BR>\n";
		$time = time();
		if (isset($_COOKIE[$cookie_name])){
			//cookie set, do not try to log out of vbulletin.
			$sql = "UPDATE " . $tableprefix . "user
				SET lastactivity = " . ($time - $this->vboptions['cookietimeout']) . ",
				lastvisit = " . $time . "
				WHERE userid = ".$_COOKIE[$cookie_name];
			$update_user_session_result = $this->db->Execute($sql);
			//if ($this->debug) echo $sql."<br>\n";
			if (!$update_user_session_result)
			{
				$this->log(__line__.'DB Error logging out, sql: '.$sql.'<br /> error message: '.$this->db->ErrorMsg(), true);
			}


			// make sure any other of this user's sessions are deleted (in case they ended up with more than one)
			$sql = "DELETE FROM " . $tableprefix . "session WHERE userid = ".$_COOKIE[$cookie_name];
			$delete_user_session_result = $this->db->Execute($sql);
			//if ($this->debug) echo $sql."<br>\n";
			if (!$delete_user_session_result)
			{
				$this->log(__line__.'DB Error logging out, sql: '.$sql.'<br /> error message: '.$this->db->ErrorMsg(), true);
			}


			$sql = "DELETE FROM " . $tableprefix . "session WHERE sessionhash = \"" . addslashes($_COOKIE["sessionhash"])."\"";
			$delete_sessionhash_result = $this->db->Execute($sql);
			//if ($this->debug) echo $sql."<br>\n";
			if (!$delete_sessionhash_result)
			{
				$this->log(__line__.'DB Error logging out, sql: '.$sql.'<br /> error message: '.$this->db->ErrorMsg(), true);
			}
		}
		if ($_ENV['REQUEST_URI'] OR $_SERVER['REQUEST_URI'])
		{
			$scriptpath = $_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : $_ENV['REQUEST_URI'];
		}
		else
		{
			if ($_ENV['PATH_INFO'] OR $_SERVER['PATH_INFO'])
			{
				$scriptpath = $_SERVER['PATH_INFO'] ? $_SERVER['PATH_INFO']: $_ENV['PATH_INFO'];
			}
			else if ($_ENV['REDIRECT_URL'] OR $_SERVER['REDIRECT_URL'])
			{
				$scriptpath = $_SERVER['REDIRECT_URL'] ? $_SERVER['REDIRECT_URL']: $_ENV['REDIRECT_URL'];
			}
			else
			{
				$scriptpath = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_ENV['PHP_SELF'];
			}

			if ($_ENV['QUERY_STRING'] OR $_SERVER['QUERY_STRING'])
			{
				$scriptpath .= '?' . ($_SERVER['QUERY_STRING'] ? $_SERVER['QUERY_STRING'] : $_ENV['QUERY_STRING']);
			}
		}

		if ($_SERVER['HTTP_CLIENT_IP'])
		{
			define('ALT_IP', $_SERVER['HTTP_CLIENT_IP']);
		}
		else if ($_SERVER['HTTP_X_FORWARDED_FOR'] AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches))
		{
			// make sure we dont pick up an internal IP defined by RFC1918
			foreach ($matches[0] AS $ip)
			{
				if (!preg_match("#^(10|172\.16|192\.168)\.#", $ip))
				{
					define('ALT_IP', $ip);
					break;
				}
			}
		}
		else if ($_SERVER['HTTP_FROM'])
		{
			define('ALT_IP', $_SERVER['HTTP_FROM']);
		}
		else
		{
			define('ALT_IP', $_SERVER['REMOTE_ADDR']);
		}

		$scriptpath = preg_replace('/(s|sessionhash)=[a-z0-9]{32}?&?/', '', $scriptpath);

		$seed = (double) microtime() * 1000000;
		mt_srand($seed);
		$random_seed = mt_rand(1, 1000000);
		$session_idhash = md5($_SERVER['HTTP_USER_AGENT'] . ALT_IP );
		$session_host = substr($_SERVER['REMOTE_ADDR'], 0, 15);
		$session['sessionhash'] = md5($time . $scriptpath . $session_idhash . $session_host . $random_seed);

		$session['dbsessionhash'] = $session['sessionhash'];
		$sql = "INSERT INTO ".$tableprefix."session
				(sessionhash, userid, host, idhash, lastactivity, styleid, useragent)
			VALUES
				('" . addslashes($session['sessionhash']) . "', 0, '" . addslashes($session['host']) . "', '" . addslashes($session['idhash']) . "', " . time() . ", 0, '" . addslashes($_SERVER["HTTP_USER_AGENT"]) . "')";
		$delete_session_result = $this->db->Execute($sql);
		//if ($this->debug) echo $sql."<br>\n";
		if (!$delete_session_result)
		{
			$this->log(__line__.'DB Error logging out, sql: '.$sql.'<br />error message: '.$this->db->ErrorMsg(), true);
		}
		//$this->vbsetcookie('userid',0,2,$cookieprefix);
		//$this->vbsetcookie('password', 0,2,$cookieprefix);
		//$this->vbsetcookie('sessionhash', $session['sessionhash'], 2,$cookieprefix);

		$this->cleanUp();
		return true;
	}

	/**
	 * Optional, function that updates a user's info, stuff like changing password, changing e-mail, etc.
	 *
	 * @param array $user_info
	 */
	function user_edit($user_info){
		if (!$this->connect()){
			return false;
		}

		$this->user_info=$user_info;
		//vBulletin
		$time = time();
		// check to see if email address has changed
		include ($this->settings['config_path']);

		if (isset($config['Database']['tableprefix']))
		{
			//this changed in some version...started at least in 3.5.2
			$tableprefix = $config['Database']['tableprefix'];
		}
		if (isset($config['Misc']['cookieprefix'])){
			$cookieprefix = $config['Misc']['cookieprefix'];
		}
		if (isset($config['Misc']['cookieprefix']))
		{
			//this changed in some version...started at least in 3.5.2
			$cookieprefix  = $config['Misc']['cookieprefix'];
		}

		$this->get_vBulletin_vboptions($tableprefix);
		if ($this->get_user_id())
		{
			$sql = "SELECT password, salt,email FROM " . $tableprefix . "user
				WHERE userid = " . $this->user_info['user_id'];
			$userdata_result = $this->db->Execute($sql);
			//if ($this->debug) echo $sql."<br>\n";
			if (!$userdata_result)
			{
				$this->log(__line__.'DB Error editing user data on bridge installation, you will need to update this data within your vbulletin installation manually:
						EMAIL: '.$this->user_info["email"].'
							USERNAME: '.$this->user_info["username"].'
							PASSWORD: '.$this->user_info["password"].'
							USERID: '.$this->user_info['user_id'].'
							Error message: '.$this->db->ErrorMsg(), true);
				$this->cleanUp();
				return false;
			}
			elseif ($userdata_result->RecordCount() == 1)
			{
				$bbuserinfo = $userdata_result->FetchNextObject();
				if ($this->user_info["email"] && $this->user_info["email"] != $bbuserinfo->EMAIL)
				{
					//check that email does not already exist
					$sql = "SELECT username,email FROM " . $tableprefix . "user WHERE email=\"" . addslashes($this->user_info["email"])."\"";
					$duplicate_email_result = $this->db->Execute($sql);
					//if ($this->debug) echo $sql." in api<br>\n";
					if (!$duplicate_email_result)
					{
						$this->log(__line__.'DB Error editing user data on bridge installation, you will need to update this data within your vbulletin installation manually:
						EMAIL: '.$this->user_info["email"].'
							USERNAME: '.$this->user_info["username"].'
							PASSWORD: '.$this->user_info["password"].'
							USERID: '.$this->user_info['user_id'].'
							Error message: '.$this->db->ErrorMsg(), true);
						$this->cleanUp();
						return false;
					}
					elseif ($duplicate_email_result->RecordCount() > 0)
					{
						$this->log(__line__.'DB Error editing user data on bridge installation, you will need to update this data within your vbulletin installation manually:
						EMAIL: '.$this->user_info["email"].'
							USERNAME: '.$this->user_info["username"].'
							PASSWORD: '.$this->user_info["password"].'
							USERID: '.$this->user_info['user_id'].'
							Error message: '.$this->db->ErrorMsg(), true);
						$this->cleanUp();
						return false;
					}
					else
					{
						//email does not currently exist within the vbulletin installation

						$sql = "update ".$tableprefix."user set email = \"".addslashes($this->user_info["email"])."\"
							where userid = ".$this->user_info['user_id'];
						//if ($this->debug) echo $sql." <bR>\n";
						$update_email_result = $this->db->Execute($sql);
						if(!$update_email_result)
						{
							$this->log(__line__.'DB Error editing user data on bridge installation, you will need to update this data within your vbulletin installation manually:
						EMAIL: '.$this->user_info["email"].'
							USERNAME: '.$this->user_info["username"].'
							PASSWORD: '.$this->user_info["password"].'
							USERID: '.$this->user_info['user_id'].'
							Error message: '.$this->db->ErrorMsg(), true);
							$this->cleanUp();
							return false;
						}
					}
				}
				//check to see if password needs to be changed..if there is a current one passed the password is supposed
				//to be changed
				if (strlen(trim($this->user_info["password"])) > 0)
				{
					//get salt for password
					$salt = '';

					for ($i = 0; $i < 3; $i++)
					{
						$salt .= chr(rand(32, 126));
					}
					$hashedpassword = md5(md5($this->user_info["password"]) . $salt);

					$this->log('CREATING NEW PASSWORD HASH. Password is '.$this->user_info['password'].' which has '.strlen($this->user_info['password']).' characters. Salt is '.$salt.'. hash is '.$hashedpassword);

					$sql = "update " . $tableprefix . "user set
						password = \"".$hashedpassword."\",
						salt = \"".$salt."\"
						where userid = ".$this->user_info['user_id'];
					$update_password_result = $this->db->Execute($sql);
					//if ($this->debug) echo $sql." <bR>\n";
					if(!$update_password_result)
					{
						$this->log(__line__.'DB Error editing user data on bridge installation, you will need to update this data within your vbulletin installation manually:
						EMAIL: '.$this->user_info["email"].'
							USERNAME: '.$this->user_info["username"].'
							PASSWORD: '.$this->user_info["password"].'
							USERID: '.$this->user_info['user_id'].'
							Error message: '.$this->db->ErrorMsg(), true);
						$this->cleanUp();
						return false;
					}

				}
				$this->cleanUp();
				return true;
			}
			else
			{
				//no userdata could be gotten using the vbulletin userid cookie
				$this->log(__line__.'DB Error editing user data on bridge installation, you will need to update this data within your vbulletin installation manually:
						EMAIL: '.$this->user_info["email"].'
							USERNAME: '.$this->user_info["username"].'
							PASSWORD: '.$this->user_info["password"].'
							USERID: '.$this->user_info['user_id'].'
							Error message: '.$this->db->ErrorMsg().'
							Number Results: '.$userdata_result->RecordCount(), true);
				$this->cleanUp();
				return false;
			}
		}
		else
		{
			//get_user_id returned false
			$this->log(__line__.'DB Error editing user data on bridge installation (get user ID failed), you will need to update this data within your vbulletin installation manually:
						EMAIL: '.$this->user_info["email"].'
							USERNAME: '.$this->user_info["username"].'
							PASSWORD: '.$this->user_info["password"].'
							USERID: '.$this->user_info['user_id'].'
							Error message: '.$this->db->ErrorMsg(), true);
			$this->cleanUp();
			return false;
		}

		$this->cleanUp();
		return true;
	}

	function exportUserCount(){
		$db =& DataAccess::getInstance();
		//count records
		$local_sql = "SELECT COUNT(id) FROM geodesic_userdata WHERE id !='1'";
		$local_sql = $db->getrow($local_sql);
		if(!$local_sql)
		{
			return 'Unknown error';
		}
		$Count_users = $local_sql['COUNT(id)'];
		return $Count_users;
	}

	function get_bridge_usernames_array()
	{
		if(!defined('vb_is_init'))
		{
				$this->vb_init();
		}
		If(!$this->connect())
		{
			$this->log('can\'t connect to bridge database');
		}
		$sql = "SELECT username from ".$this->vbconfig['db_prefix']."user WHERE username !='admin'";
		$users = $this->db->getall($sql);
		$this->cleanUp();
		return $users;
	}


	function bridge_user_exists($username)
	{
		if(!defined('vb_is_init'))
		{
				$this->vb_init();
		}

		if(!$this->connect())
		{
			$this->log('can\'t connect to bridge database');
		}

		$sql = "SELECT userid from ".$this->vbconfig['db_prefix']."user WHERE username ='$username'";

		$user = $this->db->getrow($sql);
		$this->cleanUp();

		if(!empty($user))
		{
			return true;
		}
		else
		{
			return false;
		}

	}

	/**
	 * Optional, function to export users from the local install, to the bridge install.
	 *
	 * @return boolean True if successful, false otherwise.
	 */
	function exportUsers(){
		global $userdm;

		$bridge_users = $this->get_bridge_usernames_array();
		//get all users in the given range, return array of user data.
		$admin = $db = 1;
		include GEO_BASE_DIR . 'get_common_vars.php';

		$local_sql = "SELECT ud.email, log.username, log.password
					FROM geodesic_userdata as ud
					JOIN geodesic_logins log ON ud.id = log.id
					WHERE ud.id !=1 AND log.status = 1";
		$users_data = $db->getall($local_sql);

		if(!empty($users_data))
		{
			$has_register = 0;
			$reg_failed = true;
			$this->ignore_critical_logs = true; //ignore errors
			foreach ($users_data as $user_data)
			{
				if($this->bridge_user_exists($user_data['username']))
					{
						$failed++;
					}
					else
					{
						if($this->user_register($user_data))
						{
							$has_register++;
						}
						else
						{
							$failed++;
						}
					}
			}
			$this->ignore_critical_logs = false;//turn off ignore errors
			if($failed)
			{
				$plural = ($failed==1)? 'user was': 'users were';
				$failedstr = "<br />\n$failed $plural not exported, probably because they already exist in {$this->name}.";
			}
			if($has_register)
			{
				$plural = ($has_register==1)? 'user was': 'users were';
				$admin->userNotice("$has_register $plural exported to ".$this->name.". $failedstr");
			} else {
				$admin->userNotice("None of the users were exported. $failedstr");
			}
		}
		$this->cleanUp();
		return true;
	}



	function vb_init()
	{
		if(!defined('vb_is_init'))
		{
			define('vb_is_init',1);

			if(!include ($this->settings['config_path']))
			{
				$this->log('can not find vbulleting config file, see vbulleting bridge settings',true);
			}


			$this->vbconfig['db_name'] = $config['Database']['dbname'];
			$this->vbconfig['db_prefix'] = $config['Database']['tableprefix'];
			$this->vbconfig['server'] = $config['MasterServer']['servername'];
			$this->vbconfig['username'] = $config['MasterServer']['username'];
			$this->vbconfig['password'] = $config['MasterServer']['password'];
			$this->vbconfig['pconnection'] = $config['MasterServer']['usepconnect'];
			$this->vbconfig['cookie_prefix'] = $config['Misc']['cookieprefix'];

			$config_file_path = $this->settings['config_path'];
			$config_path = dirname($config_file_path);

			$this->vbconfig['parent_dir'] = dirname($config_path);

 		}
		if (!defined('INIT_VB')){
			define('INIT_VB',1);
			define('DIR',$this->vbconfig['parent_dir']);
			define('CWD',$this->vbconfig['parent_dir']);
			define('VB_AREA','USER_MANAGEMENT');
			define('VB_API', false); //TODO: this is new...does VB have an api now that we might ought to be using?
		}
	}


	function connect(){
		//make sure we are not already connected...
		if ($this->isConnected()){
			return true;
		}
		//make a connection..
		$this->db = &ADONewConnection('mysql');

		if(!defined('vb_is_init'))
		{
				$this->vb_init();
		}


		$host = $this->vbconfig['server'];
		$user = $this->vbconfig['username'];
		$pass = $this->vbconfig['password'];
		$db_name = $this->vbconfig['db_name'];


		$this->db->Connect($host,$user,$pass,$db_name);
		if (!$this->db->isConnected()){
			$this->log("Error connecting to bridge database, check settings for this bridge.
			DB error returned: ".$this->db->ErrorMsg(), true);//log it
			return false;
		}
/*		if ($this->settings['db_strict']){
			if (!$this->db->Execute('SET SESSION sql_mode=\'\'')){
				$this->log('Error when attempting to turn off strict mode, check your bridge settings.  Debug info: '.$this->db->ErrorMsg(), true);
				die('cannot  set sql_mode');
				return false;
			}
		}*/
		return true;
	}

	function isConnected(){
		if (isset($this->db) && is_object($this->db)){
			return $this->db->isConnected();
		}
		return false;
	}



	var $vb;

	/**
	 * Optional, function to register a new user in the system.
	 *
	 * @param unknown_type $vars
	 * @return unknown
	 */
	function user_register($user_info){
	$this->log('top of register function: '.print_r($user_info,1));
		global $vbulletin, $stylevar, $vbphrase;
		//echo 'vb: '.$vbulletin.'<br />';
		$this->user_info = $user_info;
		if (!defined('INIT_VB')){
			if (!defined('SKIP_SESSIONCREATE') && defined('IN_ADMIN')){
				//this function is also used to sync users in the admin. If we're doing that, we don't want to log in a user after creating it.
				define('SKIP_SESSIONCREATE',1);
				define('NOCOOKIES',2);
			}
			$this->vb_init();
		}
		if (!defined('THIS_SCRIPT')) {
			define('THIS_SCRIPT', 'register');

			$phrasegroups = array('timezone', 'user', 'register', 'cprofilefield');

			//for some reason, vB 4.0.2 (and possibly others) defines SALT_LENGTH as 30 at the top of class_dm_user.php
			//this must be a bug, because the database field that holds the salt is a CHAR(3)
			//since it's a constant, if we define it here, VB's code will not overwrite the value
			define('SALT_LENGTH',3);

			//apparently if global.php is called, function.php throws an error we use init.php instead
			//require_once(DIR.'/global.php');



			if(!is_file($this->settings['config_path']))
			{
				$this->log('can not find vbulleting config file, see vbulleting bridge settings',true);
				return false;
			}

			$include = "/includes/init.php";
			if(!is_file($this->vbconfig['parent_dir'].$include))
			{
				$this->log("Vbulletin bridge error:  config file not set, please check settings", true);
			 return false;
			}
			if(!require_once(DIR.$include))
			{
				$this->log("Vbulletin bridge error: failed to include $include",true);
			}


			$include = "/includes/class_dm.php";
			if(!require_once(DIR.$include))
			{
				$this->log("Vbulletin bridge error: failed to include $include",true);
			}

			$include = "/includes/class_dm_user.php";
			if(!require_once(DIR.$include))
			{
				$this->log("Vbulletin bridge error: failed to include $include",true);
			}

			if (!isset($_GLOBALS['vbulletin'])){
				$GLOBALS['vbulletin'] =& $vbulletin;
			}
		}
		$userdm = new vB_DataManager_User($vbulletin, ERRTYPE_ARRAY);

		//echo !is_object($userdm)? "NOT AN OBJECT <BR />":"";

		$basic_data = array(
		'username',
		'email',
		'password'
		);

		foreach($basic_data as $transfer)
		{
			$userdm->set($transfer, $user_info[$transfer]);

		}

		//echo "info: <pre>".print_r($$transfer ,1)."</pre>";
		$userdm->set('usergroupid', 2);
		$userdm->set('ipaddress', '127.0.0.1');
		$userdm->set('timezoneoffset', 'timezoneoffset');
		$userdm->set_bitfield('options', 'adminemail', '0');
		$userdm->set_bitfield('options', 'showemail', '0');

		$dst_setting = 0;
		switch ($dst_setting)
		{
			case 0:
			case 1:
				$userdm->set_bitfield('options', 'dstonoff', $dst_setting);
				break;
			case 2:
				$userdm->set_bitfield('options', 'dstauto', 1);
				break;
		}

		#If there are errors (eMail not set, eMail banned, Username taken, etc.) you can check for errors using
		if (count($userdm->errors) > 0) {

			foreach($userdm->errors as $count => $error){
				//print "ERROR{$count}:{$error}\n<br />"; //We expect errors for users already exist.
				//just return false if errors
				$this->log('Error registering a new user, username '.$user_info['username'].', the user may need to be added manually.  Error given by vBulletin: '.$error,1);
				return false;
			}
		} else {
			//everything is set OK. now have vB save the data
			$newuserid = $userdm->save();

			//user will be automatically logged into Geo at this point -- do the same for vB
			$this->session_login(array('username' => $user_info['username'], 'password' => $user_info['password']));

			if($newuserid) {
				return $newuserid;
			}
		}
	}

	/**
	 * Function used in admin, associative array of settings are passed in and the function should
	 * test those settings, and return true or false depending on whether they are legit or not.
	 *
	 * @param array $settings
	 * @return boolean
	 */
	function test_settings($settings){
		$this->cleanUp();
		$admin = 1;
		include GEO_BASE_DIR . 'get_common_vars.php';
		//should return true if ok, false otherwise...  useing userError or whatever..

		//check to make sure config file exists
		if (!file_exists($this->settings['config_path'])){
			$admin->userError('The VBulletin config.php file could not be found!  Check your bridge settings and adjust the setting <strong>Server path to config.php</strong>, it is currently set to: '.$this->settings['config_path']);
			return false;
		}

		include $this->settings['config_path'];
		if (!isset($dbusername) && !isset($config['MasterServer']['username'])){
			//checks for settings that should be set in the vBulletin configuration.
			//(Only does minimal checks, does not check for all settings needed, this is just
			// to make sure it looks like they included the correct file)
			$admin->userError('The VBulletin config.php file does not seem to be correct, make sure that it is set to the location for <strong>vBulletin</strong> config.php, usually found inside of a folder named <em>include</em>.  Check your bridge settings and adjust the setting <strong>Server path to config.php</strong>, it is currently set to: '.$this->settings['config_path']);
			return false;
		}

		$admin->userSuccess('vBulletin config.php file found at the path specified, and seems to be configured correctly.');
		//TODO: Add tests for the config file's path, and the license key

		if (!$this->connect()){
			$settings = implode(', ',$this->vbconfig);
			$admin->userError("Could not connect to database, check the db settings ({$settings}) in your ".$this->name." installation (config.php)); <br />".$this->db->ErrorMsg());

			$this->db->Close();
			$this->cleanUp();
			return false;
		}
		$admin->userSuccess('Successful Connection to Bridge\'s database.');


		$result = true;
		$this->db->Close();
		$this->cleanUp();
		return $result;
	}

	//Utility functions
	function fetch_alt_ip()
	{
		$alt_ip = $_SERVER['REMOTE_ADDR'];

		if (isset($_SERVER['HTTP_CLIENT_IP']))
		{
			$alt_ip = $_SERVER['HTTP_CLIENT_IP'];
		}
		else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches))
		{
			// make sure we dont pick up an internal IP defined by RFC1918
			foreach ($matches[0] AS $ip)
			{
				if (!preg_match("#^(10|172\.16|192\.168)\.#", $ip))
				{
					$alt_ip = $ip;
					break;
				}
			}
		}
		else if (isset($_SERVER['HTTP_FROM']))
		{
			$alt_ip = $_SERVER['HTTP_FROM'];
		}

		return $alt_ip;
	}
	function get_vBulletin_vboptions($tableprefix=0)
	{
		if (!$this->connect()){
			return false;
		}

		//get vboptions array
		$sql = "SELECT varname,value FROM " . $tableprefix . "setting";
		$get_vboptions_result = $this->db->Execute($sql);
		if ($this->debug) echo $sql."<bR>\n";
		if (($get_vboptions_result) && ($get_vboptions_result->RecordCount()))
		{
			while ($setting = $get_vboptions_result->FetchRow())
			{
				$this->vboptions[$setting["varname"]] = $setting["value"];
				//echo $setting["varname"]." is ".$setting["value"]."<br>\n";
			}
			return true;
		}
		else
		{
			if ($this->debug) echo "error getting vBulletin vboptions<bR>\n";
			return false;
		}

		//$sql = "SELECT title, data FROM " . $tableprefix . "datastore WHERE title IN ('options')";
		//$this->vboptions_result = $this->db->Execute($sql);
		//if (!$this->vboptions_result)
		//{
		//	return false;
		//}
		//elseif ($this->vboptions_result->RecordCount() == 1)
		//{
		//	$storeitem = $this->vboptions_result->FetchNextObject;
		//	$this->vboptions = unserialize($storeitem->DATA);
		//	return true;
		//}
		//else
		//	return false;
	}
	function vbulletin_bitwise($value, $bitfield)
	{
		// Do not change this to return true/false!
		return $this->iif(intval($value) & $bitfield, 1, 0);
	} //end of function vbulletin_bitwise

	function vbulletin_convert_array_to_bits(&$arry, $_FIELDNAMES, $unset = 0)
	{
		$bits = 0;
		foreach($_FIELDNAMES AS $fieldname => $bitvalue)
		{
			if ($arry["$fieldname"] == 1)
			{
				$bits += $bitvalue;
			}
			if ($unset)
			{
				unset($arry["$fieldname"]);
			}
		}
		return $bits;
	}

	function vbulletin_htmlspecialchars_uni($text)
	{
		// this is a version of htmlspecialchars that still allows unicode to function correctly
		$text = preg_replace('/&(?!#[0-9]+;)/si', '&amp;', $text); // translates all non-unicode entities

		return str_replace(array('<', '>', '"'), array('&lt;', '&gt;', '&quot;'), $text);
	}


	function getCookiePrefix($fallback='')
	{
		if(!defined('COOKIE_PREFIX')) {
			//first, try to make vB set the cookie prefix for us (using fetch_config())
			//that way, it should hopefully be forward-compatible
			if (file_exists(dirname($this->settings['config_path']).'/class_core.php')){
				include_once(dirname($this->settings['config_path']).'/class_core.php');
				if(class_exists('vB_Registry')) {
					define('CWD',dirname($this->settings['config_path']).'/..'); //used in fetch_config
					$this->log('found registry class. CWD is: '.CWD);
					$vbReg = new vB_Registry();
					$vbReg->fetch_config();
				}
			}

			if(!defined('COOKIE_PREFIX') && strlen($fallback)) {
				//that didn't work -- try to set it manually
				define('COOKIE_PREFIX',$fallback);
			}
		}
		return COOKIE_PREFIX;
	}

	function vbsetcookie($name, $value = '', $permanent = 1,$cookieprefix)
	{
		$this->getCookiePrefix($cookieprefix);
		$this->log(__line__.' setting cookie, name: '.$name.' value= '.$value.' cookieprefix: '.COOKIE_PREFIX);



		if (file_exists(dirname($this->settings['config_path']).'/functions.php')){
			include_once(dirname($this->settings['config_path']).'/functions.php');
			if (function_exists('vbsetcookie')){

				//make sure dummy class is set up for vb functions to use
				global $vbulletin;
				if (!is_object($vbulletin)){
					$vbulletin = new vbDummyClass();
					include $this->settings['config_path'];
					$vbulletin->config = $config;
					$vbulletin->options = $this->vboptions;
				}
				//let vbulletin do the work, less chance of
				//something going wrong on future versions.  Or is that more of a chance...
				return vbsetcookie($name, $value, $permanent, false, false);
			}
		}
		//attempt to do it ourselves...
		if ($permanent == 1)
		{
			$expire = time() + 60 * 60 * 24 * 365;
		}
		elseif ($permanent == 2)
		{
			//remove the cookie
			$expire = time() - 86400;
		}
		else
		{
			$expire = 0;
		}

		/*
		if ($_SERVER['SERVER_PORT'] == '443')
		{
			// we're using SSL
			$secure = 1;
		}
		else
		{
			$secure = 0;
		}
		*/

		$secure = 0;

		$name = $cookieprefix . $name;

		$filename = 'N/A';
		$linenum = 0;

		if (strlen(trim($this->vboptions['cookiedomain'])) == 0)
		{
			$this->vboptions['cookiedomain'] = $_SERVER["HTTP_HOST"];
			//TODO: Make the cookie domain used the same as vbulletin uses
			//the following does not work if domain only has 2 parts, like domain.com.
			/*
			if ($this->vboptions['cookiedomain'] != $_SERVER["SERVER_ADDR"])
			{
				$dotpos = strpos($this->vboptions['cookiedomain'], '.');
				$this->vboptions['cookiedomain'] = substr($this->vboptions['cookiedomain'], $dotpos + 1);
			}
*/
			$this->log(__line__.'SETTING Cookie domain to: '.$this->vboptions['cookiedomain']);
		} else {
			$this->log(__line__.'Cookie domain already set, it is: '.$this->vboptions['cookiedomain']);
		}

		// consider showing an error message if there not sent using above variables?
		if (substr($this->vboptions['cookiepath'], -1, 1) != '/')
			$this->vboptions['cookiepath'] == "/";
		$alldirs = '';
		if ($value == '' AND strlen($this->vboptions['cookiepath']) > 1 AND strpos($this->vboptions['cookiepath'], '/') !== false)
		{
			// this will attempt to unset the cookie at each directory up the path.
			// ie, cookiepath = /test/vb3/. These will be unset: /, /test, /test/, /test/vb3, /test/vb3/
			// This should hopefully prevent cookie conflicts when the cookie path is changed.
			$dirarray = explode('/', preg_replace('#/+$#', '', $this->vboptions['cookiepath']));
			$alldirs = '';
			foreach ($dirarray AS $thisdir)
			{
				$alldirs .= "$thisdir";
				if (!empty($thisdir))
				{ // try unsetting without the / at the end
					setcookie($name, $value, $expire, $alldirs, $this->vboptions['cookiedomain'], $secure);
				}
				$alldirs .= "/";
				setcookie($name, $value, $expire, $alldirs, $this->vboptions['cookiedomain'], $secure);
				$this->log('Debug info: Setting vBulletin session cookies.  '.$name." is the name<BR>\n$value is the value<Br>\n $expire is expire<bR>\n
					$alldirs is alldirs<Br>\n
					{$this->vboptions['cookiedomain']} is the cookiedomain<Br>\n");
				if ($this->debug)
				{
					echo $name." is the name<BR>\n";
					echo $value." is the value<Br>\n";
					echo $expire." is expire<bR>\n";
					echo $alldirs." is alldirs<Br>\n";
					echo $this->vboptions['cookiedomain']." is the cookiedomain<Br>\n";
					echo $secure." is secure<BR>\n";
				}
			}
		}
		else
		{
			if (strlen(trim($this->installation_info["cookie_path"])) > 0)
				$cookie_path = $this->installation_info["cookie_path"];
			else
				$cookie_path = "/";
			setcookie($name, $value, $expire, $cookie_path, $this->vboptions['cookiedomain'],$secure);
			$this->log('Debug info: Setting vBulletin session cookies.  '.$name." is the name<BR>\n$value is the value<Br>\n $expire is expire<bR>\n
					$alldirs is alldirs<Br>\n
					{$this->vboptions['cookiedomain']} is the cookiedomain<Br>\n");
			if ($this->debug)
			{
				echo $name." is the name 2<BR>\n";
				echo $value." is the value 2<Br>\n";
				echo $expire." is expire 2<bR>\n";
				echo $alldirs." is alldirs 2<Br>\n";
				echo $this->vboptions['cookiepath']." is the cookiepath 2<Br>\n";
				echo $secure." is secure 2<BR>\n";
			}
		}
		return true;
	}

	function iif($expression, $returntrue, $returnfalse = '')
	{
		return ($expression ? $returntrue : $returnfalse);
	}//end of function iif

	function get_user_id()
	{
		//use login info from active installation to get id from latent installation
		if (!$this->connect()){
			return false;
		}
		include ($this->settings["config_path"]);

		if (isset($config['Database']['tableprefix']))
		{
			//this changed in some version...started at least in 3.5.2
			$tableprefix = $config['Database']['tableprefix'];
		}
		if (isset($config['Misc']['cookieprefix'])){
			$cookieprefix = $config['Misc']['cookieprefix'];
		}
		if (strlen(trim($this->user_info["username"])) > 0)
		{
			$sql = "select userid from " . $tableprefix . "user where username = ?";
			$get_user_id_result = $this->db->Execute($sql, array($this->user_info["username"]));
			if (!$get_user_id_result)
			{
				$this->log('DB Error when attempting to retrieve User Id, debug info: SQL Query: '.$sql.' Error Msg: '.$this->db->ErrorMsg(), true);
				return false;
			}
			elseif ($get_user_id_result->RecordCount() == 1)
			{
				$current_userid = $get_user_id_result->FetchRow();
				$this->user_info['user_id'] = $current_userid['userid'];

				return true;
			}
			else
			{
				$this->log('Error: No user found matching the username '.$this->user_info['username'].' when attempting to retrieve the user id.', true);
				return false;
			}
		}
		else
		{
			$this->log('Error:  Username not known, so retrieving user id failed.', true);
			return false;
		}

	}


	//Bridge connection functions...
	/**
	 * clean up the bridge connection and the main db connection.
	 */
	function cleanUp(){
		//make sure database connection is reset, to prevent weird stuff from happening...
		$db = true;
		include (GEO_BASE_DIR.'get_common_vars.php');
		$db->Close();

		//also close this connection
		if ($this->isConnected()){
			$this->db->Close();
		}
	}

}




class vbDummyClass {
	var $config;
	var $options;
}