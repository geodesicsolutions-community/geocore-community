/* Custom CSS File Created: {$smarty.now|date_format:"%B %e, %Y - %H:%M:%S"} */

/*
 * Use this custom.css file to overwrite CSS from the default.css file.  If you
 * want to customize the CSS, copy the applicable CSS section(s) to this file
 * and customize here.  It is recommended to use the admin edit tool as it makes
 * this process a little easier.
 * 
 * --- Caveat: Background Images ---
 * 
 * Note that images loaded in the CSS are relative, so if the default CSS file
 * specifies a URL like url('../images/background.jpg') - since it is relative,
 * it will use background.jpg from the default template set, NOT your custom template
 * set.  If you want to use an image background from your own template set, copy
 * the applicable CSS to this file so that the image location will be relative
 * to this custom.css file rather than the default template's default.css file.
 */

header.page .logo_box  {
	/* SAMPLE of how to force it to use the background image from this
	   template set rather than from the default. */
	background: transparent url('../images/logo_bg.png') no-repeat top right;
}
