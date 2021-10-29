{* 7.4beta2-30-g6bcb3a7 *}
{if $neighborly}
	<img src="{$neighborly}" alt="" />
	{if $neighborly_tooltip}
		<a href="show_help.php?addon=charity_tools&amp;auth=geo_addons&amp;textName=tooltip_badge_display_neighborly" class="lightUpLink" onclick="return false;">
			<img src="{external file=$helpIcon}" alt="" class="help_icon" />
		</a>
	{/if}
{/if}
{if $charitable}
	<img src="{$charitable}" alt="" />
	{if $charitable_tooltip}
		<a href="show_help.php?addon=charity_tools&amp;auth=geo_addons&amp;textName=tooltip_badge_display_charitable" class="lightUpLink" onclick="return false;">
			<img src="{external file=$helpIcon}" alt="" class="help_icon" />
		</a>
	{/if}
{/if}