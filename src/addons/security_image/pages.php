<?php
//addons/security_image/pages.php
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

# pages class for security image, we will be displaying the security image

class addon_security_image_pages extends addon_security_image_info
{
	private $reg, $oImage, $tImage, $width, $height, $spacing;
	private $white, $gray, $black, $transparent, $fonts;
	
	
	public function image ()
	{
		//Tell view class not to display
		geoView::getInstance()->setRendered(true);
		
		//check to make sure one of methods exists
		if(! (function_exists('imagecreate') || function_exists('imagecreatetruecolor'))) {
			echo 'Error: GD Library not found, or not up to date.  The functions imagecreate() or imagecreatetruecolor() do not exist.  The security image requires GD library to work.  See the user manual for more information.';
			return;
		}
		
		if (!function_exists('imagejpeg') && !function_exists('imagegif') && !function_exists('imagepng') && !function_exists('imagewbmp')) {
			echo 'Error: GD Library cannot create image, there is no jpeg, gif, png, or even bmp support.';
			return;
		}
		$this->_init();
		
		if ($this->reg->useLines) { 
			$lineCount = $this->reg->get('lines', 3);
			for ($i = 0; $i < $lineCount; $i++) {
				$multx = ($i)? (50*floor($i/3-1) + rand(-10,10)): 0;
				$multy = (($i)? 20*floor($i%3-1): 0) + rand(-5,5);
				
				$x = $this->width/2 + $multx;
				$y = $this->height/2 + $multy;
				
				$this->_drawLines($x, $y, (($i)? 0: 1));
			}			
		}
		
		if ($this->reg->useGrid) {
			$this->_drawGrid();
		}
		if ($this->reg->useNoise) {
			$this->_drawNoise();
		}
		$this->_getFonts();
		
		$code = $this->_genChars();
		
		if (!$code) {
			echo 'Error generating security code.';
			return;
		}
		$this->_drawCode($code);
		
		if ($this->reg->useDistort) $this->_warpText();
		
		//apply transparencies
		$this->_applyTransparency();
		
		//make the image
		imagecopymerge ($this->oImage, $this->tImage, 0,0,0,0,$this->width, $this->height,100);
		
		// apply blur, emboss, sketchy, negative filters
		$this->_applyFilters();
		
		if ($this->reg->useRefresh && $this->reg->refreshUrl) {
			$rImage = $this->_loadImageFromFile( $this->reg->refreshUrl );
			if ($rImage){
				$rWidth = imagesx( $rImage );
				$rHeight = imagesy( $rImage );
				
				$dst_x = $this->width - $rWidth;
				
				imagecopy( $this->oImage, $rImage, $dst_x, 0, 0, 0, $rWidth, $rHeight );
			}
		}
		
		imagerectangle($this->oImage,0,0,$this->width-1,$this->height-1,$this->black);
		
		$this->_setHeaders();
		
		if (function_exists("imagepng")) {
			//prefer using png, it's most high tech
			header("Content-type: image/png");
			imagepng($this->oImage);
		} elseif (function_exists("imagegif")) {
			header("Content-type: image/gif");
			imagegif($this->oImage);
		} elseif (function_exists("imagejpeg")) {
			header("Content-type: image/jpeg");
			imagejpeg($this->oImage);
		} elseif (function_exists("imagewbmp")) {
			header("Content-type: image/vnd.wap.wbmp");
			imagewbmp($this->oImage);
		}
		
		// free memory used in creating image
		imagedestroy($this->oImage);
		imagedestroy($this->tImage);
		return true;
	}
	
	private function _init ()
	{
		$this->reg = geoAddon::getRegistry($this->name);
		
		$this->width = $this->reg->get('width', 125);
		$this->height = $this->reg->get('height', 50);
		
		// create new image
		$this->oImage = $this->_createImage();
		$this->tImage = $this->_createImage();
		
		// calculate spacing between characters based on width of image
		$this->spacing = (int)($this->width / $this->reg->get('numChars',4));
		
		imagefill($this->oImage,0,0,$this->white);
		imagefill($this->tImage,0,0,$this->transparent);
	}
	
