{* 7.5.3-125-gf5f0a9a *}

{include file="cart_steps.tpl" g_resource="cart" g_type="system"}

{foreach from=$error_msgs item=err_msg}
	<div class="cart_error">
		{$err_msg}
	</div>
{/foreach}
<form action="{$process_form_url}" method="post">
	<div class="content_box">
		<h2 class="title">{$msgs.chooseTokens_title}</h2>
		<h3 class="subtitle">{$msgs.chooseTokens_subtitle}</h3>
		<p class="page_instructions">{$msgs.chooseTokens_instructions}</p>
		<div class="listing_extra_item">
			{$msgs.chooseTokens_choose_label}
			<div class="listing_extra_cost">
				{foreach $tokenChoices as $id=>$choice}
					<div>
						<input type="radio" id="radio{$id}" name="token_choice" value="{$choice.tokens}"{if $tokens == $choice.tokens} checked="checked"{/if} />
						<label for="radio{$id}">
							{$choice.tokens} {$msgs.chooseTokens_tokens_after}
						</label>
						<label for="radio{$id}" class="price">
							{$choice.price|displayPrice}
						</label>
					</div>
				{/foreach}
			</div>
			<div class="clear"></div>
		</div>
		<div class="clear"></div>
	</div>
	<div class="center">
		<input type="submit" class="button" value="{$msgs.chooseTokens_continue_button}" />
		<br /><br />
		<a href="{$cancel_url}" class="cancel">{$msgs.chooseTokens_cancel}</a>
	</div>
</form>
