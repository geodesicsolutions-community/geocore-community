<?php

defined('IN_ADMIN') || die('NO ACCESS');

//include_once '../app_top.common.php';

$software_name = "GeoCore";

    $footer_html = (isset($footer_html)) ? $footer_html : '';
    $footer_html .= "
			</td>
		</tr>
	</table>
	{if !$white_label}
	<div style='padding: 0.5em 0; background-color: #000066; text-align: right;'>
		<div class='medium_font_light' style='float: left;'>
			&nbsp;Created by <a href=http://www.geodesicsolutions.com class=medium_font_light style='color: white;'>Geodesic Solutions LLC</a>
		</div>
		<div class='medium_font_light' style=' text-align: right; white-space:nowrap;'>
			" . $software_name . " DB Ver. " . geoPC::getVersion() . "&nbsp; [ <a href=\"http://geodesicsolutions.com/changelog\" class=\"medium_font_light\">Release Notes</a> ]&nbsp; 
		</div>
	</div>
	{/if}
</html>";


return $footer_html;
