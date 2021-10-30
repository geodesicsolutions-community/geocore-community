<?php
//Format.class.php
/**
 * Holds the class geoFormatString.
 * 
 * @package System
 * @since Version 4.0.0
 */


/**
 * Used to display a message nice and pretty, we'll probably be getting rid of
 * this class though so don't go crazy using it.
 * 
 * @package System
 * @since Version 4.0.0
 */
class geoFormatString
{
	
	/**
	 * Formats a passed text to display a friendly user message.
	 *
	 * @param string $msg
	 * @param int $type [GEO_SUCCESS = 1, GEO_NOTICE = 2, GEO_FAILURE = 3]
	 * @return string
	 */
	public function message ( $msg, $type = GEO_SUCCESS)
	{
		$css = 
		"
		<style>
		div.userMessage {
			margin: 1em 0;
			border-width: 2px;
			border-style: solid;
			font-weight: bold;
			text-align: left;
			font-size: 14px;
			padding: 0px 15px 0px 0px;
			z-index: 10000;
		}
		
		div.userMessage a {
			/* text-decoration: none;	*/
			text-decoration: underline;
		}
		
		div.success ul,
		div.error ul,
		div.notice ul {
			margin: 0 0 0 0;
			padding-left: 0px;
		}
		
		div.error a { color: #B00000; }
		div.error {
			border-color: #B00000;
			background-color: #FFEAEA;
			color: #B00000;
		}
		
		div.error ul li{
			padding-left: 30px;
			padding-top: 6px;
			min-height: 18px;
			margin: 5px;
			list-style-type:none;
			background:url(./admin/admin_images/bullet_error.gif) left top no-repeat;
		}
		
		div.notice a { color: #005097; }
		div.notice {
			border-color: #005097;
			background-color: #E1F1FF;
			color: #005097;
		}
		
		div.notice ul li{
			padding-left: 30px;
			padding-top: 6px;
			min-height: 18px;
			margin: 5px;
			list-style-type:none;
			background:url(./admin/admin_images/bullet_notice.gif) left top no-repeat;
		}
		
		div.success a { color: #608B1F; }
		div.success {
			border-color: #608B1F;
			background-color: #E9F9D2;
			color: #608B1F;
		}
		
		div.success ul li{
			padding-left: 30px;
			padding-top: 6px;
			min-height: 18px;
			margin: 5px;
			list-style-type:none;
			background:url(./admin/admin_images/bullet_success.gif) left top no-repeat;
		}

	</style>
		";
		
		geoView::getInstance()->addTop($css);
		
		//TODO: add  type for Failure and Notice
		switch($type) {
			case GEO_SUCCESS:
				$csstype = "success";
				break;
			case GEO_NOTICE:
				$csstype = "notice";
				break;
			case GEO_FAILURE:
				$csstype = "error";
				break;
		}
		
		
		$message = "
		<div class='userMessage $csstype'>
			<ul>
					<li>
					$msg
					</li>
			</ul>
		</div>";
		
		
		return $message;
	}
}