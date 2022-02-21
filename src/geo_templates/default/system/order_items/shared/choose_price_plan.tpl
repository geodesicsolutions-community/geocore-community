{* 7.5.3-125-gf5f0a9a *}

{include file="system/cart/cart_steps.tpl"}

<div class="content_box">
	{if $steps_combined}
		<h1 class="subtitle">{$messages.2464}</h1>
	{else}
		<h1 class="title">{$messages.606}</h1>
		<h3 class="subtitle">{$messages.2464}</h3>
	{/if}
	<p class="page_instructions">
		{$messages.2463}
	</p>

	{if $error_msgs.cart_error}
		<div class="field_error_box">
			{$error_msgs.cart_error}
		</div>
	{/if}
	<div style="margin-left: 40px;"{if $steps_combined} class="combined_update_fields"{/if}>
		<ul class="priceplan_choose">
			{foreach from=$price_plans item=price_plan}
				<li class="element">
					{if $steps_combined}
						<label>
							<input type="radio" name="price_plan" value="{$price_plan.price_plan_id}"
								{if $price_plan.price_plan_id==$price_plan_id} checked="checked"{/if} />
					{else}
						<a href="{$process_form_url}&amp;price_plan={$price_plan.price_plan_id}">
					{/if}
						<span class="category_title" style="padding: 0;">{$price_plan.name}</span>
						{if $price_plan.description}
							<p class="category_description">{$price_plan.description}</p>
						{/if}
					{if !$steps_combined}
						</a>
					{else}
						</label>
					{/if}
				</li>
			{/foreach}
		</ul>
		<div class="clr"><br /></div>
	</div>
</div>

{if !$steps_combined}
	<br />
	<div class="center">
		<a href="{$cart_url}&amp;action=cancel" class="cancel">{$messages.74}</a>
	</div>
{/if}
