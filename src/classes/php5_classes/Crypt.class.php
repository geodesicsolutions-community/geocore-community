<?php
//Crypt.class.php
/**
 * Holds the geoCrypt class, which is used to encrypt data such as CC numbers.
 * 
 * @package System
 * @since Version 4.0.0
 */
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## 
##    7.1.0-22-g58a63a7
## 
##################################

/**
 * Class to use for encrypting information that needs to be able to be retrieved later.
 * 
 * The goal of this encryption, is to make it sufficiently difficult to try to figure
 * out what the key is (and thus be able to decrypt the encrypted string), when an untrusted
 * party gains access to the encrypted data.
 * 
 * This will detect what type of encryption the server supports, and use that encryption
 * to encrypt the data.  This means that if the server changes, and the new server does
 * not have the same capabilities as the previous one, it may make the data not recoverable
 * (unless transfered to a server that does have sufficient capabilites to decrypt it)
 * 
 * @package System
 * @since Version 4.0.0
 * @todo Must implement additional algorithm types (refered to as cypher type in
 *  code) using alternate encryption techniques. with the "strongest" being the
 *  default if it is capable on the server.
 */
class geoCrypt {
	/**
	 * The "basic" type of cypher, this is the "fallback" if no other encryption
	 * algorithms are possible with the server.
	 * @var string
	 */
	const CYPHER_BASIC = 'basic';
	
	/**
	 * Set this to true to enable debug messages for troubleshooting or creating new cypher
	 * types.  Do NOT leave this at 1 as it can make data that is supposed to be encrypted
	 * be stored in a log file in plain-text, and that is probably not a very good thing.
	 * In fact, it would be a very bad thing on production sites, so don't do it
	 * on a production site.
	 * 
	 * When adding new debug messages:  Be sure to use ONLY the keyword "CRYPT", an example
	 * call would look like:
	 * if (self::DEBUG) trigger_error('DEBUG CRYPT: Debug message!');
	 * 
	 * @var bool
	 */
	const DEBUG = false;
	
	/**
	 * Server capabilities are gauged, to determine the best cypher method (the
	 * encryption algorithm).  The method used
	 * will be one of the constants prepended with CYPHER_ - it is static so that it does not
	 * have to be re-detected multiple times in one page load.
	 *
	 * @var string
	 */
	protected static $_defaultCypher;
	
	/**
	 * The site key, used as salt for each key generated
	 *
	 * @var string
	 */
	protected static $_siteKey;
	
	/**
	 * The data to be used to generate a key from
	 *
	 * @var array
	 */
	protected $_keyData;
	
	/**
	 * The real key used, this should not be stored anywhere, but rather re-generated
	 * by taking the same data used when encrypting to re-generate it
	 *
	 * @var string
	 */
	protected $_keyString;
	
	/**
	 * The cypher algorithm used (or to be used) when encrypting/decrypting
	 *
	 * @var string
	 */
	protected $_cypherUsed;
	
	/**
	 * The plain text
	 * @var string
	 */
	protected $_plainText;
	
	/**
	 * The encrypted text
	 * @var string
	 */
	protected $_encryptedText;
	
	/**
	 * Array of allowed cypher types
	 *
	 * @var array
	 */
	protected static $_validCyphers = array (
		self::CYPHER_BASIC,
	);
	
	/**
	 * Sets up defaults used for encrypting/decrypting information
	 *
	 */
	public function __construct()
	{
		if (!isset(self::$_defaultCypher)) {
			//detect what cypher to use by default
			
			//TODO: Add more cypher types
			self::$_defaultCypher = self::CYPHER_BASIC;
		}
		
		if (!isset(self::$_siteKey)) {
			$db = DataAccess::getInstance();
			//NOTE:  license_verify: Nothing to do with license or verify,
			//this is just to make it slightly harder for someone to
			//figure out the key, by naming it something that doesn't
			//sound like a key.
			$key = base64_decode($db->get_site_setting('license_verify'));
			if ($key === false || strlen($key) < 100){
				//need to generate a random key, between 100 and 200 chars long
				$key = self::generateRandomKey(100,180);
				$db->set_site_setting('license_verify',base64_encode($key));
			}
			self::$_siteKey = $key;
		}
	}
	
