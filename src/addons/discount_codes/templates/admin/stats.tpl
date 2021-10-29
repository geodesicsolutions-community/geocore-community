{* 6.0.7-3-gce41f93 *}
{if $isAjax}
<div class="closeBoxX"></div>
<div class="lightUpTitle">Usage Stats for {$data.discount_code|fromDB|escape}</div>
{else}
<fieldset><legend>Usage Stats for {$data.discount_code|fromDB|escape}</legend>
<div>
{/if}
<p class="page_note">This is the usage stats for the discount code {$data.discount_code|fromDB|escape}.</p>

<div class="{cycle values='row_color1,row_color2'}">
	<div class="leftColumn">Discount ID #</div>
	<div class="rightColumn">{$data.discount_id}</div>
	<div class="clearColumn"></div>
</div>
<div class="{cycle values='row_color1,row_color2'}">
	<div class="leftColumn">Name</div>
	<div class="rightColumn">{$data.name|fromDB}</div>
	<div class="clearColumn"></div>
</div>

{if $data.description}
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Description</div>
		<div class="rightColumn">{$data.description|fromDB}</div>
		<div class="clearColumn"></div>
	</div>
{/if}
<div class="{cycle values='row_color1,row_color2'}">
	<div class="leftColumn">Discount Percentage</div>
	<div class="rightColumn">{$data.discount_percentage}% Off</div>
	<div class="clearColumn"></div>
</div>

{if $data.apply_normal}
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn"># Normal Orders</div>
		<div class="rightColumn">{$normal_count}</div>
		<div class="clearColumn"></div>
	</div>
{/if}
{if $data.apply_recurring}
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn"># Recurring Orders</div>
		<div class="rightColumn">{$recurring_count}</div>
		<div class="clearColumn"></div>
	</div>
{/if}

<div class="{cycle values='row_color1,row_color2'}">
	<div class="leftColumn">Clients that have used code</div>
	<div class="rightColumn">
		{$userCount}
		{if $userCount}
			<form action="index.php?page=users_search" method="post" style="display: inline;">
				<input type="hidden" name="b[search_type]" value="id_in" />
				<input type="hidden" name="b[id_in]" value="{$usersList|escape}" />
				<input type="submit" value="View All" class="mini_button" />
			</form>
		{/if}
	</div>
	<div class="clearColumn"></div>
</div>
<div class="{cycle values='row_color1,row_color2'}">
	<div class="leftColumn">Clients that have NOT used code</div>
	<div class="rightColumn">
		{$userNegativeCount}
		{if $userNegativeCount}
			<form action="index.php?page=users_search" method="post" style="display: inline;">
				<input type="hidden" name="b[search_type]" value="id_not_in" />
				<input type="hidden" name="b[id_not_in]" value="{$usersList|escape}" />
				{if $data.is_group_specific}
					<input type="hidden" name="b[group_in]" value="{$groupList|escape}" />
				{/if}
				<input type="submit" value="View All" class="mini_button" />
			</form>
		{/if}
	</div>
	<div class="clearColumn"></div>
</div>
<br /><br />
{if !$isAjax}
</div>
</fieldset>
{/if}
