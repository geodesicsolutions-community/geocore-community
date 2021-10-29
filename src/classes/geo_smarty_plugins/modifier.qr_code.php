<?php
//modifier.qr_code.php
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
## ##    6.0.7-2-gc953682
## 
##################################

//this smarty plugin uses the Google Charts API to turn a string of text into a QR code

function smarty_modifier_qr_code ($text, $dims=100, $encoding='UTF-8', $error_correction='L')
{
	$text = urlencode($text);
	$dims = $dims . 'x' . $dims; //QR codes must be square
	$api_url = "https://chart.googleapis.com/chart?cht=qr&amp;chs={$dims}&amp;chl={$text}&amp;choe={$encoding}&amp;chld={$error_correction}";
	$tpl = new geoTemplate('system','other');
	$tpl->assign('api_url',$api_url);
	return $tpl->fetch('qr_code.tpl');
}
