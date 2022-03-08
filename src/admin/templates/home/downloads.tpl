{* so we don't have to have super long thing for each link *}
{capture assign='extrnLink'}class="mini_button" style="white-space: normal;" onclick="window.open(this.href); return false;"{/capture}

<fieldset>
	<legend><span class="glyphicon glyphicon-download-alt"></span> Software Downloads</legend>
	<div class="medium_font">

		<div class="col_ftr">Free Downloads:</div>

		<div class="page_note">
			<img src="admin_images/bullet_success.png" alt="Download Access Active" style="margin:0 5px; vertical-align: middle; float: left;" />
			Your ability to download GeoCoreCE from Github never expires.
    		<div class="clr"></div>
		</div>

		<div class="center"></div>

		<a href="#" id="downloadToggle">See Options</a>
		<div id="download_Links" style="display: none;">
			<div style="margin-top: 15px;">
                <ul class="home_links center">
                    <li><a href="https://github.com/geodesicsolutions-community/geocore-community/releases" target="_blank" class="btn btn-default source">Source Code on GitHub</a></li>
                </ul>
			</div>
			<div class="clr"></div>
		</div>

	</div>
</fieldset>
