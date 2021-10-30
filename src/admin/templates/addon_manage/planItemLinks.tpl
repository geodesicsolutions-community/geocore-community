{* 6.0.7-3-gce41f93 *}

<ul>
	{foreach from=$plans item=plan}
		<li style="margin-bottom: 8px;">
			<a href="{$plan.link}">{$plan.name}</a>
			{if $plan.cats}
				{foreach from=$plan.cats item=catPlan}
					<ul style="padding-left: 0px;">
						<li>
							<a href="{$catPlan.link}">{$catPlan.name}</a>
						</li>
					</ul>
				{/foreach}
			{/if}
		</li>
	{/foreach}
</ul>