<?php
//addons/show_debug_messages/util.php
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
## ##    6.0.7-2-gc953682
## 
##################################

# Show Filtered Debug Messages addon

class addon_show_debug_messages_util {
	private $messages = array();
	private $starttime;
	private $filters;
	private $data_dumped;
	private $css_outputed;
	/**
	 * Number of lines to offset by, to account for header change.
	 */
	private $line_offset = -6;
	
	/**
	 * whether to use the line offset or not.
	 */
	private $use_offset = false;
	
	public function __construct(){
		$this->starttime = microtime(true);
		$this->filters = array();
		$this->data_dumped = false;
		$this->css_outputed = false;
		$filter_string='';
		if (isset($_GET['line_offset']) || (!isset($_COOKIE['line_offset']) && defined('IAMDEVELOPER'))){
			if (isset($_GET['line_offset']) && is_numeric($_GET['line_offset'])){
				//use that offset number instead of the default.
				setcookie('line_offset',intval($_GET['line_offset']));
				$this->line_offset = $_GET['line_offset'];
			} else {
				//use the default line offset number.
				setcookie('line_offset', $this->line_offset);
			}
			$this->use_offset = true;
		} else if (isset($_COOKIE['line_offset'])){
			$this->use_offset = true;
			$this->line_offset = intval($_COOKIE['line_offset']);
		}
		if (isset($_GET['debug'])) {
			setcookie('debug',$_GET['debug']);
			$filter_string=$_GET['debug'];
		} else if (isset($_COOKIE['debug'])){
			$filter_string = $_COOKIE['debug'];
		}
		if (strlen($filter_string)>0) {
			if (strstr($filter_string,' ')!=false){
				//make an array of filters
				$this->filters = explode(' ',strtoupper($filter_string));
			} else {
				//make an array of one filter.
				$this->filters = array (strtoupper($filter_string));
			}
		} else {
			//make an empty array.
			$this->filters = array ();
		}
		if (defined('IN_ADMIN') && count($this->filters) > 0) {
			$v = geoView::getInstance();
			$v->addJScript('../addons/show_debug_messages/messages_in_admin.js');
		}
	}
	public function core_app_bottom(){
		if (defined('IN_ADMIN') && !geoAjax::isAjax()){
			//on admin side, there is no filter display page, so need to call manually.
			$msgs = $this->formatMessages();
			if ($msgs) {
				echo "<div class='debugMessages'>".$msgs."</div>";
			}
		}
	}
	public function core_filter_display_page($full_text){
		if (strpos($full_text,'</body>') !== false ) {
			$output = $this->formatMessages();
			$full_text = str_replace('</body>',$output.'</body>',$full_text);
		}
		return $full_text;
	}
	public function core_filter_display_page_nocache($full_text){
		return $this->core_filter_display_page($full_text);
	}
	public function core_errorhandle ($message_data) {
		//$message_data = $observer->getState();
		//find out what kind of message this is.
		if ($message_data['errstr']=='FLUSH MESSAGES'){
			//dump the data.
			$this->outputAllErrors();
			return true;
		}
		$class='error_msg';
		$show_this_message = false;
		//$match_type = array();
		$withBacktrace = false;
		
		if (preg_match('/^(DEBUG|ERROR)/',$message_data['errstr'],$match_type)>0){
			//if the message is prepended with DEBUG then treat it as a debug message.
			$class=strtolower($match_type[1]).'_msg';
		} else {
			//we are only filtering messages prepended with DEBUG or ALL, go ahead and show all errors.
			$show_this_message = true;
		}
		$matches = array(); //make sure the array is cleared.
		if (in_array('ALL',$this->filters)){
			//if ALL is one of the filters, then always show the debug message.
			$show_this_message = true;
		} 
		
		if (preg_match ('/^(DEBUG|ERROR) ?([A-Z_ ]+)\:/',$message_data['errstr'], $matches)>0){
			$debug_type = explode(' ',$matches[2]);
			$base = $class;
			foreach($debug_type as $type){
				if (in_array($type, $this->filters)){
					//show the thing.
					$show_this_message = true;
					$class = $base.'_'.$type;
				}
				if("BACKTRACE" == $type) {
					$withBacktrace = true;
				}
			}
			if ($class==$base && in_array('ALL',$this->filters)){
				$class=$base.'_ALL';
			}
		}
		
		
		if ($show_this_message && count($this->filters)){
			$trace = '';
			if ($withBacktrace) {
				$trace = debug_backtrace();
				$trace = $trace;
				die( '<div><pre>'.print_r($trace,1).'</pre></div>' );
			}
			$mem = (function_exists('memory_get_usage'))? geoNumber::filesizeFormat(memory_get_usage()) : 0;
			$mem_usage = ($mem)? '<div>Mem: '.$mem.'</div>' : '';
			$msg = "<div class='{$class}'><div>T: ".(microtime(true)-$this->starttime)." S</div>
			{$mem_usage}<div>{$message_data['errstr']}</div>
			<div>{$message_data['errfile']}</div>
			<div>{$message_data['errline']}".(($this->use_offset)? ' ('.($message_data['errline'] + $this->line_offset).')' : '' )."</div>
			{$trace}</div>";
			
			//$this->starttime = microtime(true);
			if ($this->data_dumped && count($this->filters)>0){
				//data already dumped, so just output it to the screen.
				$this->outputCss();
				echo "<div class='debugMessages'>$msg</div>";
			} else {
				$this->messages [] = $msg;
			}
		}
	}
	private function outputAllErrors(){
		if (count($this->messages)>0 && count($this->filters) > 0){
			echo $this->formatMessages();
		}
		$this->data_dumped = true;
		$this->css_outputed = true;
	}
	
