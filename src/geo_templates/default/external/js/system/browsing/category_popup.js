/*
 * This file handles opening the category selection popup.
 * This needs to be called in separately so that it plays nice with the built-in category cache
 * (especially when deferring JS to the footer)
 */

jQuery(document).ready(function() {
	jQuery('.subcategory-nav-open').click(function() {
		jQuery(document).gjLightbox('open', function(){
			return jQuery('.category_block').clone().show().html();
		});
	});
});