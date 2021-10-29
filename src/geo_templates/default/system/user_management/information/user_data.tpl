{* 7.5.3-125-gf5f0a9a *}

<div class="content_box">
	<h1 class="title my_account">{$messages.637}</h1>
	<h3 class="subtitle">{$messages.554}</h3>
	<p class="page_instructions">{$messages.555}</p>
	
	{foreach from=$data item=i}
		<div class="{cycle values='row_odd,row_even'} highlight_links">
			<label class="field_label">{$i.label}</label>
			<div class="inline">{if $i.link}<a href="{$i.link}">{/if}{$i.value}{if $i.link}</a>{/if}</div>
		</div>
	{/foreach}
	<div class="center"><a href="{$editInfoLink}" class="button">{$messages.568}</a></div>
</div>

<br />

{if $sellerBuyerInfo}
	<div class="content_box">
		<h2 class="title">{$messages.500181}</h2>
		<p class="page_instructions">{$messages.500182}</p>
		{$sellerBuyerInfo}
	</div>
{/if}


{if $pricePlanInfo}
	{$pricePlanInfo}
{/if}

{if $addonPlanInfo}
	{$addonPlanInfo}
{/if}

<div class="center">
	<a href="{$userManagementHomeLink}" class="button">{$messages.569}</a>
</div>