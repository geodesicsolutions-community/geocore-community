<?php
/**
 	CJAX FRAMEWORK @verion@
	ajax made easy with cjax
	
	-- DO NOT REMOVE THIS -- 
	-- AUTHOR COPYRIGHT MUST REMAIN INTACT --
	CJAX FRAMEWORK
	Written by: Carlos Galindo
	Website: @WEBSITE@
	Email: cjxxi@msn.com
	Date: 9/21/2007
	Last Updated:  05/22/2008
 */
	

/**
 *  @package CoreEvents
 */

/**
 * Load external classes
 *
 * @param string $c
 */
class CoreEvents {
	/**
	 * Check weather or not to call the shutdown function
	 *
	 * @var boolean $_is_shutdown_called
	 */
	private static $_is_shutdown_called;
	
	/**
	 * store cache procedure
	 *
	 * @var string $cache
	 */
	private static $cache = '';
	
	/**
	 * specified weather to use the cache system or normal mode
	 *
	 * @var boolean $use_cache
	 */
	static $use_cache;
	
	/**
	 * Event which is used for elements to create calls to the server
	 * 
	 *
	 * @var string $JSevent = "onClick";
	 */
	private $JSevent;
	
	/**
	 * Set the text to show when the page is loading
	 * this replaces the "loading.."
	 *
	 * @var string $text
	 */
	public $text;
	/**
	 * Set the image that shows up when the page is loading
	 * this replaces teh default image
	 *
	 * @var unknown_type
	 */
	public $image;
	/*
	 * This must be set to true before making a CJAX call,
	 * only if the element you are interacting with is an anchor
	 * or an image
	 * 
	 */
	public $link;
	
	/*
	 * The the CJAX console on debug mode
	 */
	public $debug;
	
	/**
	 * Set the default directory when images loading images reside
	 *
	 * @var string $image_dir
	 */
	public $image_dir;
	
	/**
	 * Get the current version of CJAX FRAMEWORK you are using
	 *
	 * @var string
	 */
	public $version;
	
	/**
	 * Define that you are using an external extension
	 * 
	 *
	 * @var string
	 */
	public $extension;
	
	/**
	 * When using th plugin system, you will need to specify what the pluing base url is
	 * 
	 */
	private $extension_dir;
	
	/**
	 * Tells whether CJAX output has initciated or not, to void duplications
	 *
	 * @var boolean $is_init
	 */
	private $is_init;
	
	/**
	 * Sets the default way of making AJAX calls, it can be either get or post
	 */
	private $method ='get';
	/**
	 * Stores the the waiting procedure for the next action
	 */
	private static $wait;
	
	/**
	 * Auto execute methods for extensions
	 *
	 * @param unknown_type $method
	 * @param unknown_type $args
	 */
	public function __call($method, $args) 
	{
	 	if(!$this->extension) $this->extension = 'plugins';
	 	if(!$this->extension_dir) $this->extension_dir = '__base__/extensions/';
 		extension::add($method,$this->extension_dir,$this->extension);
 		$params = null;
	 	if( count($args) > 0 ) {
	 		$i = 0;
		 	foreach( $args as $arg ){
		 		$i++;
		 		$params[] = "<param$i>{$this->encode($arg)}</param$i>";
		 		$param[] = "<param>{$this->encode($arg)}</param>";
		 	}
		 	$params = "<params>".implode($params)."<array>".implode($param)."</array></params>";
	 	}
		$this->xml("<do>$method</do><extension>$this->extension</extension><ctype>extension_system</ctype><settings><method>$method</method>{$params}<base>$this->extension_dir</base></settings>");
	 }
	 
	/*
	 * sets up the default loading image
	 */
	function __construct()
	{
		$this->image = "cjax/core/images/loading.gif";
		$this->JSevent();
		//extension::init();
	}
	
