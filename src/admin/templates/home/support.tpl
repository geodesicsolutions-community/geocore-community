{* 16.09.0-79-gb63e5d8 *}

{* so we don't have to have super long thing for each link *}
{capture assign='extrnLink'}class="mini_button" style="white-space: normal;" onclick="window.open(this.href); return false;"{/capture}


<fieldset>
	<legend><i class="fa fa-support"></i> Software Support</legend>
	<div class="medium_font">

		<div>
			<div class="col_ftr">Free Support:</div>
			<div class="page_note">
				<img src="admin_images/bullet_success.png" alt="Support Active" style="margin:0 5px; vertical-align: middle; float: left;" />
				You have free access to the support options below that do <strong class="text_blue">not expire</strong>.
				<div class="clr"></div>
			</div>
			<a href="#" id="freeSupportToggle">See Options</a>

			<div id="freeSupport_Links" style="display: none;">
				<div style="margin-top: 10px;">
					<ul class="home_links">
						<li><a href="https://geodesicsolutions.org/wiki/" class="btn btn-default source">User Manual Wiki</a></li>
                        <li><a href="https://github.com/geodesicsolutions-community/geocore-community/discussions" class="btn btn-default source">GeoCoreCE Github Discussion Area</a></li>
						<li><a href="https://www.facebook.com/GeoCoreCE" class="btn btn-default source">GeoCoreCE Facebook Page</a></li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</fieldset>
