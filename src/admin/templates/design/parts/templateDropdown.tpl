{* 16.09.0-79-gb63e5d8 *}

<select name="{$selectName}" id="{$selectId}" class="form-control col-md-7 col-xs-12">
	{if $showBlankTemplate}
		<option value="none"></option>
	{/if}
	{foreach from=$templates item=tsetList key=template}
		<option value="{if $tsetList}{$template|escape}{else}none{/if}"{if $templateSelected&&$templateSelected==$template} selected="selected"{/if}>
			{strip}
				{$template}
				{if $tsetList && $advMode}
					&nbsp;- [
					{foreach from=$tsetList item=t_set name=tSets}
						{$t_set}{if !$smarty.foreach.tSets.last}, {/if}
					{/foreach}
					]
				{/if}
			{/strip}
		</option>
	{/foreach}
</select>