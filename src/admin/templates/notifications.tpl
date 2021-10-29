{* 7.5.3-36-gea36ae7 *}
{if $notifications}
<fieldset><legend>Notifications</legend>
<div class='notifications'>
	<ul>
		{foreach from=$notifications item="notification"}
		<li class='medium_font'>
			{$notification}
		</li>
		{/foreach}
	</ul>
</div>
</fieldset>
{/if}