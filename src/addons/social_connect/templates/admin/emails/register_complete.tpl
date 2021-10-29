{* 67d0e9c *}

<p>===== <strong>Automatic Admin Notice</strong> =====</p>
<p><strong>For Site: </strong><a href="{$classifieds_url}">{$classifieds_url}</a></p>

<h1>Facebook Registration is Complete</h1>
<p>A new user was automatically registered with a minimal account by using Facebook Connect.  Below is the user's info.</p>
<p>
	{foreach $user_data as $info}
		<strong>{$info@key}</strong> : {if $info@key=='date_joined'}{$info|date_format}{else}{$info|escape}{/if}<br /><br />
	{/foreach}
</p>