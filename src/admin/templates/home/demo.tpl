{* 7.0.0-71-g719f0c5 *}

{* so we don't have to have super long thing for each link *}
{capture assign='extrnLink'}class="mini_button" style="white-space: normal;" onclick="window.open(this.href); return false;"{/capture}


<fieldset>
	<legend>GeoCore Trial Demo</legend>
	<div class="medium_font">
		<table style="width: 100%;">
			<tr class="{cycle values='row_color2,row_color1'}">
				<td class="stats_txt2">
					Trial Demo Expires:
				</td>
				<td class="stats_txt3_wide">
					<span class="text_blue">{$stats.downloadExpire|date_format}</span>
				</td>
			</tr>
		</table>
		<br />
		<div class="page_note">
			{if $stats.downloadLeft}
				<img src="admin_images/bullet_success.gif" alt="Download Access Active" style="margin: 5px; vertical-align: middle; float: left;" />
				<p>You have <strong class="text_blue">{$stats.downloadLeft}</strong> remaining on your Trial Demo.</p>
				<p>If you have any questions about the software, please feel free to contact us via LiveChat at <a href="http://geodesicsolutions.com/" {$externLink}>geodesicsolutions.com</a> or by email at <a href="mailto:sales@geodesicsolutions.com">sales@geodesicsolutions.com</a></p>
			{else}
				<img src="admin_images/bullet_error.gif" alt="Notice" style="margin: 5px; vertical-align: middle;float: left;" />
				<p>Your Trial Demo has <strong style="color: red;">Expired</strong> and is scheduled for automatic deletion from our server in <strong style="color: red;" title="{($stats.downloadExpire + 1209600)|date_format}">{$demo_deletion}</strong>.</p>
				<p>Once it has been deleted, it cannot be recovered. If you need more time to evaluate the software, please contact us immediately at <a href="mailto:sales@geodesicsolutions.com">sales@geodesicsolutions.com</a> to request an extension.</p>
			{/if}
			<div class="clr"></div>
		</div>
	</div>
</fieldset>