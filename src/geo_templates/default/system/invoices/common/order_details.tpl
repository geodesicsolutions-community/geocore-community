<div class="content_box">
		<h2 class="title">{$messages.500536}</h2>
		<p class="page_instructions">{$messages.500537}</p>
		
		{foreach from=$order_items key=k item=item}
			{include file="common/order_item.tpl" is_sub=0}
		{foreachelse}
			<div class="note_box">
				{$messages.500538}
			</div>
		{/foreach}
		<div class="clr"></div>
	</div>