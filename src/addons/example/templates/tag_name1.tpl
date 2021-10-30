{* b1a8dfa *}
{* 
Note to template designers:
	You can over-ride an addon's smarty template by placing
	an identically named template file in your template set,
	under addons/ in a sub-directory named the same as the folder
	name for the addon.
	For example, to over-ride this template, you would create
	a file at:
	my_template_set/addons/example/tag_name1.tpl

 *}

<div style="float: right;">
	{if $display_hello_world}Hello World!<br />{/if}
	This is being called from an addon tag, and the contents are
	actually located in the 
	<strong>smarty template addon/example/templates/tag_name1.tpl</strong>,
	and controlled by the <strong>function tag_name1()</strong> in 
	the <strong>class addon_example_tags</strong>
	in the <strong>file addons/example/tags.php</strong>.  Now try saying
	that 5 times fast.
</div>