	private function _createImage()
	{
		if (function_exists("imagecreatetruecolor")) {
			$new_image = imagecreatetruecolor($this->width,$this->height);
		} else if (function_exists('imagecreate')) {
			$new_image = imagecreate($this->width,$this->height);
		} else {
			//show not get here, checks already done, but ya never know..
			return false;
		}
		// allocate white background colour
		$this->white = ImageColorAllocate($new_image, 255, 255, 255);
		$this->gray = ImageColorAllocate($new_image, 100, 100, 100);
		$this->black = ImageColorAllocate($new_image, 0, 0, 0);
		$this->transparent = ImageColorAllocate($new_image, 127, 127, 127);
		return $new_image;
	}
	
	private function _setHeaders ()
	{
		//Sets the headers to try to prevent caching of the file.
		//return;
		
		header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header( 'Cache-Control: post-check=0, pre-check=0', false );
		header( 'Pragma: no-cache' );
	}
	
	private function _frand()
	{ 
		return 0.0001*rand(0,9999); 
	}
	
	private function _prand ($i, $n)
	{
		if ($n <= 1) {
			return $this->_frand();
		}
		return ($i-1+0.25*($this->_frand()-0.5))/($n-1);
	}
	
	private function _kscale($a, $b)
	{
		$norm = sqrt($a*$a + $b*$b);
		return pow($norm, 1.3);
	}
	
	private function _drawLines ($x, $y, $longhorizflag)
	{
		if ($longhorizflag) {
			$theta = 0;
			$len = rand(100, 150);
			$lwid = 1;
		} else {
			$theta = ($this->_frand()-0.5)*M_PI*0.5;
			$len = rand(25,45);
			$lwid = 1;
		}
		
		$k = $this->_frand() * 0.6 + 0.2;
		$k = $k * $k * 0.5;
		
		$phi = $this->_frand() * 6.28;
		$step = 0.5;
		$dx = $step * cos($theta);
		$dy = $step * sin($theta);
		$n = $len/$step;
		$amp = 3 * $this->_frand() / ($k+5.0/$len);
		$x0 = $x - 0.5 * $len * cos($theta);
		$y0 = $y - 0.5 * $len * sin($theta);
		
		$ldx = round(-$dy*$lwid);
		$ldy = round($dx*$lwid);
		for ($i = 0; $i < $n; ++$i) {
			$x = $x0 + $i * $dx + $amp * $dy * sin($k * $i * $step + $phi);
			$y = $y0 + $i * $dy - $amp * $dx * sin($k * $i * $step + $phi);
			imageline($this->oImage, $x, $y, $x+$lwid, $y+$lwid, $this->_getColor());
		}
	}
	
	private function _getColor ()
	{
		if ($this->reg->useRandomColors) {
			$r = rand($this->reg->get('rmin',0), $this->reg->get('rmax',150));
			$g = rand($this->reg->get('gmin',0), $this->reg->get('gmax',150));
			$b = rand($this->reg->get('bmin',0), $this->reg->get('bmax',150));
			
			$color = imagecolorallocate($this->oImage, $r,$g,$b);
			return $color;
		}
		//default to use gray for everything if random colors are off.  How dull!
		return $this->gray;
	}
	
	private function _drawGrid ()
	{
		//randomize the canvas
		
		$ratio = ($this->width < $this->height)? ($this->width/$this->height) : ($this->height/$this->width);
		
		$ratio++;
		$numGrid = $this->reg->get('numGrid',8);
		
		$y_lines = floor($numGrid / $ratio);
		$x_lines = $numGrid - $x_lines;
		
		$x_dist = $this->width / $x_lines;
		$y_dist = $this->height / $y_lines;
		
		for ( $y = mt_rand($y_dist-2, $y_dist+2); $y < $this->height; $y += mt_rand($y_dist-2, $y_dist+2) ) {
			$color = $this->_getColor();
			ImageLine($this->oImage, 0, $y, $this->width, $y, $color); 
		}	
		for ( $x = mt_rand($x_dist-2, $x_dist+2); $x<$this->width; $x+=mt_rand($x_dist-2, $x_dist+2) ) {
			$color = $this->_getColor();
			ImageLine($this->oImage, $x, 0, $x, $this->height, $color);  
		}
	}
	