	/**
	 * Encode special data to void conflicts with javascript
	 *
	 * @param string $data
	 * @return encoded string
	 */
	function encode($data)
	{
		// escape quotes and backslashes, newlines, etc.
		return strtr($data, array('\\'=>'\\\\',"'"=>"\\'",'"'=>'\\"',"\r"=>'\\r',"\n"=>'\\n','</'=>'<\/'));
		
		//After the above solution has been tested thoroughly, can remove below.
		$search = array( "\n"  ,"\r" ,"+"     ,"\"" ,"\\" ,"/"  ,"\t"  ,"''");
		$replace = array("~NL~","~NL~","~ADD~" ,"~Q~","~BS~","~FS~","~T~","~SQ~"); 
		$data = str_replace($search,$replace,$data); 
		$data = urlencode($data);
		$data = str_replace("+","~S~",$data); 
		return $data;
	}
		
	/**
	 * Not implemented, just for testing purpose
	 *
	 * @param string $xml
	 */
	function isXML($xml)
	{
		if(strpos($xml,'<') !==false && strpos($xml,'/>')!==false) { 
			return true;
		}
	}
	
	/**
	 * Not implemented, just for testing purpose
	 *
	 * @param string $xml
	 */
	function parseXml($xml)
	{
		$this->parseXml('<TAG1>test1</TAG1><TAG2>test2</TAG2><TAG3>test3</TAG3><TAG4>test4</TAG4>');
		//header("Content-type: application/x-javascript");
		
		//match all what is inside the xml tags.
		//$keywords = preg_split("/(<\/?[a-zA-Z0-9_]*>)/", $xml,-1, PREG_SPLIT_NO_EMPTY);		
		$keywords = preg_split("/(<\/?[a-zA-Z0-9_]*>)/", $xml,-1, PREG_SPLIT_NO_EMPTY);	
		
		$keywords2 =str_replace(array('<','>','/'),array('','|','|'),$xml);
		//$keywords2 =str_replace('||','|',$keywords2);
		$keywords2 = explode('|',$keywords2);
		
		//die('Keywords:<pre>'.print_r($keywords,1).'</pre><br /><pre>'.print_r($keywords2,1).'</pre>'.'XML:<BR />'.$xml);
	}
	
	function OS()
	{
		if(strpos($_SERVER['SERVER_SOFTWARE'],'WIN')!==false)
		{
			return 'WIN';
		}
		return true;
	}
	
	/**
	 * xml outputer, allows the interaction with xml
	 *
	 * @param xml $xml
	 * @return string
	 */
	function xml($xml)
	{
		if(!$xml) return false;
		$function = '';
		if(strpos($xml,'<do>')===false)
		{
			$trace = debug_backtrace();
			$function = "{$trace[1]['function']}";
			
			if($function=='wait')
			{
				self::$wait = $xml;
				return true;
			}
			$function = "<do>{$function}</do>";
			if(self::$wait)
			{
				$function = "{$function}".self::$wait;
				self::$wait = '';
			}
		}
		else
		{
			if(self::$wait)
			{
				$function = "{$function}".self::$wait;
				self::$wait = '';
			}
		}
		$tags = "<xml class='cjax'>\n{CJAX_CODE}</xml>";
		$out = "<cjax>{$function}{$xml}</cjax>";
		if(self::onTheFly())
		{
			self::cache($out);
		}
		else
		{
			print str_replace('{CJAX_CODE}',$out,$tags);
			
		}
	}

	/**
	 * Used for loading "fly" events
	 *
	 * @param string $add
	 */
	function cache($add)
	{
		if(!self::$_is_shutdown_called)
		{
			$bol = register_shutdown_function(array('CoreEvents','saveSCache'));
			self::$_is_shutdown_called = true;
			self::$use_cache = true;
			flush();
		}
		self::$cache .= $add;		
	}
	
	/**
	 * Saves the cache
	 *
	 * @return string
	 */
	function saveSCache()
	{
		/*$ouput = "
		CJAX.load_mode = 'fly';CJAX.source=\"".self::$cache."\";CJAX.replace_txt();";
		$out = str_replace("\n", "", $out);*/
		
		$out = "
		CJAX.source=\"".self::encode(self::$cache)."\";CJAX.replace_txt(true);";
		$out = str_replace(array("\n","\t"), "", $out);
		@session_start();
		GLOBAL $_SESSION;
		$_SESSION['cjax_cache'] = $out;
		@setcookie ('cjax_cache',$out,false);
		
		//if(self::file_write(trim($out)))
		return true;
	}
	
