{* 7.1.2-38-g0a29354 *}

<script type="text/javascript">
//<![CDATA[
	{strip}addonNavigation.getParams = {
		{foreach $smarty.get as $key => $value}
			{if $key=='action'}{$key='_action'}{elseif $key=='controller'}{$key='_controller'}{/if}
			{* allow for 2 level deep array *}
			{if is_array($value)}
				{foreach $value as $sKey => $sValue}
					{if is_array($sValue)}
						{foreach $sValue as $ssKey => $ssValue}
						'{$key|escape_js}[{$sKey|escape_js}][{$ssKey|escape_js}]' : '{$ssValue|escape_js}'{if !($value@last && $sKey@last && $ssKey@last)},{/if}
						{/foreach}
					{else}
						'{$key|escape_js}[{$sKey|escape_js}]' : '{$sValue|escape_js}'{if !($value@last && $sKey@last)},{/if}
					{/if}
				{/foreach}
			{else}
				'{$key|escape_js}' : '{$value|escape_js}'{if !$value@last},{/if}
			{/if}
		{/foreach}
	};{/strip}
//]]>
</script>