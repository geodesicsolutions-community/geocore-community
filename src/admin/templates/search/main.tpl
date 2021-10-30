{* 16.09.0-79-gb63e5d8 *}


<form id="text_search_form" method="post" action="" class="form-horizontal form-label-left" style="text-align: center; width: 100%;">
	<div class="x_content" style="border: 3px solid #eee; padding: 15px; background-color: #FFF;">
		<input type="hidden" name="search_type" value="text" id="searchType" />
		<input type="hidden" name="auto_save" value="1" />
		<div class='center'>
			<input type="text" name="text" value="{$text|escape}" class='form-control col-md-7 col-xs-12' id="text_query" placeholder="Search Admin for Text..." />
		</div>
		<div class='clearColumn'></div>
		<div class='center'>*Case INsensitive Exact Phrase Match</div>
		<div class='clearColumn'></div>
		<div  class='center' style="margin: 10px;">
			<label>
				<input type="radio" name="show_first" id="show_first" value="1" {if $show_first}checked="checked"{/if} />
				Show FIRST Occurrence Only
			</label>
			<br />
			<label>
				<input type="radio" name="show_first" value="0" {if !$show_first}checked="checked"{/if} />
				Show ALL Occurrences
			</label>
		</div>
		<div class="clearColumn"></div>
		<div style="text-align: center;">
			<input type="submit" value="Search" class="mini_button" id="searchButton" />
		</div>
	</div>
</form>
<br /><br />
<div style="display: none;" id="searchResultsBox">
	<ul class="tabList">
		<li class="activeTab" id="textTab"><i class="fa fa-file"></i><span class="visible-lg-inline"> Pages/Modules Text</span></li>
		<li id="addonTab"><i class="fa fa-plug"></i><span class="visible-lg-inline"> Addon Text</i></li>
		<li id="contentTab"><i class="fa fa-paint-brush"></i><span class="visible-lg-inline"> Template Contents</i></li>
		<li id="filenameTab"><i class="fa fa-paint-brush"></i><span class="visible-lg-inline"> Template Filenames</i></li>
	</ul>
	<div class="tabContents">
		<div id="loadingBox" style="text-align: center; margin: 10px;">
			<img src="admin_images/loading.gif" alt="loading..." style="vertical-align: middle;" /> Loading...
		</div>
		<div id="textTabContents"></div>
		<div id="addonTabContents"></div>
		<div id="contentTabContents"></div>
		<div id="filenameTabContents"></div>
	</div>
	<div style="margin-top: 5px; color: #666; display: none; float: right;" id="permaLinkBox">
		<div style="float: left; padding-right: 5px; border: none; background: transparent;" class="page_note">Search Permalink:</div>
		<div class="page_note" style="float: left;" id="permaLink"></div>
		<div class="clearColumn"></div>
	</div>
</div>
