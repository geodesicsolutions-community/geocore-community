{* 6.0.7-3-gce41f93 *}
<div class="closeBoxX"></div>

{if $adminMsgs}
	{$adminMsgs}
{else}
	<div class="lightUpTitle" id="editConfirmTitle">
		Swap Items with {$username}'s client-side Cart
	</div>
	<div class="templateToolContents" style="width: 450px;">
		<form action='index.php?page=admin_cart_swap&amp;userId={$userId}&amp;direction={$direction}' method='post'>
			<div class="page_note">
				{if $direction=='to'}
					This will move all the items currently in this admin created order, into 
					<span class="text_blue">{$username}</span>'s client-side cart, and remove the items from
					the admin order.
					<br /><br />
					Note that any existing items in the user's client-side cart will remain intact.
				{else}
					This will move all the items currently in <span class="text_blue">{$username}</span>'s client-side
					cart, into the admin created order that is currently in progress,
					and remove the items from the client-side cart.
					<br /><br />
					Note that any existing items in this admin-created order will remain intact.
				{/if}
				
			</div>
			
			<br />
			<div class="center">
				<div style="display: inline-block; border: 2px solid #777777; padding: 10px;">
					{if $direction=='to'}This Admin-Created Order{else}<span class="text_blue">{$username}</span>'s Client-Side Cart{/if}
				</div>
				<strong>&gt;&gt;</strong>
				<div style="display: inline-block; border: 2px solid #777777; padding: 10px;">
					{if $direction=='from'}This Admin-Created Order{else}<span class="text_blue">{$username}</span>'s Client-Side Cart{/if}
				</div>
			</div>
			<br /><br />
			
			<div style="text-align: right;">
				<input type='submit' name='auto_save' class="mini_button" value='Move Items' />
				<input type="button" value="Cancel" class="closeLightUpBox mini_cancel" />
			</div>
		</form>
	</div>
{/if}