	/**
	 * write to a file in file system, used as an alrernative to for cache
	 *
	 * @param string $content
	 * @param string $flag
	 */
 	function file_write($content,$flag='w') {
 		$dir = str_replace('classes','js',__file__);
 		$dir = str_replace('core.class.php','cjax.js.php',$dir);
 		$filename = $dir;
        if (file_exists($filename)) {
            if (!is_writable($filename)) {
                if (!chmod($filename, 0666)) {
                     echo "CJAX: Error! file ($filename) is not writable, Not enough permission";
                     exit;
                };
            }
        }
        if (!$fp = @fopen($filename, $flag)) {
			echo "CJAX: Error! file ($filename) is not writable, Not enough permission";
 			exit;
        }
        if (fwrite($fp, $content) === FALSE) {
			echo "Cannot write to file ($filename)";
			exit;
        }
        if (!fclose($fp)) {
            echo "Cannot close file ($filename)";
            exit;
        }
    }
    
    function OS_SLASH()
    {
		$pos = strpos(PHP_OS, 'WIN');	    
		if ($pos !== false) {
    		return '\\';
    	}
    	return '/';
    }
	// error handler function
	/**
	 * Yet to implement
	 *
	 * @param string $errno
	 * @param string $errstr
	 * @param string $errfile
	 * @param string $errline
	 * @return string
	 */
	function CJAXErrorHandler($errno, $errstr, $errfile, $errline)
	{
	    switch ($errno) {
	    case E_USER_ERROR:
	        echo "<b>CJAX:</b> [$errno] $errstr<br />\n";
	        echo "  Fatal error on line $errline in file $errfile";
	        echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
	        echo "Aborting...<br />\n";
	        exit(1);
	        break;
	
	    case E_USER_WARNING:
	        echo "<b>My WARNING</b> [$errno] $errstr<br />\n";
	        break;
	
	    case E_USER_NOTICE:
	        echo "<b>My NOTICE</b> [$errno] $errstr<br />\n";
	        break;
	
	    default:
	        echo "Unknown error type: [$errno] $errstr<br />\n";
	        break;
	    }
	
	    /* Don't execute PHP internal error handler */
	    return true;
	}

	/**
	 * initciates the process of sending the javascript file to the application
	 *	
	 * @param optional boolean $echo
	 * @return string
	 */
	function init($echo = false)
	{
		$this->clearCache();
		if($this->is_init)
		{
			return true;
		}
		if ($echo)
		{
			echo $this->head_ref ($this->JSdir);
			$this->initialized = true;
			$this->is_init = true;
			return true;
		}
		else
		{
			$this->initialized = true;
			$this->is_init = true;
			return self::head_ref ($this->JSdir);
		}
	}
	
	
	function clearCache()
	{
		@session_start();
		$_SESSION['cjax_cache'] = '';
		@setcookie('cjax_cache','',false);
	}
	

	
	/**
	 * Setting up the directory where the CJAX FRAMEWORK resides
	 *
	 * @param string $JSdir
	 */
	function JSdir($JSdir)
	{
		$this->JSdir = $JSdir;
	}
	
	/**
	 * Image directory where loading images are loaded from
	 *
	 * @param unknown_type $dir
	 */
	function image_dir($dir = '')
	{
		$this->image_dir = $dir;
	}

	/**
	 * Optional text, replaces the "loading.." text when an ajax call is placed
	 *
	 * @param unknown_type $ms
	 */
	function text($ms = '')
	{
		$this->text = $ms;
	}
	
	/**
	 * Execution Event, use to create new AJAX calls in inderent scenarios
	 * for example, changing this to "onChage" will cause any element to execute 
	 * the ajax call listening the onChange event
	 * @param unknown_type $JSevent
	 */
	function JSevent($JSevent = "onclick")
	{
		$this->JSevent = $JSevent;
	}
	
	/**
	 * Simple debug option to alert any output by AJAX calls
	 *
	 * @param boolean $debug
	 */
	function debug($debug = false)
	{
		$this->debug = $debug;
	}

	/**
	 * yet to implement
	 *
	 * @param unknown_type $style
	 */
	function style($style = '')
	{
		$this->style = $style;
	}
	
