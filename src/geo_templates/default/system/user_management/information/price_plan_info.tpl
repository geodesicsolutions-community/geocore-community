{* 7.5.3-125-gf5f0a9a *}

<br />
<div class="content_box">
	<h1 class="title my_account">{$pageTitle}</h1>
	<p class="page_instructions">{$pageDescription}</p>
	
	{foreach from=$data item=i}
		<div class="{cycle values='row_odd,row_even'}">
			<label class="field_label">{$i.label}</label>
			{if $i.link}<a href="{$i.link}"{if $i.linkClass} class="{$i.linkClass}"{/if}>{/if}{$i.value}{if $i.link}</a>{/if}
		</div>
	{/foreach}
	
	{if count($ffRows) > 0}
		{include file='information/final_fee_table.tpl'}
	{/if}
</div>

{if count($categories) > 0}
	<br />
	<div class="content_box">
		{if $pagination}<a name="plan{$plan}"></a>{/if}
		<h2 class="title">{$messages.738}</h2>
		<p class="page_instructions">{$messages.739}</p>
		
		{foreach from=$categories item=c}
			<h3 class="subtitle">{$c.name}</h3>
			{foreach from=$c.rows item=r}
				<div class="{cycle values='row_odd,row_even'}">
					<label class="field_label">{$r.label}</label>
					{$r.value}
				</div>
			{/foreach}
		{/foreach}
		{if $pagination}{$pagination}{/if}
	</div>
{/if}