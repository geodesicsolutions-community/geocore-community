{* 16.09.0-88-g469438c *}

<style type="text/css">
	.steps
	{
		position:relative;
		font-family: Tahoma,Verdana;
		font-size:12px;
		width:auto;
		height:25px;
		border-style:solid;
		border-color:#88aacc;
		border-width:1px;
		padding: 2px 8px 2px 8px;
		display:inline;
		margin:3px;
		top:-30px;
		right: 0px;
		background-color:white;

	}

	.tip
	{
		font-size:14px;
		font-weight: bold;
	}

	.rebbox
	{
		position:relative;
		overflow:visible;
		width:50%;
		margin: 1px 1px 1px 1px;
		padding: 1px 8px 1px 8px;
		height:20px;
		border-style:solid;
		border-width: 1px;
		border-color:red;


	}
</style>

{* doesn't really make sense to have this here with the new breadcrumb design, since it's not really a breadcrumb....
<div class="breadcrumbBorder">
	<ul id="breadcrumb">
	{foreach $steps as $step_number => $this_step}
		<li {if $this_step@last}class="current2"{/if}><a>{$this_step}</a></li>
	{/foreach}
	</ul>
</div>
<br />
*}

<fieldset style='text-align: left;'>
<legend>SEO</legend>
<div>


{if $go_step}
	{if $go_step eq 1}
		{* Customize URLS *}
		To use the software default URL settings, click <strong>Continue</strong>. For advanced users, click <strong>Customize URLs</strong> to insert your own custom structure. You may come back at a later time and reconfigure this setting if you like.


		<br /><br />
		{include file="admin/HTML/add_button.tpl" link="?mc=addon_cat_SEO&page=addon_SEO_main_config&step=2" label="Continue"}
		 <span style="font-weight: bold;">OR</span>
		{include file="admin/HTML/add_button.tpl" link="onclick=\"$('customUrls').toggle();\"" link_is_really_javascript=1 label="Customize URLs"}
		<div id="customUrls">
		<br />
		When you are finished customizing the URL's, click <strong>Continue</strong> above.
 	{elseif $go_step eq 2}
		<strong><span style='color:red'> Important!!</span></strong><br />
		Edit or create the <strong>.htaccess</strong> file in the base directory of the Geo installation (the same directory as the config.php file).<br />
		Insert the text below into your .htaccess file now.</b> (copy and paste)
		<br /><br />
		<strong>.htaccess</strong> Contents
		<div id="htaccess_setup"></div>
		<br />
		<span class='medium_font'>Before you continue, <strong>make sure you have updated your .htaccess file</strong>! Failure to so will cause broken links.
		If similar text already appears in your .htaccess file, remove the text and replace it with the above.  When you are finished, remember to upload the .htaccess file to your site.</span>
	{elseif $go_step eq 3}
		<div id='responses' class='medium_font'>
		<span class='medium_font'>
		Click on the URL below and make sure it works:<br /><br />
		{$url_info}
		</span>
		<br /><br />
		<strong>Is the link above working?</strong><br />
		<label><input type='radio' value='1' name='seo' id='value1'>Yes (Displays a category, <strong>**</strong>possibly without proper styling)</label><br />
		<label><input type='radio' value='2' name='seo' id='value2' checked="checked">No (404 page not found error, or page redirects, or similar problem)</label>
		<br /><br />
		<p class="page_note"><strong>**</strong> As long as the page displays without any 404 "page not found" errors, select "Yes" above, <strong>even if the page style or formatting looks messed up.</strong>  Rest assured, once this wizard is complete, SEO links will be turned on and the category page will display with the proper style formatting.</p>
		</div>
		<a href="#" onclick="continueClick(); return false;" id="continue_button" class="mini_button">Continue</a>
	{elseif $go_step eq 4}
	<span class='medium_font'>
	 Click the button below to turn on Search Engine Friendly URLs.<br /><br />
	</span>

	{/if}

{/if}
{$content}
{if $go_step eq 1}
</div>
<script>
$('customUrls').hide();
</script>
{elseif $go_step eq 2}
		<script>
		var elem = $('generate');
		if(elem) {ldelim}
			elem.click();
			elem.hide();
		}
		</script>
{/if}
</div>
</fieldset>
