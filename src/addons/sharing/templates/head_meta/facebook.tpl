{* 7.4.4-114-g6ba2326 *}
{* The meta tag(s) for listing stuff to make like work better on Facebook *}
{if $image_url}
	<meta property="og:image" content="{$image_url}" />
{/if}
{if $listing}
	<meta property="og:description" content="{$description_clean|escape}" />
	<meta property="og:title" content="{$listing.title|fromDB|escape}" />
	<meta property="og:type" content="product" />
	<meta property="og:url" content="{$listing_url|escape}" />
{/if}

<script>
//<![CDATA[
(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.0{if $fb_app_id}&appId={$fb_app_id}{/if}";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
//]]>
</script>