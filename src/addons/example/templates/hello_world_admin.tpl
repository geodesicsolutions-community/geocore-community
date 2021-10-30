{* b1a8dfa *}
{* 
Note to template designers:
	You can over-ride an addon's smarty template by placing
	an identically named template file in your template set,
	under addons/ in a sub-directory named the same as the folder
	name for the addon.
	For example, to over-ride this template, you would create
	a file at:
	my_template_set/addons/example/hello_world_admin.tpl

 *}

<fieldset>
	<legend>Addons are <em style="text-decoration: underline;">Super Fun!</em></legend>
	<div>
		This is just a simple example of how you do not 
		need to specify a parent category, and the addon
		admin page will still be auto-added to the automatically 
		created category under addon management.  It's also a great
		example of how to use smarty templates to render a page in
		the admin, although the instructions below do not cover that, for
		that you look at the source for the example addon.
		<br /><br />
		And for your further <em>learning pleasure</em>, here is how 
		to create the page you are looking at, but skipping 
		right to the part where you are adding
		the admin page.<br />
		File: <strong>addons/example/admin.php</strong><br />
		Function: <strong>init_pages</strong><br />
		Add line:
		<div style="border: 1px dashed green; padding: 10px;">
{* The next part is a bunch of fancy smarty stuff, end result of doing a php highlight string *}
			{capture name='phpStringc' assign="phpString"}<?php menu_page::addonAddPage('addon_example_another_page','',
	'Addons are <em style="text-decoration: underline;">Fun!</em>','example');{/capture}
			{$phpString|highlight_string:1|regex_replace:'/&lt;\?[\s]*php[\s]*/':''}
		</div>
				<br />
				Then in same file, add the function named <strong>display_addon_example_another_page</strong>.<br />
				Then in that function, add to the body.  The end result of the file would look like this:<br />
				<div style="border: 1px dashed green; padding: 10px;">
					{capture name='c2' assign='phpString'}{literal}<?php
//file: addons/example/admin.php

class addon_example_admin
{
	public function init_pages ()
	{
		menu_page::addonAddPage('addon_example_another_page','',
			'Addons are <em style="text-decoration: underline;">Fun!</em>',
			'example');
	}
	public function display_addon_example_another_page ()
	{
		geoAdmin::getInstance()->getView()->addBody("Hello World!!!");
	}
}
{/literal}{/capture}
					{$phpString|highlight_string:1}
				</div>
				<p><strong>Using Fieldsets in Admin:</strong> To ensure that the fieldset collapses correctly using the new JS, wrap the entire contents of the fieldset with a div,
				not including the legend.  Otherwise it can cause only parts of the fieldset to be collapsed when the user clicks the fieldset.
				View the source of this page to see what I mean.</p>
				<br /><br />
				<p><strong>Passing variables local to this template:</strong> Were you wondering where setting1 and setting2 went?  Their right here: <br />
					setting1: {$setting1}<br />
					setting2: {$setting2}<br />
					product_version: {$product_version}<br /><br />
					And by using the appropriate $view->set... function 
					(either setBodyVar, or setAddonVar),
					we don't have to worry about name conflicts, since those functions
					set the variables to be local to that template.  That is why we can
					use $product_version even though that same variable name is used in the bottom
					of the template for each admin page.
				</p>
			</div>
		</fieldset>