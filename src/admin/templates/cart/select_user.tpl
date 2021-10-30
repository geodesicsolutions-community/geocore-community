{* 7.2beta3-70-g4bcfda5 *}

{$adminMsgs}

<style>
.ui-autocomplete-loading {
	background: white url('admin_images/loading_16.gif') right center no-repeat;
}
</style>

{if $verify_url}
	<script>
	jQuery(function () {
		geoAdminCart.verifiedUrl = '../{external file=$verify_url}';
	});
	</script>
{/if}

<fieldset>
	<legend>Select User</legend>
	<div>
		<p class="page_note">
			You must specify what user this order is being created for.
		</p>
		{if $allow_anon}
			<div class="center">
				<a href="index.php?page=admin_cart&userId=0" class="mini_button">New Anonymous Order</a>
				<br /><br />
			</div>
		{/if}
		<form id="selectUserForm" method="get" action="index.php">
			<input type="hidden" name="page" value="admin_cart" />
			<input type="hidden" name="userId" id="userIdInput" value="" />
			<div class="{cycle values='row_color1,row_color2'}" style="position: relative;">
				<div class="leftColumn">Find User to place Order For:</div>
				<div class="rightColumn ui-widget">
					<input type="text" id="userSearch" name="userSearch" style="width: 300px;" placeholder="username/ID#/first/last/address/phone" />
				</div>
				<div class="clearColumn"></div>
			</div>
		</form>
	</div>
</fieldset>

{if $ordersInProgress}
	{include file='cart/orders_in_progress.tpl' userSelect=1}
{/if}