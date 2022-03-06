<form action="" method="post">		
	<select id="language_select">
		{foreach from=$languages item=lang key=id}
			<option value="{$id}" {if $id == $current_language}selected="selected"{/if} data-redir="{$lang.link}">{$lang.name}</option>
		{/foreach}
	</select>
</form>

{add_footer_html}
	<script>
		jQuery('#language_select').change(function() {
			var to = jQuery('#language_select option:selected').data('redir');
			window.location = to;
		});
	</script>
{/add_footer_html}