	private function _drawNoise ()
	{
		for( $x = 0; $x < $this->reg->get('numNoise',250); $x++) {
			$color = $this->_getColor();
			imagesetpixel($this->oImage, rand(0,$this->width), rand(0,$this->height), $color);
		}
	}
	
	private function _getFonts ()
	{
		$fontsDir = ADDON_DIR . 'security_image/fonts/';
		
		if ($handle = opendir($fontsDir)) {
		   while (false !== ($file = readdir($handle))) {
		       if ($file != "." && $file != ".." && stristr($file, '.ttf')) {
		           $this->fonts[] = $fontsDir.$file;
		       }
		   }
		   closedir($handle);
		}
	}
	
	private function _genChars ()
	{
		if (defined('IS_ROBOT') && IS_ROBOT){
			//it is a robot, so cookies aren't set.
			return 'RoBoT';
		}
		$db = $session = 1;
		include GEO_BASE_DIR . 'get_common_vars.php';
		
		$sessionCode = $session->getSessionId();
		if (!$sessionCode) {
			//Oops!  don't bother with code if there isn't a session ID!
			if (self::DEBUG) echo 'Could not retrieve session ID!<br />';
			return false;
		}
		
		$code = '';
		$allowedChars = $this->reg->get('allowedChars','2346789ABCDEFGHJKLMNPRTWXYZ');
		$allowedCount = strlen($allowedChars)-1;
		
		$numChars = $this->reg->get('numChars',4);
		
		// loop through and generate the code letter by letter
		for ($i=0; $i<$numChars && strlen($code) < $numChars; $i++) {
			$code .= substr($allowedChars, mt_rand(0, $allowedCount), 1);
		}
		
		$sql = "UPDATE `geodesic_sessions` SET `securityString` = ? WHERE `classified_session` = ?";
		
		$result = $db->Execute($sql, array($code, $sessionCode));
		if (!$result) {
			//That's weird, some sort of DB error
			if (self::DEBUG) echo 'DB Error inserting new code! '.$db->ErrorMsg().'<br />';
			return false;
		}
		return $code;
	}
	
	private function _drawCode ($code)
	{
		$codeLen = strlen($code);
		$fontCount = count($this->fonts) - 1;
		
		for ($i = 0; $i < $codeLen; $i++) {
			$black = ImageColorAllocate($this->tImage, 0, 0, 0);
			$angle = rand(-25,25);
			$yValue = rand(0,10);
			$characterX = ($this->spacing / 3) + ($i * $this->spacing);
			
			$fontsize = $this->reg->get('fontSize',16);
			
			if(function_exists('imagettftext') && count($this->fonts) > 0) {
				//preferred method, use fonts from TTF library.
				
				$characterY = ceil($this->height/2) + ($fontsize/2) + $yValue;
				//make sure it is shifted down to account for refresh overlayed
				$characterY = ($this->reg->useRefresh && $characterY < 16)? 16: $characterY;
				
				$font = $this->fonts[rand(0, $fontCount)];
				imagettftext($this->tImage, $fontsize, $angle, $characterX, $characterY, $this->_getColor(), $font, $code[$i]);
			} else {
				$yOffset = ($this->reg->useRefresh)? 15: 9;
				
				//font size has max of 5 using imagestring
				$fontsize = ($fontsize <= 5)? $fontsize: 5;
				
				//do it twice slightly offset to make it bold I'm guessing?
				imagestring($this->tImage, $fontsize, $characterX-1, $yValue+$yOffset, $code[$i], $this->_getColor());
				imagestring($this->tImage, $fontsize, $characterX, $yValue+$yOffset+1, $code[$i], $this->_getColor());
			}
		}
	}
	
	
	
