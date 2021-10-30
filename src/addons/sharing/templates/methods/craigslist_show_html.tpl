{* 7.4.3-11-g745410f *}
<div class="closeBoxX"></div>
{if $preview}
	<h1 class="title">{$msgs.craigslist_html_previewTitle}</h1>
	<div class="clPreview">{$html}</div>
{else}
	<h1 class="title">{$msgs.craigslist_html_boxTitle}</h1>
	<div class="clShowHTML">
		
		<p class="clInstructions">{$msgs.craigslist_html_instructions}</p>
		
		{$msgs.craigslist_html_title}<br />
		<input type="text" readonly="readonly" value="{$title}" class="field clInput" /><br />
		
		{$msgs.craigslist_html_price}<br />
		<input type="text" readonly="readonly" value="{$price}" class="field clInput" /><br />
		{$msgs.craigslist_html_desc}<br />
		<textarea class="field" readonly="readonly" rows="14" cols="100">{$html}</textarea>
	</div>
{/if}