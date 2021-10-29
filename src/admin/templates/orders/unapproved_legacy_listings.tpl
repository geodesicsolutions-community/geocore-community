<fieldset>
<legend>Legacy Listings (Placed Pre-4.0) Awaiting Approval</legend>
<p class="small_font">The following listings were waiting for admin approval before upgrading from version 3. You may approve or delete them here.
{if count($legacy) >= 50}
	<br /><strong>NOTE:</strong> This page displays only the first 50 listings awaiting your approval, but there are more than 50 legacy listings that require your attention.
	As you approve or remove these, more will take their place until all of the legacy listings are approved or removed.
{/if}
<form action="" method="post">
<table>
<tr>
	<td><strong>Listing</strong></td>
	<td><strong>Approve</strong></td>
	<td><strong>Remove</strong></td>
</tr>
{foreach from=$legacy item=listing}
<tr class="row_color{cycle values="1,2"}">
	<td><a href="?mc=users&page=users_view_ad&b={$listing.id}">{$listing.title|fromDB} ({$listing.id})</a></td>
	<td><input type="checkbox" id="app{$listing.id}" name="approve[{$listing.id}]" value="1" onclick="if(this.checked) document.getElementById('del{$listing.id}').checked = false;"></td>
	<td><input type="checkbox" id="del{$listing.id}" name="delete[{$listing.id}]" value="1" onclick="if(this.checked) document.getElementById('app{$listing.id}').checked = false;"></td>
</tr>
{foreachelse}
{* This is just a sanity check -- 
this template should never be called if there are no applicable listings *}
<tr><td colspan="3">No Legacy Listings Awaiting Approval!</td></tr>
{/foreach}
</table>
<input type="submit" name="auto_save" value="submit" />
</form>
</fieldset>