	private function outputCss(){
		echo $this->formatCss();
		$this->css_outputed = true;
	}
/**
	 * Takes the current messages, and returns them in a string.
	 *
	 */
	private function formatMessages(){
		//if we have messages to display, and debug is turned on
		$output = '';
		if (count($this->messages)>0 && count($this->filters) > 0){
			$time = microtime(true);
			$output .= $this->formatCss();
			$output .= implode(' ',$this->messages);
			if (!$this->data_dumped && in_array('STATS',$this->filters)){
				//add db stats data...
				$db = true;
				include (GEO_BASE_DIR.'get_common_vars.php');
				$output .= '<div class="error_msg"><div>DB Query Stats: </div><div>'.$db->getStats().'</div></div>';
			}
			$output .= '<div class="error_msg"><div>Time took to display all messages: </div><div>'.(microtime(true) - $time).'</div></div>';
		}
		$this->data_dumped = true;
		$this->messages = array();
		return $output;
	}
	/**
	 * generates the css necessary to display messages all pretty like,
	 * as long as the css has not already been output.
	 */
	private function formatCss(){
		if ($this->css_outputed){
			//css already outputed.
			return '';
		}
		$output = '';
		$styles = '<style type="text/css">'."\n";
		$color_msgs = '<div class="error_msg"><div>Error Messages</div></div>';
		foreach ($this->filters as $type){
			$r = rand(hexdec('a0'),hexdec('ef'));
			$g = rand(hexdec('a0'),hexdec('ef'));
			$b = rand(hexdec('a0'),hexdec('ef'));
			
			$bg = dechex($r).dechex($g).dechex($b);
			$color_msgs .= '<div class="debug_msg_'.$type.'"><div>'.$type.' Filter Color</div></div>';
			$color = dechex(abs(hexdec($bg)-hexdec('ffffff')));
			//echo $color;
			$styles .= '.debug_msg_'.$type.' {
	clear: both;
	float:left;
	border:thin dotted #'.$bg.';
	margin: 5px;
}
.debug_msg_'.$type.' div {
	float:left; 
	padding:10px;
	border-left:thin dashed blue;
	background-color:#'.$bg.';
	color:#'.$color.';
}
.error_msg_'.$type.' {
	float:left;
	border:thick dotted '.$bg.';
	margin:5px;
}
.error_msg_'.$type.' div {
	float:left; 
	padding:10px;
	border-left:thick dashed '.$bg.';
	background-color:#FFBFBF;
}
';
			}
			
		$output .= $styles.'.error_msg {
	'.(in_array('NOERR',$this->filters)?'display:none;':'').'
	float:left;
	border:thin dotted red;
	margin:5px;
}
.error_msg div {
	float:left; 
	padding:10px;
	border-left:thin dashed red;
	background-color:#FF8F8F;
}</style>';
		$output .= '<h2>Debug Info:</h2>'."\n".$color_msgs.'<br style="clear:both;" />';
		$this->css_outputed = true;
		return $output;
	}
}