	/**
	 * Require to be set to true before using a text link to execute an AJAX call
	 *
	 * @param boolean $link
	 * @return string
	 */
	function link($link = false)
	{
		$this->link = $link;
	}
	
	/**
	 * if CJAX is not located sircuntacion at the subdirectory level, and 
	 * CJAX is bein called from within a child directory then you will need to specify
	 * the url where CJAX is located (eg. http://yoursite.com/cjax)
	 *
	 * @param string $Path [CJAX URL] 
	 */
	function path($path)
	{
		self::$path = $path;
	}
	
	/**
	 * Outputs our FRAMEWORK to the browser
	 * @param unknown_type $js_path
	 * @return unknown
	 */
	function head_ref($js_path = "")
	{
		$path = '';
		if(isset(self::$path) && strlen(self::$path) > 0)
		{
			if(self::$path[strlen(self::$path)-1] =='/')
			{
				self::$path = substr(self::$path,0,strlen(self::$path) -1);
			}
			$path = self::$path."/core/js/cjax.js";
		} else {
			$path = "{$js_path}cjax.js";
		}
		if (self::$cache) {
			$out = "
			CJAX.source=\"".self::encode(self::$cache)."\";CJAX.replace_txt(true);";
			$out = str_replace(array("\n","\t"), "", $out);
			
			$extra = "
			<script type='text/javascript'>
			$out
			</script>
			";
			self::$cache = '';
		}
		return "<script id='cjax' language='JavaScript' type='text/javascript' src='{$path}'></script>\n\n$extra";
	}
	
	/*
	 * checks for imputs and return values sent throught the $_GET method
	 */
	function get($value)
	{
		global $HTTP_POST_VARS, $HTTP_GET_VARS, $_SERVER, $_REQUEST;
		
		$get_value = @$_REQUEST["$value"];
		
		if(is_array($get_value))
		{
			foreach($get_value as $option => $value )
			{
				$return [$option] =  addslashes($value);
			}
			return $return;
		}
		return addslashes($get_value);
	}

	/*
	 * Checks wether or not the actualy load method is being iniciated from the "fly"
	 * or from an AJAX call
	 */
	function onTheFly()
	{
		if(self::get('cjax')==1)return false;
		return true;
	}
	
	/**
	 * Create Ajax calls
	 *
	 * @param required string $url
	 * @param optional string $elem_id = null
	 * @param optional string $cmode = 'get'
	 * @return string
	 */	
	function call($url,$element_id='', $cmode = "get")
	{
		$event = "_event_";
		$return = '';
		if($this->JSevent) {
			$event = "{$this->JSevent}=\"_event_\"";
			$return = "CJAX.turn(this);";
		}
		if ($this->link){
			$event = "'javascript:void(0)' {$event}";
			$this->link = false;
		}
		$out[] = "<url>$url</url>";
		if($element_id) $out[] = "<element>{$element_id}</element>";
		if($this->text) $out[] = "<text>{$this->text}</text>";
		if ($this->image) $out[] = "<image>{$this->image}</image>";
		if($cmode !='get') $out[] = "<mode>{$cmode}</mode>";
		if ($this->debug) $out[] = "<debug>1</debug>";
		if(isset($out) && is_array($out)) $params = implode($out);
		$html = str_replace("_event_","CJAX.exe_html('{$params}');{$return}",$event);
		return $html;		
	}
		
	/**
	 * Execute an Ajax call "on the fly"
	 * 
	 * @param string $elem_id
	 * @param string $url
	 * @param string $mode
	 * @return an ajax call that will execute when  the page loads
	 */
	function load($url,$elem_id, $mode = 'get')
	{
		$out[]="<url>$url</url>";
		if($elem_id) $out[] = "<element>{$elem_id}</element>";
		if($mode!='get') $out[] = "<mode>{$mode}</mode>";
		if($this->text) $out[] = "<text>{$this->text}</text>";
		if($this->image) $out[] = "<image>{$this->image_dir}{$this->image}</image>";
		if($this->debug) $out[] = "<dubug>1</debug>";	
		
		
		if(isset($out) && is_array($out)) {
			$params  = implode($out);
		}
		
		$script = "
		<script>
			CJAX.exe_html ('{$params}');
		</script>
		";

		return $script;
	}
	
