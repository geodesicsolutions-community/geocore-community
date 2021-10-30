{* 7.2beta3-46-g391f053 *}

{* so we don't have to have super long thing for each link *}
{capture assign='extrnLink'}class="mini_button" style="white-space: normal;" onclick="window.open(this.href); return false;"{/capture}


<fieldset>
	<legend>Leased License Info</legend>
	<div class="medium_font">
		<table style="width: 100%;">
			<tr class="{cycle values='row_color1,row_color2'}">
				<td class="stats_txt2">
					License Key:
				</td>
				<td class="stats_txt3_wide" style="white-space: nowrap;">
					<span class="text_blue">{$stats.licenseKey}</span>
				</td>
			</tr>
			<tr class="{cycle values='row_color1,row_color2'}">
				<td class="stats_txt2">
					License Expires:
				</td>
				<td class="stats_txt3_wide" style="white-space: nowrap;">
					<span class="text_blue">
						{if $stats.licenseExpire=='never'}
							When Lease Ends
						{else}
							{$stats.licenseExpire|date_format}
						{/if}
					</span>
				</td>
			</tr>
			<tr class="{cycle values='row_color1,row_color2'}">
				<td class="stats_txt2">
					Download Access Expires:
				</td>
				<td class="stats_txt3_wide" style="white-space: nowrap;">
					<span class="text_blue">
						When Lease Ends
					</span>
				</td>
			</tr>
			<tr class="{cycle values='row_color1,row_color2'}">
				<td class="stats_txt2">
					Paid Support Expires:
				</td>
				<td class="stats_txt3_wide" style="white-space: nowrap;">
					<span class="text_blue">
						When Lease Ends
					</span>
				</td>
			</tr>
			<tr class="{cycle values='row_color1,row_color2'}">
				<td class="stats_txt2">
					Local Data Expires
				</td>
				<td class="stats_txt3_wide" style="white-space: nowrap;">
					<span {if $stats.licenseLeft}class="text_blue"{else}style="color: red;"{/if}>
						{$stats.localLicenseExpire|date_format}
					</span>
				</td>
			</tr>
		</table>
		<br />
		<div class="page_note">
			{if $stats.licenseLeft}
				<img src="admin_images/bullet_success.gif" alt="License is Active" style="margin: 5px; vertical-align: middle; float: left;" />
				You have <strong class="text_blue">{$stats.licenseLeft}</strong>
				remaining before leased license data is renewed.
				Note that the license data renewal date may not match up exactly with the monthly lease
				invoice due date.  Refer to the generated invoice for the precise due date every month.
			{else}
				<img src="admin_images/bullet_error.gif" alt="Notice" style="margin: 5px; vertical-align: middle;float: left;" />
				Lease is currently <strong style="color: red;">past due</strong> according to our records!
			{/if}
			
			<div class="clr"></div>
		</div>
		<a href="#" id="downloadToggle">See Options</a>
		<div id="download_Links" style="display: none;">
			<div style="margin-top: 15px;">
				<ul class="home_links center">
					<li><a href="https://geodesicsolutions.com/client-area/task,my_downloads/" {$extrnLink}>My Downloads</a></li>
					{if $stats.packageId}
						<li><a href="https://geodesicsolutions.com/client-area/task,my_package_details/package_id,{$stats.packageId}/category_id,370/tab,downloads/" {$extrnLink}><span class="text_blue">Latest</span> Release Downloads</a></li>
					{/if}
				</ul>
			</div>
			<div class="clr"></div>
		</div>
	</div>
</fieldset>