	private function _warpText ()
	{
		//Bored are you?  See if you can reduce this method mathmatically!  Dust
		//off your mad math skills, put it to the test.
		$p = $this->reg->get('distort',1);
		
		$sx = $this->width;
		$sy = $this->height;
	
		// copy into $img2
		$img2 = $this->_createImage($sx, $sy);
		imagepalettecopy($img2, $this->tImage);
		imagecopy($img2, $this->tImage, 0,0 ,0,0, $sx, $sy); 
		
		// x and y distortion: a couple of fourier components
		$kmin = 0.05;
		$kmax = 0.4*$p;
		$nfreq = 2;
		$maxamp = 0.2*$p; // relative to frequency
		for ($i = 1; $i <= $nfreq; ++$i) {
			$kxx[$i] = $kmin * exp($this->_prand($i, $nfreq)*(log($kmax/$kmin)));
			$kxy[$i] = $kmin * exp($this->_prand($i, $nfreq)*(log($kmax/$kmin)));
			$kyx[$i] = $kmin * exp($this->_prand($i, $nfreq)*(log($kmax/$kmin)));
			$kyy[$i] = $kmin * exp($this->_prand($i, $nfreq)*(log($kmax/$kmin)));
		}
		for ($i = 1; $i <= $nfreq; ++$i) {
			for ($j = 1; $j <= $nfreq; ++$j) {
				$cofsx[$i][$j] = $maxamp/$this->_kscale($kxx[$i], $kxy[$j])*(0.5*$this->_frand()+0.5);
				$cofsy[$i][$j] = $maxamp/$this->_kscale($kyx[$i], $kyy[$j])*(0.5*$this->_frand()+0.5);
			}
		}

		// sine tables
		for ($i = 1; $i <= $nfreq; ++$i) {
			$phix = 6.28*$this->_frand();
			$phiy = 6.28*$this->_frand();
			for ($x = 0; $x < $sx; ++$x) {
				$sinxx[$x][$i] = sin($x*$kxx[$i] + $phix);
				$sinyx[$x][$i] = sin($x*$kyx[$i] + $phiy);
			}
			$phix = 6.28*$this->_frand();
			$phiy = 6.28*$this->_frand();
			for ($y = 0; $y < $sy; ++$y) {
				$sinxy[$y][$i] = sin($y*$kxy[$i] + $phix);
				$sinyy[$y][$i] = sin($y*$kyy[$i] + $phiy);
			}
		}
		
		// background color
		$bgc = $this->transparent;
		// copy bitwise back into $this->tImage
		$hx = $sx/2; $hy = $sy/2;
		for ($x = 0; $x < $sx; ++$x)  {
			for ($y = 0; $y < $sy; ++$y) {
				$dx = 0;
				$dy = 0;
				for ($i = 1; $i <= $nfreq; ++$i) {
					for ($j = 1; $j <= $nfreq; ++$j) {
						$dx += $cofsx[$i][$j]*$sinxx[$x][$i]*$sinxy[$y][$j];
						$dy += $cofsy[$i][$j]*$sinyx[$x][$i]*$sinyy[$y][$j];
					}
				}
				$x2 = $x + $dx;
				$y2 = $y + $dy;
				$c = $bgc;
				if ($x2 >= 0 && $x2 < $sx && $y2 >= 0 && $y2 < $sy) {
					$c = imagecolorat($img2, $x2, $y2);
				}
				imagesetpixel($this->tImage, $x, $y, $c);
			}
		}
		imagedestroy($img2);
	}
	
	private function _applyTransparency ()
	{
		imagecolortransparent($this->tImage,$this->transparent);
		imagecolortransparent($this->oImage,$this->transparent);
	}
	
	private function _applyFilters ()
	{
		if (!is_callable('imagefilter')){
			return ;
		}
		$filters = array (
			'useBlur' => IMG_FILTER_GAUSSIAN_BLUR,
			'useEmboss' => IMG_FILTER_EMBOSS,
			'useSketchy' => IMG_FILTER_MEAN_REMOVAL,
			'useNegative' => IMG_FILTER_NEGATE
		
		);
		foreach ($filters as $setting => $filter) {
			if ( $this->reg->$setting) {
				//apply this filter
				$backupImage = $this->oImage;
				if (! imagefilter($this->oImage,$filter) ){
					$this->oImage = $backupImage;
				}
				unset($backupImage);
			}
		}
	}
	
	private function _loadImageFromFile ($url)
	{
		$load_functions = array (
			'imagecreatefromgif',
			'imagecreatefromjpeg',
			'imagecreatefrompng',
			'imagecreatefromwbmp'
		);
		$img_resource = false;
		foreach ($load_functions as $func_name){
			if (function_exists($func_name)){
				$img_resource = $func_name($url);
				if ($img_resource) {
					break; //finally got the img resource.
				}
			}
		}
		return $img_resource;
	}
}