	/**
	 * Sets the key's data.  This data will be used to generate a text key, in such a way
	 * so that the same data will produce the same key.  Then you can store the data instead of
	 * storing the plain-text key, so it will not be obvious that the data was used (in part)
	 * to generate the key.
	 *
	 * @param array $key_data Suggested use:  Pass an array of user or transaction data (including things like
	 *  ip used at time of transaction, or time transaction took place), then store the same data
	 *  so that it can be retrieved later.  Then you pass the same data at a later time to be able to decrypt
	 *  the data again.
	 */
	public function setKeyData($key_data)
	{
		if (!is_array($key_data) && strlen(trim($key_data)) > 0){
			$key_data = array ('data' => $key_data);
		} else if (!is_array($key_data)) {
			throw new Exception('Error: Expected input to be array.');
		}
		$this->_keyData = $key_data;
	}
	
	/**
	 * Gets the plain text.  If the encrypted text was previously set, it will automatically decrypt
	 * that using the key data (if set).
	 * 
	 * @return string
	 */
	public function getPlainText()
	{
		$this->_process();
		return $this->_plainText;
	}
	
	/**
	 * Set the plain text that will be encrypted.
	 *
	 * @param string $text
	 */
	public function setPlainText($text)
	{
		$this->_plainText = $text.""; //force it to be a string
	}
	
	/**
	 * get the encrypted text
	 * 
	 * @return string
	 */
	public function getEncryptedText()
	{
		$this->_process();
		return $this->_encryptedText;
	}
	
	/**
	 * Sets the encrypted text, so that it can be decrypted and returned by
	 * getPlainText
	 *
	 * @param string $text
	 */
	public function setEncryptedText($text)
	{
		$this->_encryptedText = $text . "";//force it to be a string
	}
	
	/**
	 * Gets the cypher used during the last encryption/decryption.
	 * If no encryption/decryption has been done yet, it returns the
	 * default used by the system when none is specified.
	 * 
	 * @return string
	 */
	public function getCypherUsed()
	{
		$this->_process();
		return $this->_cypherUsed;
	}
	
	/**
	 * Manually set the cypher to be used.  Typically you only set the
	 * cypher when decrypting.
	 * 
	 * @param string $cypher
	 */
	public function setCypherUsed($cypher = self::CYPHER_BASIC)
	{
		if (in_array($cypher,self::$_validCyphers)){
			$this->_cypherUsed = $cypher;
		}
	}
	
	/**
	 * Convenience function, encrypts the given string using the key provided, and
	 * returns the encrypted text.
	 * 
	 * NOT recommended if the cypher used to encrypt the data is needed to be known
	 *
	 * @param string $text
	 * @param mixed $key Either an array of strings to use to generate a key, or a string key
	 * @param string $cypher Cypher algorithm to use, if not specified the default for this server's capabilities
	 *  will be used.
	 * @return string
	 */
	public static function encrypt ($text, $key, $cypher = null)
	{
		$crypt = new geoCrypt;
		$crypt->setKeyData($key);
		$crypt->setPlainText($text);
		if (!is_null($cypher) && in_array($cypher, self::$_validCyphers)){
			$crypt->setCypherUsed($cypher);
		}
		return $crypt->getEncryptedText();
	}
	
	/**
	 * Convenience function, decrypts the given string using the key provided, and
	 * returns the decrypted text.
	 *
	 * @param string $text
	 * @param mixed $key Either an array of strings to use to generate a key, or a string key
	 * @param string $cypher Cypher algorithm to use, if not specified the default for this server's capabilities
	 *  will be used.
	 * @return string
	 */
	public static function decrypt ($text, $key, $cypher = null)
	{
		$crypt = new geoCrypt;
		$crypt->setKeyData($key);
		$crypt->setEncryptedText($text);
		if (!is_null($cypher) && in_array($cypher, self::$_validCyphers)){
			$crypt->setCypherUsed($cypher);
		}
		return $crypt->getPlainText();
	}
	
