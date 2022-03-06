{* 7.6.3-149-g881827a *}
{if $cartSteps}
	<nav class="breadcrumb">
		{foreach from=$cartSteps item=label key=step name=cartsteploop}
			{if $label}
				<div{if $step == $currentCartStep} class="active"{/if}>
					{$label}
				</div>
			{/if}
		{/foreach}
	</nav>
{/if}