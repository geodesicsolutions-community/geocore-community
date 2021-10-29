{* 6.0.7-3-gce41f93 *}

<div class="content_box">
	<h1 class="title">{$messages.1411}</h1>
	
	{if $alreadyRegistered}
		<p class="page_instructions"><a href="{$registration_url}">{$messages.779}</a></p>
	{else}
		<p class="page_instructions">{$messages.243}</p>
		
		<div class="center">
			<a href="{$registration_url}" class="button">{$messages.242}</a>
		</div>
	{/if}
</div>
