<fieldset>
	<legend>No Body TPL specified</legend>
	<div>
		Either there was an error, or the main body template file was not properly specified.
		<br /><br />
		Example:<br />
		<strong>my_admin_page.php</strong>
		<pre style="border: thin dashed black;">
{literal}
class myAdminPageClass
{
	function display_myPage()
	{
		$admin = geoAdmin::getInstance();
		//load up template vars here
		$admin->v()->var1 = 'data'; //just an example of how to set template var
		
		//set what template to use for the main body here (this is the one you are
		//not doing yet, since you are seeing this page)
		<strong>$admin->setBodyTpl('my_template.tpl');</strong>
		
		//thats it!  the page loader will now load up the admin page, you don't have
		//to do any of the displaying yourself.
	}

}
{/literal}
		</pre>
		<br />
		<strong>admin/templates/my_template.tpl</strong>
		<pre style="border: thin dashed black;">
{literal}
&lt;fieldset>
	&lt;legend>My Settings!&lt;/legend>
	&lt;div>This is a bunch of settings for my admin page!  Here is a var I just passed from display page:
	&lt;br />
	{$data}
	&lt;/div>
&lt;/fieldset>
{/literal}
		</pre>
	</div>
</fieldset>