	/**
	 * Generates a "random" key using PHP's mt_rand function.
	 *
	 * @param int $min_key_length Minimum number of chars for the length of the key
	 * @param int $max_key_length Max number of chars for the length of the key
	 * @return string
	 */
	public static function generateRandomKey($min_key_length = 30, $max_key_length = 45){
		//generate random key
		$to = rand($min_key_length,$max_key_length); //num chars is random, between 30 and 45		
		// define possible characters
		$possible = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-.`~!@#$%^&*()?<>/\|+='; 
		$newKey = '';
		for ($i = 0; $i < $to; $i++){
			$newKey .= substr($possible, mt_rand(0, strlen($possible)-1), 1);
		}
		
		return $newKey;
	}
	
	/**
	 * Sets up any un-set stuff, and encyrpts or decrypts depending on which vars
	 * are set.  Only does stuf that needs to be done, so safe to call multiple times.
	 *
	 */
	protected function _process ()
	{
		//Generate key string, if not already generated
		if (!isset($this->_keyString) || strlen($this->_keyString) == 0){
			//generate key string from key data
			if (!is_array($this->_keyData)){
				$this->_keyData = array();
			}
			$this->_keyData['_siteKey'] = self::$_siteKey;
			$this->_generateKey();
		}
		
		//Set cypher used, if not already set
		if (!isset($this->_cypherUsed) || !in_array($this->_cypherUsed, self::$_validCyphers)){
			$this->_cypherUsed = self::$_defaultCypher;
		}
		
		//Encrypt or decrypt text, depending on which one is not already set
		if (!isset($this->_encryptedText) && isset($this->_plainText)) {
			//go from plain text to encrypted
			$this->_encrypt();
		} else if (isset($this->_encryptedText) && !isset($this->_plainText)) {
			//go from encrypted to plain text
			$this->_decrypt();
		}
	}
	/**
	 * Encrypts _plainText and stores the encrypted value in _encryptedText (to be retrieved by getEncryptedText())
	 * using the cypher method set in _cypherUsed
	 * 
	 * This function MUST be called after _process() is called, or it may act un-predictably.
	 *
	 */
	protected function _encrypt()
	{
		switch ($this->_cypherUsed) {
			case self::CYPHER_BASIC:
				//break ommited on purpose
			default:
				$string = $this->_plainText;
				$result = '';
				for($i=0; $i<strlen($string); $i++) {
					$char = substr($string, $i, 1);
					$keychar = substr($this->_keyString, ($i % strlen($this->_keyString))-1, 1);
					$char = chr(ord($char)+ord($keychar));
					$result.=$char;
				}
				$this->_encryptedText = base64_encode($result);
				break;
		}
	}
	
	/**
	 * Decrypts _encryptedText and stores the decrypted value in _plainText (to be retrieved by getPlainText())
	 * using the cypher method set in _cypherUsed
	 * 
	 * This function MUST be called after _process() is called, or it may act un-predictably.
	 *
	 */
	protected function _decrypt()
	{
		switch ($this->_cypherUsed) {
			case self::CYPHER_BASIC:
				//break ommited on purpose
				
			default:
				$result = '';
				$string = base64_decode($this->_encryptedText);
				for($i=0; $i<strlen($string); $i++) {
					$char = substr($string, $i, 1);
					$keychar = substr($this->_keyString, ($i % strlen($this->_keyString))-1, 1);
					$char = chr(ord($char)-ord($keychar));
					$result.=$char;
				}
				$this->_plainText = $result;
				break;
				
		}
	}
	
	/**
	 * Takes _keyData as set by setKeyData() and generates a string key from it, and stores that key
	 * in _keyString to be used by _encrypt() and _decrypt().  The same array of data will always
	 * generate the same key string.
	 *
	 */
	protected function _generateKey ()
	{
		$key = '';
		ksort($this->_keyData); //so that the order of the array doesn't matter in the key that is generated.
		foreach ($this->_keyData as $i => $val) {
			//"clean" the data some
			if (is_numeric($val)) {
				$val = (float)$val;
				//force it to be 4 decimal places every single time, and use thousands
				//seperator, just because we can and we need all numbers to be uniform
				//so they look the same even after getting sent to the database and coming back again.
				$val = number_format($val,4,'.',',');
			} else {
				$val = trim($val);
			}
			//NOTE: May need to do further "cleaning" to ensure value always stays
			//the same even after getting sent to and from the DB... if so do that
			//cleaning here, NOT outside of this class... we need 1 solution in 1
			//place, not 20 different solutions...
			
			$key = "$i:_:{$key}:_:$val";
		}
		//If debug enabled, show what the key is before hash, so we can troubleshoot
		//problems caused by the key changing when trying to decrypt.
		if (self::DEBUG) trigger_error('DEBUG CRYPT: Key used before hash='.$key);
		$this->_keyString = sha1($key);
	}
}