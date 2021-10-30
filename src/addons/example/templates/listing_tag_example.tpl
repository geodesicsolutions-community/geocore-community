{* b1ee10a *}
{* 
Note to template designers:
	You can over-ride an addon's smarty template by placing
	an identically named template file in your template set,
	under addons/ in a sub-directory named the same as the folder
	name for the addon.
	For example, to over-ride this template, you would create
	a file at:
	my_template_set/addons/example/listing_tag_example.tpl

 *}
 
 {*
 	NOTE: When using a $listing_tag tag that is called using the {listing} tag
 	inside a template, note that it can sometimes be called many times on the same
 	page for a bunch of different listings, since it can be used in browsing pages.
 	
 	Because of this, it is recommended to avoid using static id for any HTML,
 	instead use CSS class, or find a way to make sure the id is unique even when
 	the tag is used multiple times.
 	
 	NOT GOOD:
 	<div id="my_addon_id">...</div>
 	
 	OK:
 	<div class="my_addon_class">...</div>
 	
 	Also probably OK (but make sure to pass in listing_id variable):
 	<div id="my_addon_id_listing_{$listing_id}">...</div>
 *}
 
 <strong>Listing Tag Example: The Title in <span style="color: {$title_color};">{$title_color|capitalize}</span>:</strong>
 <span style="color: {$title_color};">{$listing_title}</span>