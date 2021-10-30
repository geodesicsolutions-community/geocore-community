{* 16.09.0-79-gb63e5d8 *}


<div class="closeBoxX"></div>
<div class="lightUpTitle">Import Suggested Text Changes</div>
{if $errorMsgs}
	<div class="errorBoxMsgs">
		<br />
		<strong>Unable to perform action here:</strong><br />
		{$errorMsgs}
		<br /><br />
		<div class="templateToolButtons">
			<input type="button" class="closeLightUpBox mini_button" value="Ok" />
		</div>
		<div class="clearColumn"></div>
	</div>
{else}
		<div style="width: 350px;" class="page_note">
			<p>This template set includes suggested text changes which may be necessary for it to appear as intended.</p>
			<p>To import them, first download this file to an accessible location on your computer: 
				<a href="index.php?page=languages_export&mc=languages&download=1&fromTemplateSet={$t_set}"><strong class="text_blue" style="white-space: nowrap;">{$geo_templatesDir}{$t_set}/text.csv</strong></a>
			</p>
			<p>Then, use that downloaded file with the <a href="index.php?page=languages_import&mc=languages"><strong class="text_blue" style="white-space: nowrap;">Import Language Data</strong></a> tool.</p>
		</div>
		
{/if}
