{* 6.0.7-3-gce41f93 *}
{if $no_recovery}
	<div class="page_description">{$no_recovery}</div>
{else} 
	<div class="content_box">
		<h1 class="title">{$messages.347}</h1>
		<p class="page_instructions">{$messages.348}</p>
		
		{if $error_message}
			<div class="field_error_box">{$error_message}</div>
		{/if}
		
		{if $display_success}
			<div class="success_box">{$messages.2496}</div>
		{/if}
		<form action="{$formTarget}" method="post">
			<div class="row_even center">
				<label for="b[email]" class="bold">{$messages.349}</label>
				<input type="text" name="b[email]" id="b[email]" size="30" maxlength="100" class="field" />		
			</div>
	
			{if $security_image}{$security_image}{/if}
	
			<div class="center">
				<input type="submit" name="submit" value="{$messages.350}" class="button" />
			</div>
		</form>
	</div>
{/if}