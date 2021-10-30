{* 7.6.3-179-g04b5ca4 *}
{* Note: ALL of a user's registration data is available in these default_pages templates as the $user array *}
<div class="user_page">
	<h2>Welcome to our store!</h2>
	<p>A map of our location is included below. Use the links on the left to begin browsing.</p>
	<div style="width: 100%; text-align: center;">
	<iframe style="width: 90%; height: 20.3125em; margin: 0 auto;" frameborder="0" scrolling="no" src="http://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;q={$locationForMap}&amp;output=embed"></iframe>
	</div>
</div>