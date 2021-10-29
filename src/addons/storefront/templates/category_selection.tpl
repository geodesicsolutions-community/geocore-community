{* 7.4.4-10-g8576128 *}
<div class="listing_extra_item clearfix">
	<div class="listing_extra_cost">
		<select name="c[storefront_category]" class="field">
			{foreach from=$cats item="cat"}
				<option value="{$cat.category_id}"{if $selected == $cat.category_id} selected="selected"{/if}>
					{$cat.category_name}
				</option>
				<optgroup>
					{foreach $cat.subcategories as $sub}
						<option value="{$sub.category_id}"{if $selected == $sub.category_id} selected="selected"{/if}>
							{$sub.category_name}
						</option>
					{/foreach}
				</optgroup>
			{/foreach}
		</select>
	</div>
	<br />{$storefront_messages.storefront_category_choose_title}
	<br />
	{if $error}<span class="error_message">{$error}</span>{/if}
	<br />
</div>
