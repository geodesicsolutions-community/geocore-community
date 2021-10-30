{* 6.0.7-3-gce41f93 *}

<div class="closeBoxX"></div>

{if $adminMsgs}
	{$adminMsgs}
{else}
	<div class="lightUpTitle" id="editConfirmTitle">
		Edit Price for {$itemDetails.title} ({$item_id})
	</div>
	<div class="templateToolContents" style="width: 450px;">
		<form action="index.php?page=admin_cart_edit_price&amp;userId={$cart_user_id}&amp;item={$item_id}" method="post">
			<div class="page_note">
				Note that the price can get reset if the item is edited.  For instance
				if the image item has the price changed, and is then edited to add or
				remove an image, the cost of the image item will be reset.  If you are
				going to send the item(s) to the client's cart, make sure the client
				is aware of this.
			</div>
			
			<br />
			<strong>Change price for <span class="text_blue">{$itemDetails.title}</span>:</strong><br />
			<br />
			
			<div class="center">
				<label>
					{$precurrency}
					<input type="text" name="cost" value="{$cost|displayPrice:'':''}" size="6" class="field" />
					{$postcurrency}
				</label>
			</div>
			<br /><br />
			
			<div style="text-align: right;">
				<input type='submit' name='auto_save' class="mini_button" value="Apply Changes" />
				<input type="button" value="Cancel" class="closeLightUpBox mini_cancel" />
			</div>
		</form>
	</div>
{/if}