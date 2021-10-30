<?php
//twitter_feed/CLASSES.ajax.php


// DON'T FORGET THIS
if( class_exists( 'classes_AJAX' ) or die());

class addon_twitter_feed_CLASSES_ajax extends classes_AJAX {	

	public function processWidgetCode ()
	{
		if (!$this->_checkSession()) {
			return $this->_failure(1);
		}
		
		$code = geoString::specialCharsDecode($_POST['code']);
		
		/*
		 * $code will (hopefully) look something like this:
		 * 
		 * 
		   <a class="twitter-timeline"  href="https://twitter.com/cytael"  data-widget-id="345228395174563840">Tweets by @cytael</a>
			<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){
			js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}
			(document,"script","twitter-wjs");</script>
			
		 * all we care about are the href and data-widget-id parts of the <a> -- everything else will be the same for every timeline
		 *
		 * FIRST: make sure that what we've got in $code actually resembles the above
		 * THEN: strip out all the rest of the crap and send those two pieces back to the form in JSON
		 * 
		 * NOTE: be super-duper careful about security here, since this is accepting basically freeform HTML. Make absolutely certain that
		 * 			what we're given is exactly what we're expecting, and err on the side of caution.
		 */ 
		
		$code = trim($code); //because copy/pasting is sometimes stupid
		
		//first, a speedy check to make sure we're at least in the right ballpark
		if(strpos($code,'<a class="twitter-timeline"') !== 0) {
			return $this->_failure(2);
		}
		
		//now get the entire opening <a> tag, and dump the rest of the string
		$end = strpos($code,'>');
		if(!$end) {
			return $this->_failure(3);
		}
		$code = substr($code, 0, $end+1);
		
		//now we have a string that begins with <a class="twitter-timeline" and ends with >, so it should be reasonably secure
		
		//now use regex to pull the href and data-widget-id contents
		//for sanity and security, make sure the href starts with https://twitter.com/ and the id is an integer
		$href = $data_id = false;
		$matches = array();
		if(preg_match('/href="(.+?)"/', $code, $matches) === 1) {
			$href = $matches[1];
		}
		
		if(strpos($href, "https://twitter.com/") !== 0) {
			//no href, or a href we weren't expecting
			return $this->_failure(4);
		}
		
		
		$matches = array();
		if(preg_match('/data-widget-id="(.+?)"/', $code, $matches) === 1) {
			$data_id = $matches[1];
		}
		
		if(!$data_id || !is_numeric($data_id)) {
			//no data id, or data id not an integer :: NOTE: This is no longer a failure condition
			$data_id = '';
		}
		
		
		$return = array(
			'status' => 'ok',
			'href' => geoString::toDB($href), //just a bit of added injection security, to be safe
			'data_id' => $data_id
		);
		
		
		
		return $this->encodeJSON($return);
	}
	
	
	private function _failure($errNum)
	{
		return $this->encodeJSON(array('status'=>'error','errNum'=>$errNum));
	} 
	private function _checkSession ()
	{
		//If any security checks are needed, do them here.
		return true;
	}
}