{* 7.4beta1-362-g6f5c580 *}

{*
This is inserted into the head of the page for the preview window, it is meant to
stop clicks, since clicking on some of the links in preview window will result
in site errors since the listing does not exist yet (for example, contact seller)
*}

{literal}
<script>
jQuery(function () {
	jQuery('body').click(function (e) {
		e.preventDefault();
	}).css({ cursor: 'crosshair'});
	//also set the style for all links to a crosshair
	jQuery('a').css({ cursor: 'crosshair'});
});
</script>
{/literal}