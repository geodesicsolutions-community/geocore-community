{* 7.4.4-10-g8576128 *}{*
*}<?xml version="1.0" encoding="UTF-8"?>
<listings>
	{foreach from=$listings item="listing" name="listingLoop"}
		{process_listing listing=$listing} 
		<listing>
			{foreach from=$listing key=label item=value} 
				{if $label!='category_name'&&$value} 
					<{$label}>{strip}
						{if $label=='images'}
							{foreach from=$value item=image}
								<image>{$image.url}</image>
							{/foreach}
						{elseif $label=='questions'}
							{foreach from=$value item=question}
								{if $question.checkbox}
									<checkbox>{$question.value}</checkbox>
								{else}
									<question>
										<name>{$question.name}</name>
										<value>{$question.value}</value>
									</question>
								{/if}
							{/foreach}
						{elseif $label=='category'&&$catFormat!='id'}
							{$listing.category_name}
							{if $catFormat=='name_id'}
								({$value})
							{/if}
						{else}
							{if $label=='description'}<![CDATA[{/if}
							{$value}
							{if $label=='description'}]]>{/if}
						{/if}
					{/strip}</{$label}>
				{/if}
			{/foreach} 
		</listing>
	{/foreach}

</listings>
