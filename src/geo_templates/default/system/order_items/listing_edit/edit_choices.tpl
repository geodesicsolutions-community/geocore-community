{* 7.5.0-6-g895f640 *}

<div class="content_box">
	<h1 class="title">{$messages.482}</h1>
	<p class="page_instructions">{$messages.483}</p>
	
	{if $error_msgs.cart_error}
		<div class="field_error_box">
			{$error_msgs.cart_error}
		</div>
	{/if}
	
	<div class="center">
		<ul class="button_list">
			{foreach from=$choices item=label key=step}
				<li>
					<a href="{$nextPage}&amp;doStep={$step}" class="button">{$label}</a>
				</li>
			{/foreach}
		</ul>
		
		<div class="clr"><br /></div>
		
		<ul class="button_list">
			<li><a href="{$previewUrl}" onclick="window.open(this.href,'previewWindow','scrollbars=yes,status=no,width=800,height=600'); return false;" class="button">{$messages.500483}</a></li>
			<li><a href="{$cart_url}&amp;action=cancel" class="cancel">{$messages.500257}</a></li>
		</ul>
		
		<div class="clr"><br /></div>
		
		<ul class="button_list">
			<li><a href="{$nextPage}&amp;doStep=continue" class="button finish-edit">{$messages.500256}</a></li>
		</ul>
		
		<div class="clr"><br /></div>
	</div>
</div>