{* 7.4beta1-362-g6f5c580 *}

{if $new_item_buttons}
	<div class="content_box">
		<h2 class="title" id="addToCartButton">{if $allFree}{$messages.500407}{else}{$messages.500398}{/if}</h2>
		
		<ul id="cart_buttons">
			{foreach from=$new_item_buttons key=k item=s}
				{if $s}
					<li><a href="{$cart_url}&amp;action=new&amp;main_type={$k}" class="button">{$s}</a></li>
				{/if}
			{/foreach}
		</ul>
	</div>
	{add_footer_html}
	<script type="text/javascript">
		//<![CDATA[
		//makes the section able to be collapsed/expanded.
		jQuery('#addToCartButton').click(function () {
			jQuery('#cart_buttons').toggle('fast');
		});
		
		{if $no_use_checkout ne 1}
			//comment the next line in the template to make it un-collapsed
			//by default
			//jQuery('#cart_buttons').hide();
		{/if}
		//]]>	
	</script>
	{/add_footer_html}

{/if}