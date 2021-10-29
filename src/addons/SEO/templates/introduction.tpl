{* 6.0.7-3-gce41f93 *}
<strong>SEO Wizard</strong>
<br /><br />



{if $settings.type eq 2}
	<br />
	Your are currently using the "old" SEO engine and URL's.<br /><br />
	<span style='color:red'>Warning:</span> 
	By clicking on the "continue" button, you will <strong>start the process to enable using the new style urls</strong>.
	Any current urls will be replaced by the new style urls.
	<br />
	You will also need to <strong>make changes to your .htaccess file</strong> (instructions will be provided during this process).<br />
	<br /><br />
{/if}


{if $checks_pass eq 'no'}
<div class='medium_font'>
<strong>Warning - Unable to proceed:</strong> This add-on requires your server to handle url re-write procedures. 
<br />
<br />
We're sorry. Our software is detecting that will not be able to finish this process until the above issue is fixed.<br/>
<br />
<div class='medium_font'>
Here are a few suggestions to fix the above "Warning"...
</div>
<ul style='list-style:upper-alpha;text-align:left'>
	<li>
		Contact your your host and ask them to turn on mod_rewrite on this server.
	</li>
	<li>
		If you own this server, turn on mod_rewrite. If you are unsure how to do this, please contact your server administrator.
	</li>
	<li>
		Check the user manual and look for mod_rewrite documentation.
	</li>
</ul>
</div>
{else}
This SEO First-Time Wizard will guide you through the steps needed to start using search engine friendly 
URL's on your site.  Click continue below to get started.
<br /><br />
{include g_type="admin" file="HTML/add_button.tpl" link="?mc=addon_cat_SEO&page=addon_SEO_main_config&step=1" label="Continue"}


{/if}