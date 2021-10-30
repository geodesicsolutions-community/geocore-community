{* 6.0.7-3-gce41f93 *}

<fieldset>
	<legend>Admin Orders in Progress</legend>
	<div>
		<p class="page_note">
			Select user below to resume the order you started for that user.
			{if $showLinkWarning}
				<br /><br /><strong>Warning: </strong> Any <em>un-saved</em> progress on the current
				page will be lost.
			{/if}
		</p>
		<ul>
			{foreach from=$ordersInProgress item='order'}
				<li>
					<a href="{$order.link}">{$order.username} ({$order.user_id}) - {$order.last_time|format_date}</a>
				</li>
			{/foreach}
		</ul>
		{if $userSelect}
			<div class="center">
				<a href="index.php?page=orders_list&narrow_order_status=all&narrow_gateway_type=all&narrow_admin={$user.id}&sortBy=order_id&sortOrder=down" class="mini_button">
					Recently Created Orders
				</a>
			</div>
		{/if}
	</div>
</fieldset>