	/**
	 * Executes an Ajax call and send a all the elements inside a form to the server in the $_GET method
	 * 
	 * @param require string $url
	 * @param require string $form_id
	 * @param optional string $container_id = null
	 * @param optional string $mode = 'get'
	 * @return unknown
	 */
	function form($url,$form_id,$container_id = null)
	{
		if ($this->link)
		{
			$event = "'javascript:void(0)' " . $this->JSevent . "=\"";
			$this->link = false;
		}
		else
		{
			$event = $this->JSevent . "=\"";
		}
		$params = '';
		$cote = '';
		
		$out[] = "<url>$url</url>";
		if($form_id) $out[] = "<form>$form_id</form>";
		if(!is_null($container_id)) $out[] = "<container>$container_id</container>";
		if($this->text) $out[] = "<text>{$this->text}</text>";
		if($this->image) $out[] = "<image>{$this->image_dir}{$this->image}</image>";
		if($this->debug) $out[] = "<dubug>1</debug>";	
		if($this->method && $this->method !='get'/* get is default, so it is not a priority */) $out[] = "<method>$this->method</method>";
		if(isset($out) && is_array($out)) $params  = implode($out);
		$turn = "return CJAX.turn(this);\"";
		return $event . "CJAX.exe_form ('{$params}'); $turn ";
	}
	
	/**
	 * Just like form() , but this function is optimized to be loaded on the fly
	 *
	 * @param unknown_type $url
	 * @param unknown_type $form_id
	 * @param unknown_type $container_id
	 * @param unknown_type $mode
	 * @return unknown
	 */
	function callEvent($url,$element_id = null)
	{
		$out[] = "<url>$url</url>";
		if($element_id) $out[] = "<element>{$element_id}</element>";
		if($this->text) $out[] = "<text>{$this->text}</text>";
		if ($this->image) $out[]  = "<image>$this->image</image>";
		if(isset($cmode)) $out[] = "<mode>$cmode</mode>";
		if ($this->debug) $out[] = "<debug>true</debug>";
		if(isset($out) && is_array($out)) $params  = implode($out);
		$return = "({$params})";
		$return ='CJAX.exe_html'.$return.";";
		return $return;
	}
	
	/**
	 * Create Ajax calls
	 *
	 * @param required string $url
	 * @param optional string $elem_id = null
	 * @param optional string $cmode = 'get'
	 * @return string
	 */
	function call_server($url,$element_id, $cmode = "get")
	{
		if($elem_id) $out[] = "<element>{$element_id}</element>";
		if($this->text) $out[] = "<text>{$this->text}</text>";
		if ($this->image) $out[]  = "<image>$this->image</image>";
		if($cmode !='get') $out[] = "<mode>$cmode</mode>";
		if ($this->debug) $out[] = "<debug>true</debug>";
		if(isset($out) && is_array($out)) $params  = implode($out);
		$this->xml("<url>$url</url>$params");
	}
		
	/**
	 * Syntax hilighting a program source file. It calls enscript(1) to parse and
	 * insert HTML tags to produce syntax hilighted version of the source.
	 *
	 * @param  $filename The filename of the source file to be transformed.
	 * @return A text string containing syntax hilighting version of the source,
	 *         in HTML.
	 */
	function syntax_hilight($filename) {
	    if ((substr($filename, -4) == '.php')) {
	        ob_start();
	        show_source($filename);
	        $buffer = ob_get_contents();
	        ob_end_clean();
	    } else {
	        $argv = '-q -p - -E --language=html --color '.escapeshellcmd($filename);
	        $buffer = array();
	
	        exec("enscript $argv", $buffer);
	
	        $buffer = join("\n", $buffer);
	        $buffer = preg_replace('/^.*<PRE>/i',  '<pre>',  $buffer);
	        $buffer = preg_replace('/<\/PRE>.*$/i', '</pre>', $buffer);
	    }
	
	    // Making it XHTML compatible.
	    $buffer = preg_replace('/<FONT COLOR="/i', '<span style="color:', $buffer);
	    $buffer = preg_replace('/<\/FONT>/i', '</style>', $buffer);
	
	    return $buffer;
	}
	
}