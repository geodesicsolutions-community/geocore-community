{* 7.5.3-125-gf5f0a9a *}
{*
	This is used for dynamic parts, typically added by sub order items to the listing
	details collection page in various spots.
*}
{foreach $more as $details}
	{if $details.section_head}
		{* Allow adding a section head above the next contents *}
		{if !$details@first}</div><br /><div class="content_box">{/if}

		<h1 class="title">{$details.section_head}</h1>
	{/if}
	{if $details.section_sub_head}
		{* Allow adding a sub-section head above the next contents *}

		<h3 class="subtitle">{$details.section_sub_head}</h3>
	{/if}
	{if $details.section_desc}
		{* Allow adding a section description as well *}
		<p class="page_instructions">{$details.section_desc}</p>
	{/if}
	{if $details.tpl}
		{* way to include sub-template *}
        {assign var="type" value=$details.tpl.g_type}
        {assign var="resource" value=$details.tpl.g_resource}
        {assign var="file" value=$details.tpl.file}
		{include file="$type/$resource/$file"}
	{elseif $details.full}
		{* Or it can include the full contents in the actual variable *}
		{$details.full}
	{elseif $details.pre || $details.label || $details.value || $details.error}
		<div class="{if $details.error}field_error_row {/if}{cycle values='row_odd,row_even'}">
			<label class="field_label">{if $details.pre}{$details.pre}{/if} {$details.label}</label>
			{$details.value}
			{if $details.error}
				<span class="error_message">{$details.error}</span>
			{/if}
		</div>
	{/if}
{/foreach}
